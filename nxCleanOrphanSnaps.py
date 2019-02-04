#!/usr/bin/python

# Copyright (c) 2017 Nutanix Inc. All rights reserved.
#
# Author: wangzi@nutanix.com, prakash@nutanix.com
#
# Provides utility methods for dealing with snapshots created by any
# backup software. Internally, these snapshots are also called
# scoped snapshots. This tool is meant to be used to clean up
# such snapshots that have been left behind, for whatever reason.

import env

import argparse
import datetime
import getpass
import json
import requests
import time

from collections import namedtuple

import aplos.interfaces as interfaces
from aplos.intentengine.lib.intent_spec_wal import AplosIntentSpecWal
from cerebro.client.cerebro_interface_client import \
  CerebroInterfaceTool, CerebroInterfaceError
from cerebro.interface.cerebro_interface_pb2 import \
  ListProtectionDomainsArg, QueryProtectionDomainArg, ModifySnapshotArg
from util.db.cpdb_query import (ALL, EQ, COL, STR)
from util.base.types import NutanixUuid

class Password(argparse.Action):
  def __call__(self, parser, namespace, values, option_string):
    if values is None:
      values = getpass.getpass()
    setattr(namespace, self.dest, values)

PD = namedtuple("PD", "name system")

class Snapshot(object):
  def __init__(self, pd_name, cerebro_snap_uuid, ctime):
    self._pd_name = pd_name
    self._cerebro_snap_uuid = cerebro_snap_uuid
    self._ctime = ctime
    self._vm_uuid = None
    self._snap_uuid = None

  def __lt__(self, other):
    return self._ctime < other._ctime

  @property
  def pd_name(self):
    return self._pd_name

  @property
  def cerebro_snap_uuid(self):
    return self._cerebro_snap_uuid

  @property
  def ctime(self):
    return self._ctime

  @property
  def vm_uuid(self):
    return self._vm_uuid

  @vm_uuid.setter
  def vm_uuid(self, value):
    self._vm_uuid = value

  @property
  def snap_uuid(self):
    return self._snap_uuid

  @snap_uuid.setter
  def snap_uuid(self, value):
    self._snap_uuid = value

namedtuple("Snapshot",
  "pd_name cerebro_snap_uuid ctime vm_uuid snap_uuid")

class BackupSnapshotClient(object):
  """
  A simple python client to manage the snapshots created by backup software.
  """

  def __init__(self, username, password):
    self.cluster_url = "https://127.0.0.1:9440/api/nutanix/v3"
    self.session = requests.Session()
    self.user = username
    self.password = password
    self.session.auth = (self.user, self.password)

  def __query_all_pd_names(self):
    """
    Returns a set of all async PDs in the cluster including the name/UUID
    of the system protection domain.
    """
    cerebro_client = CerebroInterfaceTool()
    pd_names = set()
    arg = ListProtectionDomainsArg()
    ret = cerebro_client.list_protection_domains(arg)
    for pd_name in ret.protection_domain_name:
      pd_names.add(PD(pd_name, False))

    # Get the name of the System PD
    system_pd_arg = ListProtectionDomainsArg()
    system_pd_arg.list_system_pd = True
    ret = cerebro_client.list_protection_domains(system_pd_arg)
    for pd_name in ret.protection_domain_name:
      pd_names.add(PD(pd_name, True))

    return pd_names

  def __query_pd_scoped_snapshots(self, pd_name):
    """
    Query all the scoped/backup snapshots in the protection domain
    specified by pd_name.

    This function returns a set of Cerebro snapshot instances
    representing the  backup snapshots in the protection domain.
    """
    cerebro_client = CerebroInterfaceTool()
    pd_snaps = set()
    arg = QueryProtectionDomainArg()
    arg.protection_domain_name = pd_name
    arg.list_snapshot_handles.list_scoped_snapshots = True
    while True:
      try:
        ret = cerebro_client.query_protection_domain(arg)
        if len(ret.snapshot_handle_vec) == 0:
          break
        for snapshot in ret.snapshot_control_block_vec:
          pd_snaps.add(Snapshot(pd_name, snapshot.snapshot_uuid,
                                snapshot.finish_time_usecs))
        # Continue listing remaining snapshots in the pd.
        arg.ClearField("list_snapshot_handles")
        arg.list_snapshot_handles.list_scoped_snapshots = True
        arg.list_snapshot_handles.last_snapshot_handle.cluster_id = (
          ret.snapshot_handle_vec[-1].cluster_id)
        arg.list_snapshot_handles.last_snapshot_handle.cluster_incarnation_id = (
          ret.snapshot_handle_vec[-1].cluster_incarnation_id)
        arg.list_snapshot_handles.last_snapshot_handle.entity_id = (
          ret.snapshot_handle_vec[-1].entity_id)
      except CerebroInterfaceError as cie:
        print ("Query protection domain returned error '%s'" % str(cie))
        raise cie
    return pd_snaps

  def __get_all_scoped_snapshots_from_intent_db(self):
    """
    Queries the Aplos Intent Engine database for all the scoped snapshots whose
    intent is in the database. This is done to get the snapshot ID seen by the
    outside world. Returns a map of the form:
    <key=Cerebro snapshot UUID, Value=tuple(VM UUID, external snapshot UUID)>.

    Includes only scoped snapshots in kComplete state.
    """
    scoped_snaps = {}
    predicates = []
    predicates.append(EQ(COL("kind"), STR("vm_snapshot")))
    predicates.append(EQ(COL("state"), STR("kComplete")))
    requested_attribute_list = ['uuid', 'spec_json']
    # The return value from intentgw_db is a list of tuples in the following
    # format (intent_spec_uuid, (kind_uuid, spec_json)).
    completed_scoped_snapshots = \
      interfaces.intentgw_db.lookup_by_attribute_values(
        kind_str="intent_spec",
        requested_attributes=requested_attribute_list,
        sort_order="ASCENDING",
        sort_column="uuid",
        where_clause=ALL(*predicates))
    for snap in completed_scoped_snapshots:
      intent_spec_uuid = snap[0]
      external_snap_uuid = snap[1][0]
      wal = AplosIntentSpecWal(intent_spec_uuid)
      if wal.wal_proto.entity_snapshot_kind_wal.HasField("cerebro_snapshot_uuid"):
        cerebro_snap_uuid = wal.wal_proto.entity_snapshot_kind_wal.cerebro_snapshot_uuid
        spec_json = json.loads(snap[1][1]) if snap[1][1] else {}
        if not spec_json:
          # The corresponding intent_spec does not have spec_json. We need to
          # remove the corresponding intent_specs as well during garbage
          # collection. Hence, we record kind uuid here but leave
          # vm_uuid blank.
          scoped_snaps[cerebro_snap_uuid] = ("NONE", external_snap_uuid)
          continue
        vm_uuid = spec_json["resources"]["entity_uuid"]
        scoped_snaps[cerebro_snap_uuid] = (vm_uuid, external_snap_uuid)

    return scoped_snaps

  def __list_scoped_snaps_in_pd(self, pd_name=None):
    """
    Combines the backup snapshot information from Cerebro with information
    from Aplos Intent Engine database.
    """
    scoped_snaps_from_cerebro = set()
    if pd_name:
      scoped_snaps_from_cerebro.update(
        self.__query_pd_scoped_snapshots(pd_name))
    else:
      pds = self.__query_all_pd_names()
      for pd in pds:
        scoped_snaps_from_cerebro.update(
          self.__query_pd_scoped_snapshots(pd.name))

    scoped_snaps_from_aplos = self.__get_all_scoped_snapshots_from_intent_db()
    for snap in scoped_snaps_from_cerebro:
      cerebro_snap_uuid = snap.cerebro_snap_uuid
      if cerebro_snap_uuid in scoped_snaps_from_aplos:
        snap.vm_uuid = scoped_snaps_from_aplos[cerebro_snap_uuid][0]
        snap.snap_uuid = scoped_snaps_from_aplos[cerebro_snap_uuid][1]
    return scoped_snaps_from_cerebro

  def __delete_scoped_snap_through_cerebro(self, cerebro_snap_uuid):
    """
    Deletes the backup snapshot specified by 'cerebro_snap_uuid' from Cerebro.
    """
    cerebro_client = CerebroInterfaceTool()
    arg = ModifySnapshotArg()
    arg.snapshot_uuid = NutanixUuid.from_hex(cerebro_snap_uuid).bytes
    arg.expiry_time_usecs = 0
    cerebro_client.modify_snapshot(arg)

  def print_scoped_snaps_in_pd(self, pd_name=None):
    """
    Formats and prints the scoped snapshots in the specified PD on the
    terminal screen.
    """
    scoped_snaps = self.__list_scoped_snaps_in_pd(pd_name)
    if not scoped_snaps or not len(scoped_snaps):
      print ("There are no backup snapshots to list.")
      return
    sorted_scoped_snaps = sorted(scoped_snaps)
    print ("There are %d backup snapshots.\n" % len(sorted_scoped_snaps))
    print ("Snap UUID\t\t\tVM UUID\t\t\tCreation Time\t\t\t"
           "Cerebro Snap UUID\t\t\tPD Name\n")
    for ss in sorted_scoped_snaps:
      print ("%s\t%s\t%s\t%s\t%s\n" %
             (ss.snap_uuid, ss.vm_uuid,
              datetime.datetime.fromtimestamp(
                ss.ctime / (1000 * 1000)).strftime('%Y-%m-%d %H:%M'),
              ss.cerebro_snap_uuid, ss.pd_name))

  def print_vm_scoped_snaps(self, vm_uuid=None):
    """
    Formats and prints the scoped snapshots of the specified VM on the
    terminal screen.
    """
    scoped_snaps = self.__list_scoped_snaps_in_pd()
    if not scoped_snaps or not len(scoped_snaps):
      print ("There are no backup snapshots to list.")
      return

    print ("Snap UUID\t\t\tVM UUID\t\t\tCreation Time\t\t\t"
           "Cerebro Snap UUID\t\t\tPD Name\n")
    count = 0
    for ss in scoped_snaps:
      if ss.vm_uuid == vm_uuid:
        count += 1
        print ("%s\t%s\t%s\t%s\t%s\n" %
               (ss.snap_uuid, ss.vm_uuid,
                datetime.datetime.fromtimestamp(
                  ss.ctime / (1000 * 1000)).strftime('%Y-%m-%d %H:%M'),
                ss.cerebro_snap_uuid, ss.pd_name))
    print ("There are %d backup snapshots.\n" % count)

  def delete_snapshot(self, snapshot_uuid):
    """
    Submits a request to delete the snapshot with UUID, 'snapshot_uuid'.
    """
    if not snapshot_uuid:
      return
    rep = self.session.delete(
            self.cluster_url + "/vm_snapshots/" + snapshot_uuid,
            verify=False)
    if rep.status_code in [200, 202]:
      print ("Successfully submitted the request to delete snapshot %s" %
             snapshot_uuid)
    else:
      print ("Failed to delete snapshot %s: %s" % (snapshot_uuid, rep.status_code))

  def delete_vm_snapshots(self, vm_uuid):
    """
    Deletes the backup snapshots of the virtual machine with UUID, 'vm_uuid'.
    """
    if not vm_uuid:
      print ("Please specify the virtual machine whose snapshots must be deleted")
      return
    scoped_snaps = self.__list_scoped_snaps_in_pd()
    if not scoped_snaps:
      print ("There are no backup snapshots to delete")
      return
    for ss in scoped_snaps:
      if ss.vm_uuid == vm_uuid:
        self.delete_snapshot(ss.snap_uuid)

  def delete_pd_snapshots(self, pd_name):
    """
    Deletes all the backup snapshots in the protection domain specified
    by 'pd_name'.
    """
    if not pd_name:
      print ("Please specify the protection domain whose snapshots must be deleted")
      return
    scoped_snaps = self.__list_scoped_snaps_in_pd(pd_name)
    if not scoped_snaps:
      print ("There are no backup snapshots to delete")
      return
    for ss in scoped_snaps:
      if ss.snap_uuid:
        # The snapshot has a corresponding intent spec. Hence, we need to
        # delete through intent gateway.
        self.delete_snapshot(ss.snap_uuid)
      else:
        # The snapshot does not have a corresponding intent spec. Delete
        # the snapshot through Cerebro instead.
        self.__delete_scoped_snap_through_cerebro(ss.cerebro_snap_uuid)

  def delete_all_snapshots(self):
    """
    Deletes all the backup snapshots in the cluster.
    """
    scoped_snaps = self.__list_scoped_snaps_in_pd()
    if not scoped_snaps:
      print ("There are no backup snapshots to delete")
      return
    for ss in scoped_snaps:
      if ss.snap_uuid:
        # The snapshot has a corresponding intent spec. Hence, we need to
        # delete through intent gateway.
        self.delete_snapshot(ss.snap_uuid)
      else:
        # The snapshot does not have a corresponding intent spec. Delete
        # the snapshot through Cerebro instead.
        self.__delete_scoped_snap_through_cerebro(ss.cerebro_snap_uuid)

if __name__ == "__main__":
  parser = argparse.ArgumentParser("backup_snapshots")
  parser.add_argument("user", help="Username of the account")
  parser.add_argument("password", action=Password, nargs='?',
                      help="Password of the account. Do not enter on the command line.")

  parser.add_argument("--list_for_pd",
                      help="List all the backup snapshots of the PD",
                      metavar="<Name of the PD>", type=str)

  parser.add_argument("--list_for_vm",
                      help="List all the backup snapshots of the VM",
                      metavar="<UUID of the VM>",
                      type=str)

  parser.add_argument("--list_all",
                      help="List all backup snapshots in the cluster",
                      action="store_true")

  parser.add_argument("--delete", help="Delete the snapshot specified by UUID",
                      metavar="<Snapshot UUID>",
                      type=str)

  parser.add_argument("--delete_for_vm",
                      help="Delete the snapshots of the virtual machine specified by UUID",
                      metavar="<UUID of the VM>",
                      type=str)

  parser.add_argument("--delete_for_pd",
                      help="Delete the snapshots of the protection domain specified by pd_name",
                      metavar="<Name of the PD>")

  parser.add_argument("--delete_all",
                      help="Delete all backup snapshots in the cluster",
                      action="store_true")

  args = parser.parse_args()

  client = BackupSnapshotClient(args.user, args.password)

  if args.list_all:
    client.print_scoped_snaps_in_pd(None)

  if args.list_for_pd:
    client.print_scoped_snaps_in_pd(args.list_for_pd)

  if args.list_for_vm:
    client.print_vm_scoped_snaps(args.list_for_vm)

  if args.delete:
    client.delete_snapshot(args.delete)

  if args.delete_for_vm:
    client.delete_vm_snapshots(args.delete_for_vm)

  if args.delete_for_pd:
    client.delete_pd_snapshots(args.delete_for_pd)

  if args.delete_all:
    client.delete_all_snapshots()
