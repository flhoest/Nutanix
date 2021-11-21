<?php

	include_once('nxFramework.php');	
	
  	// Your Prism Central credentials
	$clusterConnect=array(
		"username" => "username",
		"password" => "password",
		"ip" => "1.2.3.4"
		);
		
	print("\n");
	print(" _______          __                .__        \n");
	print(" \      \  __ ___/  |______    ____ |__|__  ___\n");
	print(" /   |   \|  |  \   __\__  \  /    \|  \  \/  /\n");
	print("/    |    \  |  /|  |  / __ \|   |  \  |>    < \n");
	print("\____|__  /____/ |__| (____  /___|  /__/__/\_ \\\n");
	print("	\/                 \/     \/         \/\n");
	
	print("\t\t\t     nxGetOffVMs v 0.3\n\n");
	
	$data=nxpcGetOffVMs($clusterConnect);
	$offVMS=array();
	
	for($i=0;$i<count($data);$i++)
	{
		$offVMS[$i]["name"]=$data[$i]->status->name;
		$offVMS[$i]["cluster"]=$data[$i]->status->cluster_reference->name;

		$storage=$data[$i]->spec->resources->disk_list;
		$s=0;
	
		for($s=0;$s<count($storage);$s++)
		{
			if($storage[$s]->device_properties->device_type=="DISK") $s+=$storage[$s]->disk_size_bytes;
		}

		$offVMS[$i]["storage"]=$s;
		
	}

	// sort VM by "name"

	$sortColumn=array_column($offVMS,"name");
	array_multisort($sortColumn,SORT_ASC, $offVMS);

	// Display results
	
	print("+----------------------------------------------------------+------------------+-------------------+\n");
	print("| ".nxColorBold("VM Name")."                                                  | ".nxColorBold("Cluster Location")." |  ".nxColorBold("Reserved Storage")." |\n");
	print("+----------------------------------------------------------+------------------+-------------------+\n");

	$totalStorage=0;
	for($i=0;$i<count($offVMS);$i++)
	{
		print("| ".str_pad($offVMS[$i]["name"], 57, " ", STR_PAD_RIGHT));
		print("| ".str_pad($offVMS[$i]["cluster"]." ",17," ", STR_PAD_LEFT));
		print("| ".str_pad(formatBytes($offVMS[$i]["storage"]),17, " ",STR_PAD_LEFT));
		$totalStorage+=$offVMS[$i]["storage"];
		print(" |");
		print("\n");
	}
	print("+----------------------------------------------------------+------------------+-------------------+\n");

	$totalVMs=count($offVMS);
	$totalStorage=formatBytes($totalStorage);
	
	print("| ".str_pad(nxColorBold("Total "),68," ",STR_PAD_LEFT));
	print("| ".str_pad(nxColorBold($totalVMs),27," ",STR_PAD_LEFT)." ");
	print("| ".str_pad(nxColorBold($totalStorage),28," ",STR_PAD_LEFT)." |");
	print("\n");
	print("+-----------------------------------------------------------------------------+-------------------+\n\n");

?>
