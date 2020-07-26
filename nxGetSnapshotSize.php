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

	// =================================================================================
	//Get all VMs from cluster

	$totalSize=0;	
	$VMs=nxGetVMs($clusterConnect);
	$clusterName=json_decode(nxGetClusterDetails($clusterConnect))->name;

	print("==========================================================\n");
	print("Listing all VM snapshots for cluster ".nxColorOutput($clusterName)."\n");
	print("==========================================================\n");

	// Loop thru VMs
	
	$vmCount=count($VMs->entities);
	
	for($v=0;$v<$vmCount;$v++)
	{
		// Get VM UUID
		$vmUuid=nxGetVMUuid($clusterConnect,$VMs->entities[$v]->name);

		// Get all Snaps
		$snaps=nxGetVMSnaps($clusterConnect,$vmUuid);

		$displayVMcount=$v+1;
// 		print("+-------------------------------------------------------------+\n");
		print(nxColorOutput($VMs->entities[$v]->name)." (".$displayVMcount."/".$vmCount.")\n");

		// Get Snapshot Group_Uuid and Description (name)

		if(count($snaps))
		{
			for($i=0;$i<count($snaps);$i++)
			{
				// Get VM Container 
				$container=nxGetVMContainerName($clusterConnect,$vmUuid);

				// Get Snapshot Size
				$size=nxGetSnapSize($clusterConnect,$container,$snaps[$i]->group_uuid);
				$totalSize+=$size;

// 				print("Snapshot ".$i." ".nxColorOutput($snaps[$i]->snapshot_name)." Group_Uuid ".nxColorOutput($snaps[$i]->group_uuid)."\n");
				print("\tSnapshot ".$i." => ".nxColorOutput($snaps[$i]->snapshot_name)." => Size : ".nxColorOutput(formatBytes($size,2))."\n");
			}
		}	
		else
		{
			print("\tNo snapshot found!\n");
		}
	}
	
	print("Potential saving when snapshots removed : ".formatBytes($totalSize,2)."\n");

?>
