#!/usr/bin/php

<?php

	// Script to list VM information on AHV based clusters
	// Author: Magnus Andersson, Sr Staff Solution Architect @Nutanix.
	// Port to php : Frederic Lhoest, Sr Technology Architect @PCCW Global.
	
	// Date : Jan 2019
	// Version : 0.8

	///////////////////////////////////////////////////////////////////
	// Includes section
	///////////////////////////////////////////////////////////////////
	
	include_once "nxCredentials.php";
	include_once "nxFramework.php";

	///////////////////////////////////////////////////////////////////
	// Init section
	///////////////////////////////////////////////////////////////////

	// Specify output file directory - Do not include a slash at the end
	$directory=".";

	// Specify the time zone you are working with	
	date_default_timezone_set('UTC');

	// Enable debug mode -> chatty screen output 
	$debug=true;

	// Specify Nutanix Cluster FQDN, User and Password
	// This is defined in the nxCredentials.php file

	// Get cluster name
	$clustername=json_decode(nxGetClusterDetails($clusterConnect))->name;

	// Get current date
	$currDate=date('Y-m-d');

	$outputFile=$directory."/".$currDate."-Nutanix_Cluster-".$clustername."-VM_Report_php.csv";
	print("Nutanix Prism Element ".nxColorOutput($clusterConnect["ip"])." will be used to collect information.\n");
	file_put_contents($outputFile,"VM Name,VM Description,Total Number of CPUs,Number of CPUs,Number of Cores per vCPU,Memory GB,Disk Usage GB, Disk Allocated GB,Number of VGs, VG Names,VG Disk Allocated GB,Flash Mode Enabled,AHV Snapshots,Local Protection Domain Snapshots,Remote Protection Domain Snapshots,IP Address/IP Addresses,Network Placement,AHV Host placement\n");

	$vmuuids=nxGetVMUuid($clusterConnect,"*");

	for($i=0;$i<count($vmuuids);$i++)
	{
		
		$current=$i+1;
		$res=nxGetVMDetails($clusterConnect,$vmuuids[$i]);

		$length=strlen("Creating reporting input for VM ".nxColorOutput($res->name)." now (".$current."/".count($vmuuids).") .....\n");
		for($l=0;$l<$length;$l++) print("=");
		print("\n");
		print("Creating reporting input for VM ".nxColorOutput($res->name)." now (".$current."/".count($vmuuids).") .....\n");
		for($l=0;$l<$length;$l++) print("=");
		
		// Get VM Name
		$vmname=$res->name;

		// Get VM Description - if any		
		if(isset($res->description))
		{
			$vmdescription=$res->description;
			$vmdescription=str_replace("\"","",$vmdescription);

			// If multiple line description, takes only first line
			$tmp=explode("\\n",$vmdescription);
			$vmdescription=$tmp[0];
		}
		else
		{
			$vmdescription="No VM Description Information Available";
		} 

		if($debug) print("\nVM Description : ".nxColorOutput($vmdescription)."\n");
		if($debug) print("VM Uuid : ".nxColorOutput($vmuuids[$i])."\n");

		// Get VM power state
		$vmpowerstate=$res->power_state;
		if($debug) print("Power State : ".nxColorOutput($vmpowerstate)."\n");
		
		// Get number of vCPU
		$num_vcpu=$res->num_vcpus;
		if($debug) print("vCPU : ".nxColorOutput($num_vcpu)."\n");
		
		// Get number of Cores
		$num_cores_per_vcpu=$res->num_cores_per_vcpu;
		if($debug) print("vCore : ".nxColorOutput($num_cores_per_vcpu)."\n");
		
		// Get memory configuration
		$memory=$res->memory_mb;
		$memory=$memory/1024;
		if($debug) print("Memory : ".nxColorOutput(($memory)." GB"));
		
		// Get IP info
		$nics=$res->vm_nics;
		$networks=array();
		
		// Get host UUID
		$hostuuid=$res->host_uuid;
		$ahvhostname=nxGetHostName($clusterConnect,$hostuuid);

		if($vmpowerstate=="off") $ahvhostplacement="VM Not Powered On";
		else $ahvhostplacement=$ahvhostname;
		if($debug) print("Host : ".nxColorOutput($ahvhostplacement)."\n");
		
		if(!count($nics))
		{
			$ipinfo="No IP Address Information Available";
			if($debug) print(nxColorOutput("No IP Address Information Available")."\n");
		}
		else
		{
			$ipinfo="";
			$networkname="";
			for($n=0;$n<count($nics);$n++)
			{
				$networks[$n]["ip"]=$nics[$n]->ip_address;
				$networks[$n]["uuid"]=$nics[$n]->network_uuid;
				
				// Get IP Address
				$ipinfo.=$networks[$n]["ip"]." ";

				// Get associated Network UUID
				if($debug) print("Network : ".nxColorOutput($networks[$n]["uuid"])."\n");

				// Get Network name for specific UUID
				$networks[$n]["netName"]=nxGetvNetName($clusterConnect,$networks[$n]["uuid"]);
				$networkname.=$networks[$n]["netName"]." ";
			}
			if($debug) print("IP : ".nxColorOutput($ipinfo)."\n");
			if($debug) print("Network Name : ".nxColorOutput($networkname)."\n");
		}		
		
		// Get AHV based snapshots
		
		$pdlocalSnapshots=nxGetVMLocalSnaps($clusterConnect,$vmuuids[$i]);
		$data=serialize($pdlocalSnapshots);
		$pdlocalSnapshots=substr_count($data,$vmuuids[$i]);
		if($debug) print("Protection Domain : Local Snapshots count for ".nxColorOutput($vmuuids[$i])." is : ".nxColorOutput($pdlocalSnapshots)."\n");

		$pdremotesnaps=nxGetVMRemoteSnaps($clusterConnect,$vmuuids[$i]);
		$data=serialize($pdremotesnaps);
		$pdremotesnaps=substr_count($data,$vmuuids[$i]);
		if($debug) print("Protection Domain : Remote Snapshots count for ".nxColorOutput($vmuuids[$i])." is : ".nxColorOutput($pdremotesnaps)."\n");
		
		$snaps=nxGetVMSnaps($clusterConnect,$vmuuids[$i]);
		$data=serialize($snaps);
		$snaps=substr_count($data,$vmuuids[$i]);
		if($debug) print("Snapshots count for ".nxColorOutput($vmuuids[$i])." is : ".nxColorOutput($snaps));
		
		$vmv3=nxGetVMDetailsV3($clusterConnect,$vmuuids[$i]);
		
		// Check for specific volume groups

		$data=serialize($vmv3);
		$vgroups=substr_count($data,"volume_group_reference");
		$vgtotalsize=0;
		$vgnames="";

		// Get all VGs and compare Uuid for VM attachment
		
		if($vgroups>1)
		{
			$VGs=nxGetVGs($clusterConnect);
			for($j=0;$j<count($VGs->entities);$j++)
			{
				$attachment=$VGs->entities[$j]->status->resources->attachment_list;
				for($k=0;$k<count($attachment);$k++)
				{
					if($debug) print("Attachment : ".$attachment[$k]->vm_reference->uuid."\n");
					if($attachment[$k]->vm_reference->uuid==$vmuuids[$i])									
					{
						$vgnames.=$VGs->entities[$j]->status->name." ";
						$vgsize=$VGs->entities[$j]->status->resources->size_bytes;
						$vgtotalsize+=$vgsize;
						if($debug) print("There is a match for VG : ".nxColorOutput($vgnames)."\n");
						if($debug) print("VG Size : ".nxColorOutput(($vgsize/1073741824)." GB")."\n");
// 						var_dump($VGs->entities[$j]);
					}
				}
			}
			if($vgtotalsize) $vgtotalsize=round($vgtotalsize/1073741824,2);
		}
		print("\n");
		
		if(!$vgroups) 
		{
			$vgbaseinfo="VGs not in use";
			$vgnames="N/A";
			$vgtotalsize="N/A";
		}
		else
		{
			$vgbaseinfo=$vgroups/2;
		}		

		if($debug) print("Volume Groups : ".nxColorOutput($vgbaseinfo)."\n");

		// Get vDisks
		
		$total_bytes=0;
		$flashmode=false;
		for($v=0;$v<count($res->vm_disk_info);$v++)
		{
			if($res->vm_disk_info[$v]->is_cdrom==false) $total_bytes+=$res->vm_disk_info[$v]->size;
			if($res->vm_disk_info[$v]->flash_mode_enabled) $flashmode=true;;
		}
		
		if($flashmode) $flashmode="Yes";
		else $flashmode="No";
		
		// Convert bytes to GB
		$total_bytes=round($total_bytes/1073741824,2);

		if($debug) print("Size GB : ".nxColorOutput($total_bytes." GB")."\n");
		if($debug) print("Flashmode : ".nxColorOutput($flashmode)."\n");
		
		// Get total disk size

		$tmpvDisks=nxGetVdisks($clusterConnect,$vmuuids[$i]);

		$totalUsed=0;
		for($u=0;$u<count($tmpvDisks);$u++)
		{
			$totalUsed+=$tmpvDisks[$u]["used_capacity"];
		}
		$totalUsed=round($totalUsed/1073741824,2);

		if($debug) print("Total used disk size = ".nxColorOutput($totalUsed)."\n");
		
		$totalCPU=(int)$num_vcpu*(int)$num_cores_per_vcpu;

		// Write data to file
		file_put_contents($outputFile,$vmname.",".$vmdescription.",".$totalCPU.",".$num_vcpu.",".$num_cores_per_vcpu.",".$memory.",".$totalUsed.",".$total_bytes.",".$vgbaseinfo.",".$vgnames.",".$vgtotalsize.",".$flashmode.",".$snaps.",".$pdlocalSnapshots.",".$pdremotesnaps.",".$ipinfo.",".$networkname.",".$ahvhostplacement."\n",FILE_APPEND);
	}
	print("\nEnd of script.\n\n");

?>
