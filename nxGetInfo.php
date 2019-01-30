#!/usr/bin/php 

<?php

/*
				 _______          __                .__        
				 \      \  __ ___/  |______    ____ |__|__  ___
				 /   |   \|  |  \   __\__  \  /    \|  \  \/  /
				/    |    \  |  /|  |  / __ \|   |  \  |>    < 
				\____|__  /____/ |__| (____  /___|  /__/__/\_ \
						\/                 \/     \/         \/
*/

	include_once "nxCredentials.php";
	include_once "nxFramework.php";

	// Define width of the screen (output)
	$padSize=100;
		
	$VMs=json_decode(nxGetVMsCount($clusterConnect));
	$cluster=json_decode(nxGetClusterDetails($clusterConnect));

	// Layout cluster details on screen
	// Note : this can be improved using some padding functions but I'm sure you can do it ;)

	print("+".str_pad("",$padSize-16,"-",STR_PAD_RIGHT)."+\n");
	print("| ".str_pad("Cluster Name : ".nxColorOutput($cluster -> name)."        |     Cluster model : ".nxColorOutput($cluster -> rackable_units[0] -> model_name),$padSize," ",STR_PAD_RIGHT)." |\n");

	print("+".str_pad("",$padSize-16,"-",STR_PAD_RIGHT)."+\n");

	print("| ".str_pad("vIP : ".nxColorOutput($cluster -> cluster_external_ipaddress)."               |     Data Service IP : ".nxColorOutput($cluster -> cluster_external_data_services_ipaddress),$padSize," ",STR_PAD_RIGHT)." |\n");

	print("+".str_pad("",$padSize-16,"-",STR_PAD_RIGHT)."+\n");

	// size of array block_serials equal to number of blocks
	$blocks=count($cluster -> block_serials);
	
	print("| ".str_pad("Number of Block(s) : ".nxColorOutput($blocks)."            |     Number of nodes : ".nxColorOutput($cluster -> num_nodes),$padSize," ",STR_PAD_RIGHT)." |\n");
	print("+".str_pad("",$padSize-16,"-",STR_PAD_RIGHT)."+\n");
	print("| ".str_pad("AOS version : ".nxColorOutput($cluster -> version),$padSize-8," ",STR_PAD_RIGHT)."|\n");
	print("| ".str_pad("NCC version : ".nxColorOutput($cluster -> ncc_version),$padSize-8," ",STR_PAD_RIGHT)."|\n");
	print("| ".str_pad("Hypervisor : ".nxColorOutput($cluster -> hypervisor_types[0]),$padSize-8," ",STR_PAD_RIGHT)."|\n");
	print("+".str_pad("",$padSize-16,"-",STR_PAD_RIGHT)."+\n");
	print("| ".str_pad("Total VMs (CVMs included): ".nxColorOutput($VMs -> metadata -> totalEntities),$padSize-8," ",STR_PAD_RIGHT)."|\n");
	print("+".str_pad("",$padSize-16,"-",STR_PAD_RIGHT)."+\n");

	// Convert free storage from bytes to TB
	$free_storage=formatBytes($cluster -> usage_stats -> {'storage.free_bytes'},2);
	print("| ".str_pad("Storage free space : ".nxColorOutput($free_storage),$padSize-8," ",STR_PAD_RIGHT)."|\n");
	$total_cluster_storage=formatBytes($cluster -> usage_stats -> {'storage.capacity_bytes'},2);
	$freeStorageinPercent=round($free_storage/$total_cluster_storage*100,1);
	print("| ".str_pad("Total Cluster size : ".nxColorOutput($total_cluster_storage)." (".nxColorOutput($freeStorageinPercent)." % Free)",$padSize," ",STR_PAD_RIGHT)." |\n");
	print("+".str_pad("",$padSize-16,"-",STR_PAD_RIGHT)."+\n");
	
?>
