<?php

	//////////////////////////////////////////////////////////////////////////////
	//                   Nutanix Php Framework version 0.95                     //
	//                      (c) 2018, 2019 - F. Lhoest                          //
	//////////////////////////////////////////////////////////////////////////////

/*
				 _______          __                .__        
				 \      \  __ ___/  |______    ____ |__|__  ___
				 /   |   \|  |  \   __\__  \  /    \|  \  \/  /
				/    |    \  |  /|  |  / __ \|   |  \  |>    < 
				\____|__  /____/ |__| (____  /___|  /__/__/\_ \
					\/                 \/     \/         \/
						
	// Function index in alphabetical order (total 37)
	//------------------------------------------------

	// formatBytes($bytes,$decimals=2,$system='metric')
	// nxAttachVG($clusterConnect,$vgId,$vmId)
	// nxCloneVG($clusterConnect,$uuid,$vgName)
	// nxColorOutput($string)
	// nxCreateVM($clusterConnect,$vmSpecs)
	// nxCreateVMSnap($clusterConnect,$VMUuid,$SnapDesc="")
	// nxDelSnaps($clusterConnect,$uuid)
	// nxDeleteVM($clusterConnect,$vmUuid)
	// nxDeleteVg($clusterConnect,$vgUuid)
	// nxDetachVG($clusterConnect,$vgId,$vmId)
	// nxGetClusterDetails($clusterConnect)
	// nxGetContainerName($clusterConnect,$ContainerUuid)
	// nxGetContainerUuid($clusterConnect,$ContainerName)
	// nxGetHostName($clusterConnect,$hostUuid)
	// nxGetSnapSize($clusterConnect,$containerName,$groupUuid)
	// nxGetSnaps($clusterConnect)
	// nxGetVGDetails($clusterConnect,$vgName)
	// nxGetVGs($clusterConnect)
	// nxGetVMContainerName($clusterConnect,$vmUuid)
	// nxGetVMDetails($clusterConnect,$uuid)
	// nxGetVMDetailsV3($clusterConnect,$uuid)
	// nxGetVMLocalSnaps($clusterConnect,$uuid)
	// nxGetVMRemoteSnaps($clusterConnect,$uuid)
	// nxGetVMSnaps($clusterConnect,$uuid)
	// nxGetVMUuid($clusterConnect,$vmName)
	// nxGetVMs($clusterConnect)
	// nxGetVMsCount($clusterConnect)
	// nxGetVdisks($clusterConnect,$uuid)
	// nxGetvNetName($clusterConnect,$vNetUuid)
	// nxGetvNetUuid($clusterConnect,$vNetName)
	// nxpcApplyCategory($clusterConnect,$categories,$vmName,$clusterName,$specV)
	// nxpcGetCategories($clusterConnect)
	// nxpcGetCategoryValues($clusterConnect,$categoryName)
	// nxpcGetClusterUuid($clusterConnect,$clusterName)
	// nxpcGetSpecV($clusterConnect,$vmUuid)
	// nxpcGetVMCategories($clusterConnect,$vmUuid)
	// nxpcGetVMUuid($clusterConnect,$vmName)
													
*/

	// ---------------------------------------------------------------------------
	// Function to populate a return variable (JSON text) with all cluster details
	// ---------------------------------------------------------------------------
	
	function nxGetClusterDetails($clusterConnect)
	{
		$API="/api/nutanix/v2.0/cluster/";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440/".$API);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		curl_close($curl);
		
		return $result;
	}
	
	// ---------------------------------------------------------------------------
	// Function to populate a return variable (JSON text) with total VMs details
	// ---------------------------------------------------------------------------
	
	function nxGetVMsCount($clusterConnect)
	{
		$API="PrismGateway/services/rest/v1/vms/?count=1";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440/".$API);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}
	
	// ------------------------------------------------------------------
	// Create a VM according to provided name, vCPU, memory and disk size
	// ------------------------------------------------------------------

	function nxCreateVM($clusterConnect,$vmSpecs)
	{
	     $API_URL="/PrismGateway/services/rest/v1/vms/";
		 $curl = curl_init();
		 $vNetUuid=nxGetvNetUuid($clusterConnect,$vmSpecs["netName"]);
		 $ContainerUuid=nxGetContainerUuid($clusterConnect,$vmSpecs["containerName"]);
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				 {
					\"memoryMb\": ".$vmSpecs["mem"].",
					\"name\": \"".$vmSpecs["name"]."\",
					\"numVcpus\": ".$vmSpecs["cpu"].",
					\"vmDisks\":
					[
						{
							\"isCdrom\": false,
							\"vmDiskCreate\": 
							{
								\"containerUuid\": \"".$ContainerUuid."\",
								\"sizeMb\": \"".$vmSpecs["HDD"]."\"
							}
						}
					],
					\"vmNics\": 
					[
						{
							\"networkUuid\": \"".$vNetUuid."\"
						}
					]
				}");
		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
		 print(curl_error($curl)."\n");

		 curl_close($curl);
		 return $result;
	}

	// --------------------------------------------------
	// Create a VM snapshot according to specific VM Uuid
	// --------------------------------------------------

	function nxCreateVMSnap($clusterConnect,$VMUuid,$SnapDesc=" ")
	{
	     $API_URL="/PrismGateway/services/rest/v2.0/snapshots/";
		 $curl = curl_init();
		 $vNetUuid=nxGetvNetUuid($clusterConnect,$vmSpecs["netName"]);
		 $ContainerUuid=nxGetContainerUuid($clusterConnect,$vmSpecs["containerName"]);
		 curl_setopt($curl, CURLOPT_POST, 1);
		 
		 $post="{
				  \"snapshot_specs\": [
					{
					  \"snapshot_name\": \"".$SnapDesc."\",
					  \"vm_uuid\": \"".$VMUuid."\"
					}
				  ]
				}";

		 curl_setopt($curl, CURLOPT_POSTFIELDS,$post);
		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
		 print(curl_error($curl)."\n");

		 curl_close($curl);
		 $result=json_decode($result);
		 
		if(isset($result->task_uuid)) return true;
		else return($result->message);
		 
	}

	// ------------------------------------------------------------------
	// Get list of volume groups API v3
	// ------------------------------------------------------------------

	function nxGetVGs($clusterConnect)
	{
	     $API_URL="/api/nutanix/v3/volume_groups/list";
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				 {
  					\"kind\": \"volume_group\"
				}");
		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
		 print(curl_error($curl)."\n");

		 curl_close($curl);
		 return json_decode($result);
	}

	// ------------------------------------------------------------------
	// Get specific VG info
	// ------------------------------------------------------------------

	function nxGetVGDetails($clusterConnect,$vgName)
	{
		// APIv3
// 	    $API_URL="/api/nutanix/v3/volume_groups/list";

	    $API_URL="/api/nutanix/v2.0/volume_groups";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440/".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		curl_close($curl);

		// Browse result to match $vgName

		$res=json_decode($result);
		$myVG=array();
	
		for($i=0;$i<count($res->entities);$i++)
		{
			if($res->entities[$i]->name == $vgName)
			{
				$myVG=$res->entities[$i];				
			}
		}

		return ($myVG);
	}

(	// ------------------------------------------------------------------
	// Clone volume group $uuid to name $vgName
	// ------------------------------------------------------------------
)
	function nxCloneVG($clusterConnect,$uuid,$vgName)
	{
	     $API_URL="/PrismGateway/services/rest/v2.0/volume_groups/".$uuid."/clone";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				{
				  \"name\": \"".$vgName."\"
				}");

		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);

		 curl_close($curl);
		 return $result;
	}

	// ------------------------------------------------------------------
	// Attach volume group $vgUUID to a VM $VMUUID
	// ------------------------------------------------------------------

	function nxAttachVG($clusterConnect,$vgId,$vmId)
	{
	     $API_URL="/PrismGateway/services/rest/v2.0/volume_groups/".$vgId."/attach";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				{
					  \"operation\": \"ATTACH\",
					  \"vm_uuid\": \"".$vmId."\"
				}");

		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
// 		 print(curl_error($curl)."\n");

		 curl_close($curl);
		 return $result;
	}

	// ------------------------------------------------------------------
	// Detach volume group $vgUUID to a VM $VMUUID
	// ------------------------------------------------------------------

	function nxDetachVG($clusterConnect,$vgId,$vmId)
	{
	     $API_URL="/PrismGateway/services/rest/v2.0/volume_groups/".$vgId."/detach";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				{
					  \"operation\": \"DETACH\",
					  \"vm_uuid\": \"".$vmId."\"
				}");

		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
// 		 print(curl_error($curl)."\n");

		 curl_close($curl);
		 return $result;
	}

	// ------------------------------------------------------------------
	// Delete Volume Group based on provided unique Uuid
	// ------------------------------------------------------------------
	
	function nxDeleteVg($clusterConnect,$vgUuid)
	{
	     $API_URL="//PrismGateway/services/rest/v2.0/volume_groups/".$vgUuid;
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
   		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); 
   		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL."/");
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
		 if(curl_error($curl) != NULL)
		 {
		 	print(curl_error($curl));
		 }

		 curl_close($curl);
		 return $result;
	}

	// ------------------------------------------------------------------
	// Delete VM based on provided unique Uuid
	// ------------------------------------------------------------------
	
	function nxDeleteVM($clusterConnect,$vmUuid)
	{
	     $API_URL="/PrismGateway/services/rest/v2.0/vms/";
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
   		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); 
   		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL.$vmUuid."/");
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
		 if(curl_error($curl) != NULL)
		 {
		 	print(curl_error($curl));
		 }

		 curl_close($curl);
		 return $result;
	}

	// ------------------------------------------------------------------
	// Get associated Uuid from a VM name - $vmName
	// Assumption : VM name is unique through the entire cluster
	// is $vmName = "*" returns all UUids
	// ------------------------------------------------------------------
	
	
	function nxGetVMUuid($clusterConnect,$vmName)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/vms/?include_vm_disk_config=true&include_vm_nic_config=true";
		// Step 1 : cURL to get list of all VMs
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
// 		print(curl_error($curl)."\n");
		curl_close($curl);
		
		// Step 2 : Browse result to find relevant matching VM name <-> VM Uuid

		$result=json_decode($result);
		
		$myArray=$result -> entities;

		$myOtherArray=array();

		if($vmName!='*')
		{
			$i=0;
			while(strtolower($myArray[$i]->name)!=strtolower($vmName) && $i<count($myArray))
			{
				$i++;
// 				print($myArray[$i]->name." <-> ".$myArray[$i]->uuid."\n");
			}
			return $myArray[$i] -> uuid;
		}
		else
		{			
			for($i=0;$i<count($myArray);$i++)
			{
			
				$myOtherArray[$i]=$myArray[$i] -> uuid;
			}
			return $myOtherArray;
		}
	}

	// ------------------------------------------------------------------
	// Get associated host name from host Uuid
	// ------------------------------------------------------------------

	function nxGetHostName($clusterConnect,$hostUuid)
	{

        $API_URL="/PrismGateway/services/rest/v2.0/hosts/?search_string=".$hostUuid;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		print(curl_error($curl)."\n");
		curl_close($curl);
		
		$result=json_decode($result);
		return($result -> entities[0]->name);
	}
	
	// ---------------------------------------------------------------------------------
	// Get Associated Uuid from specified vNet Name
	// ---------------------------------------------------------------------------------
	
	function nxGetvNetUuid($clusterConnect,$vNetName)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/networks/";
		// Step 1 : cURL to get list of all vNets
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);
		
		// Step 2 : Browse result to find relevant matching vNet name <-> VM Uuid

		$res=json_decode($result);
		$myArray=$res -> entities;
		
		$i=0;
		while($myArray[$i] -> name!=$vNetName && $i<count($myArray))
		{
			$i++;
		}
		
		return $myArray[$i] -> uuid;
	}
	
	// ---------------------------------------------------------------------------------
	// Get Associated vNetName from specified vNet Uuid
	// ---------------------------------------------------------------------------------

	function nxGetvNetName($clusterConnect,$vNetUuid)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/networks/";
		// Step 1 : cURL to get list of all vNets
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);
		
		// Step 2 : Browse result to find relevant matching vNet name <-> VM Uuid

		$res=json_decode($result);
		$myArray=$res -> entities;

		$i=0;
		while($myArray[$i] -> uuid!=$vNetUuid && $i<count($myArray))
		{
			$i++;
		}
		
		return $myArray[$i] -> name;
	}
	
	// ---------------------------------------------------------------------------------
	// Get Associated Uuid from specified container Name
	// ---------------------------------------------------------------------------------
	
	function nxGetContainerUuid($clusterConnect,$ContainerName)
	{
        $API_URL="/PrismGateway/services/rest/v1/containers/";
		// Step 1 : cURL to get list of all Containers
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);
		
		// Step 2 : Browse result to find relevant matching vNet name <-> VM Uuid

		$res=json_decode($result);
		$myArray=$res -> entities;
		
		$i=0;
		while($myArray[$i] -> name!=$ContainerName && $i<count($myArray))
		{
			$i++;
		}
		return $myArray[$i] -> containerUuid;
	}

	// ---------------------------------------------------------------------------------
	// Get Associated Uuid from specified container Name
	// ---------------------------------------------------------------------------------
	
	function nxGetContainerName($clusterConnect,$ContainerUuid)
	{
        $API_URL="/PrismGateway/services/rest/v1/containers/";
		// Step 1 : cURL to get list of all Containers
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);
		
		// Step 2 : Browse result to find relevant matching vNet name <-> VM Uuid

		$res=json_decode($result);
		$myArray=$res -> entities;
		
		$i=0;
		while($myArray[$i] -> containerUuid!=$ContainerUuid && $i<count($myArray))
		{
			$i++;
		}
		return $myArray[$i] -> name;
	}
	
	function nxGetVMContainerName($clusterConnect,$vmUuid)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/vms/".$vmUuid."?include_vm_disk_config=true";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);

		$diskInfo=json_decode($result)->vm_disk_info;
		
		for($i=0;$i<count($diskInfo);$i++)
		{
			if($diskInfo[$i]->disk_address->device_bus == "scsi")
			{
				$containerName=$diskInfo[$i]->disk_address->ndfs_filepath;
				$tmp=explode("/", $containerName);
				return($tmp[1]);
			} 
		}
		
		return(false);
	}

	// ---------------------------------------------------------------------------------
	// Get List of Nutanix VMs
	// ---------------------------------------------------------------------------------
	
	function nxGetVMs($clusterConnect)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/vms/";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);

		return(json_decode($result));
			
	}

	// ---------------------------------------------------------------------------------
	// Get details of specific VM
	// ---------------------------------------------------------------------------------
	
	function nxGetVMDetails($clusterConnect,$uuid)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/vms/".$uuid."?include_vm_disk_config=true&include_vm_nic_config=true";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);
		return(json_decode($result));
			
	}
	
	// ---------------------------------------------------------------------------------
	// Get details of specific VM api v3
	// ---------------------------------------------------------------------------------
	
	function nxGetVMDetailsV3($clusterConnect,$uuid)
	{
        $API_URL="/api/nutanix/v3/vms/".$uuid;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);

		return(json_decode($result));
	}

	// -----------------------------------------------------------------------------------------
	// Get all vDisk details attached to a specific VM (by uuid) if no disk found return "false"
	// -----------------------------------------------------------------------------------------
	
	function nxGetVdisks($clusterConnect,$uuid)
	{
		$ArrayResult=array();
        $API_URL="/api/nutanix/v2.0/virtual_disks/";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);
		$result=json_decode($result);
		
		$j=0;
		for($i=0;$i<count($result->entities);$i++)
		{
			if($result->entities[$i]-> attached_vm_uuid == $uuid)
			{
// 				print("Disk is matching specify VM : ".$result->entities[$i]-> attached_vmname."\n");
				$ArrayResult[$j]["attached_vmname"]=$result->entities[$i]-> attached_vmname;
				$ArrayResult[$j]["total_capacity"]=$result->entities[$i]-> disk_capacity_in_bytes;
				$ArrayResult[$j]["used_capacity"]=$result->entities[$i]-> stats -> controller_user_bytes;
				$j++;
			}
		}
		
		if(!$ArrayResult) return false;
		else return($ArrayResult);
	}
	
	// ---------------------------------------------------------------------------------
	// Get number snapshots for specific VM Uuid
	// ---------------------------------------------------------------------------------

	function nxGetVMSnaps($clusterConnect,$uuid)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/snapshots/?vm_uuid=".$uuid;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);

		return(json_decode($result)->entities);
	}

	// ---------------------------------------------------------------------------------
	// Get All snapshots
	// ---------------------------------------------------------------------------------

	function nxGetSnaps($clusterConnect)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/snapshots/";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);
		$result=json_decode($result);

		return($result->entities);
		
	}
	
	// ---------------------------------------------------------------------------------
	// Get snapshot size of a VM
	// ---------------------------------------------------------------------------------

	function nxGetSnapSize($clusterConnect,$containerName,$groupUuid)
	{
        $API_URL="/api/nutanix/v2.0/vdisks/?path=".$containerName."/.acropolis/snapshot/".$groupUuid."/vmdisk";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);

		$result=json_decode($result)->entities;
	
		$totalUsed=0;
		$totalSize=0;
		for($i=0;$i<count($result);$i++)
		{
// 			print("Total Size : ".formatBytes($result[$i]->total_size,2)."\n");
// 			print("Used Size : ".formatBytes($result[$i]->used_size,2)."\n");
			$totalUsed+=$result[$i]->used_size;
			
		}
		return($totalUsed);
	}
	

	// ---------------------------------------------------------------------------------
	// Get number of local snapshots in protection domain for specific VM Uuid
	// ---------------------------------------------------------------------------------

	function nxGetVMLocalSnaps($clusterConnect,$uuid)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/protection_domains/dr_snapshots/?full_details=true";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);

		return(json_decode($result));
	}

	// ---------------------------------------------------------------------------------
	// Get number of remote snapshots in protection domain for specific VM Uuid
	// ---------------------------------------------------------------------------------

	function nxGetVMRemoteSnaps($clusterConnect,$uuid)
	{
        $API_URL="/PrismGateway/services/rest/v2.0/remote_sites/dr_snapshots/?full_details=true";
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);
		curl_close($curl);

		return(json_decode($result));
	}
	
	// ---------------------------------------------------------------------------------
	// Remove specified Snapshot Uuid
	// ---------------------------------------------------------------------------------

	function nxDelSnaps($clusterConnect,$uuid)
	{
	     $API_URL="/PrismGateway/services/rest/v2.0/snapshots/".$uuid;
		 $curl = curl_init();
		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
   		 curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); 
   		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);
		 if(curl_error($curl) != NULL)
		 {
		 	print(curl_error($curl));
		 }

		 curl_close($curl);
		 return $result;
	}
	
	// -----------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------
	//                       Prism Central Specific functions
	// -----------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------
	
	// -------------------------------------------
	// <PRISM CENTRAL> List all defined categories
	// -------------------------------------------

	function nxpcGetCategories($clusterConnect)
	{
	     $API_URL="/api/nutanix/v3/categories/list";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				{
				  \"kind\": \"category\"
				}");

		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);

		 curl_close($curl);
		 return json_decode($result)->entities;	
	
	}

	// ------------------------------------------------------------------
	// <PRISM CENTRAL> List all possible values for a given category name
	// ------------------------------------------------------------------

	function nxpcGetCategoryValues($clusterConnect,$categoryName)
	{
	     $API_URL="/api/nutanix/v3/categories/".$categoryName."/list";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				{
				  \"kind\": \"category\"
				}");

		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);

		 curl_close($curl);
		 return json_decode($result)->entities;	
	
	}
	
	// ----------------------------------------------------
	// <PRISM CENTRAL> Get categories for given VM
	// ----------------------------------------------------

	function nxpcGetVMCategories($clusterConnect,$vmUuid)
	{
	     $API_URL="/api/nutanix/v3/batch";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
			{
			  \"execution_order\": \"NON_SEQUENTIAL\",
			  \"action_on_failure\": \"CONTINUE\",
			  \"api_request_list\": [
				{
				  \"operation\": \"GET\",
				  \"path_and_params\": \"/api/nutanix/v3/mh_vms/".$vmUuid."\"
				}
			  ],
			  \"api_version\": \"3.0\"
			}"				
			);

		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = curl_exec($curl);

		 curl_close($curl);
		 return json_decode($result)->api_response_list[0]->api_response->metadata;	
	}

	// ----------------------------------------------------
	// <PRISM CENTRAL> Get VM uuid from VM Name
	// ----------------------------------------------------
	
	function nxpcGetVMUuid($clusterConnect,$vmName)
	{
	     $API_URL="/api/nutanix/v3/vms/list";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				{
				  \"kind\": \"vm\",
				  \"sort_attribute\": \"vm_name\",
				  \"filter\": \"vm_name == ".$vmName."\",
				  \"length\": 1,
				  \"sort_order\": \"ASCENDING\",
				  \"offset\": 0
				}
				");
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = json_decode(curl_exec($curl));

		curl_close($curl);

		return($result->entities[0]->metadata->uuid);	
	}

	// ----------------------------------------------------
	// <PRISM CENTRAL> Get Cluster uuid from Cluster Name
	// ----------------------------------------------------

	function nxpcGetClusterUuid($clusterConnect,$clusterName)
	{
	     $API_URL="/api/nutanix/v3/clusters/list";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
				{
				  \"kind\": \"cluster\",
				  \"length\": 1,
				  \"sort_order\": \"ASCENDING\"
				}
				");
		curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = json_decode(curl_exec($curl))->entities;
		curl_close($curl);

		$found=FALSE;
		for($i=0;$i<count($result);$i++)
		{
			if($result[$i]->status->name==$clusterName)
			{
				$found=TRUE;
				$uuid=$result[$i]->metadata->uuid;
			}
// 			print($result[$i]->status->name."\n");
		}

		if($found) return $uuid;
		else return FALSE;

	}

	// ----------------------------------------------------
	// <PRISM CENTRAL> Set VM category
	// ----------------------------------------------------
		
	function nxpcApplyCategory($clusterConnect,$categories,$vmName,$clusterName,$specV)
	{
		// 1 : Get VM uuid
		$vmUuid=nxpcGetVMUuid($clusterConnect,$vmName);
				
		// 2 : Get Cluster uuid
		$clusterUuid=nxpcGetClusterUuid($clusterConnect,$clusterName);

		// 3 : Apply new categories		
	    $API_URL="/api/nutanix/v3/batch";
		$curl = curl_init();

		$config="
				{
				  \"execution_order\": \"NON_SEQUENTIAL\",
				  \"action_on_failure\": \"CONTINUE\",
				  \"api_request_list\": [
					{
					  \"operation\": \"PUT\",
					  \"path_and_params\": \"/api/nutanix/v3/mh_vms/".$vmUuid."\",
					  \"body\": {
						\"spec\": {
						  \"resources\": {},
						  \"cluster_reference\": {
							\"kind\": \"cluster\",
							\"name\": \"".$clusterName."\",
							\"uuid\": \"".$clusterUuid."\"
						  }
						},
						\"api_version\": \"3.0\",
						\"metadata\": {
						  \"kind\": \"mh_vm\",
						  \"spec_version\": ".$specV.",
  						  \"categories_mapping\": {\n";

		// build category section based on $categories array

		$numItems=count($categories);
		$i=0;
		
		foreach($categories as $key => $value)
		{
			// Check if comma in $value, then it means many values for that key
			if(strpos($value,","))
			{
				$i++;
				$values=explode(",",$value);

				$config.="\"".$key."\" : [";
				for($j=0;$j<count($values);$j++)
				{
					$config.="\"".$values[$j]."\"";
					if($j<count($values)-1) $config.=",";
				}
				$config.="],";
			}
			else
			{
				if(++$i===$numItems) $config.="\"".$key."\" : [\"".$value."\"]\n";
				else $config.="\"".$key."\" : [\"".$value."\"], ";
			} 

		}	
		$config.=" \n},
						  \"categories\": {},
						  \"use_categories_mapping\": true
						}
					  }
					}
				  ],
				  \"api_version\": \"3.0\"
				}";

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,$config);
		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $info=curl_getinfo($curl,CURLINFO_HTTP_CODE);
		 $result = curl_exec($curl);

		 curl_close($curl);

		// If 200 is retured all is ok
		 return $info;
	}
	
	// ----------------------------------------------------
	// <PRISM CENTRAL> Get VM Specification version
	// ----------------------------------------------------
	
	function nxpcGetSpecV($clusterConnect,$vmUuid)	
	{
	     $API_URL="/api/nutanix/v3/batch";
		 $curl = curl_init();

		 curl_setopt($curl, CURLOPT_POST, 1);
		 curl_setopt($curl, CURLOPT_POSTFIELDS,"
			{
			  \"execution_order\": \"NON_SEQUENTIAL\",
			  \"action_on_failure\": \"CONTINUE\",
			  \"api_request_list\": [
				{
				  \"operation\": \"GET\",
				  \"path_and_params\": \"/api/nutanix/v3/mh_vms/".$vmUuid."\"
				}
			  ],
			  \"api_version\": \"3.0\"
			}"				
			);

		 curl_setopt($curl, CURLOPT_USERPWD, $clusterConnect["username"].":".$clusterConnect["password"]);
		 curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		 curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		 curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		 curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		 curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		 curl_setopt($curl, CURLOPT_URL, "https://".$clusterConnect["ip"].":9440".$API_URL);
		 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		 $result = json_decode(curl_exec($curl));
		 curl_close($curl);

		return($result->api_response_list[0]->api_response->metadata->spec_version);
	}
	
	
	
	
	// ---------------------------------------------------------------------------
	// Display a string in Nutanix Green!!!
	// ---------------------------------------------------------------------------
	
	function nxColorOutput($string)
	{
		return ("\033[32m".$string."\033[0m");
	}

	// ---------------------------------------------------------------------------
	// Display size (bytes) in human redable format
	// ---------------------------------------------------------------------------
	
	function formatBytes($bytes, $decimals = 2, $system = 'metric')
	{
		$mod = ($system === 'binary') ? 1024 : 1000;
		$units = array(
			'binary' => array('B','KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB',
			),
			'metric' => array('B','KB','MB','GB','TB','PB','EB','ZB','YB',	
			),
		);

		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f %s", $bytes / pow($mod, $factor), $units[$system][$factor]);
	}


?>
