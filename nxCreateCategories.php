<?php
	include_once "nxFramework.php";
	include_once "nxCredentialsPC.php";

	// -----------------------------------------
	// Configuration
	// -----------------------------------------

	$inputCSVFileName="categories.csv";
	$CSVSeparator=";";

	// -------------------------------------------
	// Functions reading categories from CSV files
	// -------------------------------------------

	function readCategoriesFromCSV($file,$CSVsep)
	{
		// Read input file

		$inputFile=$file;
		$rawCategories=file($inputFile);
		$categories=array();

		// Read line by line and create the array
	
		for($i=0;$i<count($rawCategories);$i++)
		{
			// cleanup the line from any special character or CR/LF
			$trimLine=trim($rawCategories[$i]," \t\n\r\0\x0B"); 
			$tmp=explode($CSVsep,$trimLine);
			$tmp=array_filter($tmp);

			$j=0;
			while($j<count($tmp))
			{
				$j++;
				if(@$tmp[$j]!=NULL) 
				{
					if($j==count($tmp)-1) @$categories[$tmp[0]].=$tmp[$j];
					else @$categories[$tmp[0]].=$tmp[$j].",";			
				}
			}
		}
		return $categories;
	}

	// =========================================
	// Main Entry Point
	// =========================================

	// Read Categories from file
	print("Reading categories from ".nxColorOutput($inputCSVFileName)."\n");
	$categories=readCategoriesFromCSV($inputCSVFileName,$CSVSeparator);

	print("Found ".nxColorOutput(count($categories))." category names\n");

	// Create category
	print("Creating categories in Prism Central.\n");
	nxpcCreateCategory($clusterConnect,$categories);

?>
