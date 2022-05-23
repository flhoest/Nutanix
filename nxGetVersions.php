<?php

	// =====================================================
	// Get Nutanix software version on a list of clusters
	// =====================================================

	// -----------------------------------------------------
	// Includes section
	// -----------------------------------------------------

	include_once('nxFramework.php');

	// -----------------------------------------------------
	// Variables definition
	// -----------------------------------------------------

	// =====================
	// Clusters
	// =====================

 	$clusters=array(
 						0 => array(	"username" => "username",
 									"password" => "password",
 									"ip" => "192.168.1.1"),
 						1 => array(	"username" => "username",
 									"password" => "password",
 									"ip" => "192.168.1.2"),
 					);

	// -----------------------------------------------------
	// Main entry point
	// -----------------------------------------------------

	print("\n");
	print(" _______          __                .__        \n");
	print(" \      \  __ ___/  |______    ____ |__|__  ___\n");
	print(" /   |   \|  |  \   __\__  \  /    \|  \  \/  /\n");
	print("/    |    \  |  /|  |  / __ \|   |  \  |>    < \n");
	print("\____|__  /____/ |__| (____  /___|  /__/__/\_ \\\n");
	print("	\/                 \/     \/         \/\n");

	print("\t\t\t     nxGetVersion v 0.9\n\n");

	$ver=array();
	print("\nPlease wait while collecting data from ".nxColorBold(count($clusters))." cluster(s)...\n");

	// Main loop thru all pre-defined clusters
	for($i=0;$i<count($clusters);$i++)
	{
		$clusterConnect=array(
			"username" => $clusters[$i]["username"],
			"password" => $clusters[$i]["password"],
			"ip" => $clusters[$i]["ip"]
			);

		// Getting cluster name
		$data=json_decode(nxGetClusterDetails($clusterConnect));

		// Getting number of nodes
		$ver[$i]["nodes"]=$data->num_nodes;

		$name=str_replace(".pccwglobal.com", "",$data->name);
		$ver[$i]["name"] = $name;
		print("\n-> ".nxColorOutput(str_pad($ver[$i]["name"],15,".",STR_PAD_RIGHT)));

		// Get all software version from LCM module

		// Get PC version
		$pc=nxGetSWVersions($clusterConnect,"\"entity_model==PC\"");
 		if(isset($pc->data->entities[0]->version)) $pc=$pc->data->entities[0]->version;
 		else $pc="";
 		$ver[$i]["PC"]=$pc;

		// Get LCM version
		$lcm=nxGetLCMVersion($clusterConnect);
		$ver[$i]["LCM"] = $lcm;

		// Get Foundation version
		$data=nxGetSWVersions($clusterConnect,"\"entity_model==Foundation Platforms\"");
		$data=$data->data->entities;
		$ver[$i]["Foundation"]=$data;

		// Get unique hypervisor version
		$tmp=array();
		for($h=0;$h<count($ver[$i]["Foundation"]);$h++)
		{
			$tmp[$h]=$ver[$i]["Foundation"][$h]->version;
		}

		$tmp2=array_unique($tmp);
		$ver[$i]["Foundation"]=array_values($tmp2);

		// Get NCC version
		$data=nxGetSWVersions($clusterConnect,"\"entity_model==NCC\"");
		$data=$data->data->entities;
		$ver[$i]["NCC"]=$data;

		// Get AOS version
		$data=nxGetSWVersions($clusterConnect,"\"entity_model==AOS\"");
		$data=$data->data->entities;
		unset($data[0]->available_version_list);
		$ver[$i]["AOS"]=$data;

		// Get Hypervisor version
		$data=nxGetSWVersions($clusterConnect,"\"entity_class==Hypervisor\"");
		@$data=$data->data->entities;

		// Remove unusable upgrade list from array
		for($j=0;$j<count($data);$j++)
		{
			unset($data[$j]->available_version_list);
		}
		$ver[$i]["Hypervisor"]=$data;
		@sort($ver[$i]["Hypervisor"]);

		// Get unique hypervisor version
		$tmp=array();
		for($h=0;$h<count($ver[$i]["Hypervisor"]);$h++)
		{
			$tmp[$h]=$ver[$i]["Hypervisor"][$h]->version;
		}

		$tmp2=array_unique($tmp);
		$ver[$i]["Hypervisor"]=array_values($tmp2);

		// Get File Server version
		$data=nxGetSWVersions($clusterConnect,"\"entity_model==File Server\"");
		$data=$data->data->entities;

		// Remove unusable upgrade list from array
		for($j=0;$j<count($data);$j++)
		{
			unset($data[$j]->available_version_list);
		}
		$ver[$i]["FileServer"]=$data;

		// Get unique fileserver version
		$tmp=array();
		for($h=0;$h<count($ver[$i]["FileServer"]);$h++)
		{
			$tmp[$h]=$ver[$i]["FileServer"][$h]->version;
		}

		$tmp2=array_unique($tmp);
		$ver[$i]["FileServer"]=array_values($tmp2);

		print(" Done!");
	}

	print("\n\nData collected, diplaying results...\n\n");

	print("+------------------------\n");
	print("| ".nxColorBold("Nutanix Clusters Version Summary")."\n");

	// first line top table
	print("+".str_pad("",20,"-",STR_PAD_BOTH));
	print("+".str_pad("",13,"-",STR_PAD_BOTH));
	print("+".str_pad("",62,"-",STR_PAD_BOTH));
	print("+".str_pad("",17,"-",STR_PAD_BOTH));
	print("+".str_pad("",13,"-",STR_PAD_BOTH));
	print("+".str_pad("",27,"-",STR_PAD_BOTH));
	print("+".str_pad("",20,"-",STR_PAD_BOTH));
	print("+\n");

	// second line top table - with labels

	print("| ".nxColorBold(str_pad("Cluster Name (#n)",19," ",STR_PAD_RIGHT)));
	print("| ".nxColorBold(str_pad("AOS",12," ",STR_PAD_BOTH)));
	print("| ".nxColorBold(str_pad("Hypervisor",61," ",STR_PAD_BOTH)));
	print("| ".nxColorBold(str_pad("LCM",16," ",STR_PAD_BOTH)));
	print("| ".nxColorBold(str_pad("NCC",12," ",STR_PAD_BOTH)));
	print("| ".nxColorBold(str_pad("File Server",26," ",STR_PAD_BOTH)));
	print("| ".nxColorBold(str_pad("Foundation",19," ",STR_PAD_BOTH)));
	print("|\n");

	// last line top table - same as first one
	print("+".str_pad("",20,"-",STR_PAD_BOTH));
	print("+".str_pad("",13,"-",STR_PAD_BOTH));
	print("+".str_pad("",62,"-",STR_PAD_BOTH));
	print("+".str_pad("",17,"-",STR_PAD_BOTH));
	print("+".str_pad("",13,"-",STR_PAD_BOTH));
	print("+".str_pad("",27,"-",STR_PAD_BOTH));
	print("+".str_pad("",20,"-",STR_PAD_BOTH));
	print("+\n");

	// =-=-=-=-=-=-=-=-=--=-=
	// Display collected data
	// =-=-=-=-=-=-=-=-=--=-=

	for($i=0;$i<count($ver);$i++)
	{
		// Cluster Name
		if(isset($ver[$i]["nodes"])) print("| ".nxColorOutput(str_pad($ver[$i]["name"]." (".$ver[$i]["nodes"].") ", 19, " ", STR_PAD_RIGHT)));
		else print("| ".nxColorOutput(str_pad($ver[$i]["name"]." (n/a) ", 19, " ", STR_PAD_RIGHT)));

		// AOS Version
		print("| ".nxColorOutput(str_pad($ver[$i]["AOS"][0]->version, 12, " ", STR_PAD_BOTH)));

		// Hypervisor(s)
		// Loop thru nodes to display hypervisor version on each node
		// If Prism Central detected, hypervisor is not displayed but PC version instead
		if($ver[$i]["PC"])
		{
			print("| ".nxColorOutput(str_pad($ver[$i]["PC"],61," ",STR_PAD_BOTH)));
		}
		else
		{
			$pHypervisor="";
			for($n=0;$n<count($ver[$i]["Hypervisor"]);$n++)
			{
				if (count($ver[$i]["Hypervisor"]) == 1)
					$pHypervisor=$pHypervisor.$ver[$i]["Hypervisor"][$n];
				elseif ($n==count($ver[$i]["Hypervisor"])-1)
					$pHypervisor=$pHypervisor.$ver[$i]["Hypervisor"][$n];
				else
					$pHypervisor=$pHypervisor.$ver[$i]["Hypervisor"][$n].", ";
			}
			if($pHypervisor) print("| ".nxColorOutput(str_pad($pHypervisor,61," ",STR_PAD_BOTH)));
			else print("| ".str_pad("n/a",61," ",STR_PAD_BOTH));
		}
		// LCM
		print("| ".nxColorOutput(str_pad($ver[$i]["LCM"], 16, " ", STR_PAD_BOTH)));

		// NCC
		print("| ".nxColorOutput(str_pad($ver[$i]["NCC"][0]->version, 12, " ", STR_PAD_BOTH)));

		// File Server
		$pFS="";
		for($n=0;$n<count($ver[$i]["FileServer"]);$n++)
		{
			if (count($ver[$i]["FileServer"]) == 1)
				$pFS=$pFS.$ver[$i]["FileServer"][$n];
			elseif ($n==count($ver[$i]["FileServer"])-1)
				$pFS=$pFS.$ver[$i]["FileServer"][$n];
			else
				$pFS=$pFS.$ver[$i]["FileServer"][$n].", ";
		}

		if($pFS) print("| ".nxColorOutput(str_pad($pFS,26," ",STR_PAD_BOTH)));
		else print("| ".str_pad("n/a",26," ",STR_PAD_BOTH));

		//Foundation
		$pFoundation="";
		for($n=0;$n<count($ver[$i]["Foundation"]);$n++)
		{
			if (count($ver[$i]["Foundation"]) == 1)
				$pFoundation=$pFoundation.$ver[$i]["Foundation"][$n];
			elseif ($n==count($ver[$i]["Foundation"])-1)
				$pFouncation=$pFoundation.$ver[$i]["Foundation"][$n];
			else
				$pFoundation=$pFoundation.$ver[$i]["FileServer"][$n].", ";
		}

		if($pFoundation) print("| ".nxColorOutput(str_pad($pFoundation,18," ",STR_PAD_BOTH)));
		else print("| ".str_pad("n/a",18," ",STR_PAD_BOTH));
		print(" |\n");

	}

	// last line top table - same as first one
	print("+".str_pad("",20,"-",STR_PAD_BOTH));
	print("+".str_pad("",13,"-",STR_PAD_BOTH));
	print("+".str_pad("",62,"-",STR_PAD_BOTH));
	print("+".str_pad("",17,"-",STR_PAD_BOTH));
	print("+".str_pad("",13,"-",STR_PAD_BOTH));
	print("+".str_pad("",27,"-",STR_PAD_BOTH));
	print("+".str_pad("",20,"-",STR_PAD_BOTH));
	print("+\n");

?>
