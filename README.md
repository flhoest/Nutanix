# Nutanix API Framework (written in Php)

```
This documentation is under review and considered as draft!
```
![Nutanix_logo](https://www.imperial.ac.uk/ImageCropToolT4/imageTool/uploaded-images/Logo_Nutanix--tojpeg_1475446675071_x2.jpg)                           ![php_logo](https://d1yjjnpx0p53s8.cloudfront.net/styles/logo-thumbnail/s3/062015/php_0.png?itok=W6WL-Rbh)

## Background

This project's goal is to provide anyone who needs to script automation, a collection of functions that call Nutnaix's APIs. I always wanted to created or query my Nutanix cluster form the command line and I had to start writing some functions to make a good use of what Nutanix is offering out of the box. I have tested this framework with Nutanix AOS 5.8.x and Nutanix CE. So far, so good ;)

## Getting Started

In order to make a good use of the provided framework, you first need to have php-cli installed. This is very easy and a lot of documentation on how to set it up on various platform (Windows, Linux & Mac OS) is widely available. The most common way to deploy php-cli is using your prefered package manager. Within the Linux world, just use : 
```
yum install php-cli -y
````

## File Listing

* nxFramework.php -> the list of functions used to query the Nutanix cluster using APIs;
* nxGetinfo.php -> an example of simple code that shows how to use the framework

## Prerequisites

Before continuing you need to have some basic information about your environment like Nutanix credentials and IP/Hostname. Once you have them, simply create a new file called nxCredentials.php from the nxCredentials.php_template file and set the following variable : 

```php 
<?php
	$clusterConnect=array(
		"username" => "username",
		"password" => "password",
		"ip" => "0.0.0.0"
	);
?>
```

## Function's Reference

The below section is a list of all existing functions in this framework.

### Index
````
formatBytes($bytes,$decimals=2,$system='metric')
nxAttachVG($clusterConnect,$vgId,$vmId)
nxCloneVG($clusterConnect,$uuid,$vgName)
nxColorBold($string)
nxColorOutput($string)
nxColorRed($string)
nxCreateVM($clusterConnect,$vmSpecs)
nxCreateVMSnap($clusterConnect,$VMUuid,$SnapDesc="")
nxDelSnaps($clusterConnect,$uuid)
nxDeleteVM($clusterConnect,$vmUuid)
nxDeleteVg($clusterConnect,$vgUuid)
nxDetachVG($clusterConnect,$vgId,$vmId)
nxGetClusterDetails($clusterConnect)
nxGetContainerName($clusterConnect,$ContainerUuid)
nxGetContainerUuid($clusterConnect,$ContainerName)
nxGetHostName($clusterConnect,$hostUuid)
nxGetLCMVersion($clusterConnect)
nxGetSWVersions($clusterConnect,$filter)
nxGetSnapSize($clusterConnect,$containerName,$groupUuid)
nxGetSnaps($clusterConnect)
nxGetVGDetails($clusterConnect,$vgName)
nxGetVGs($clusterConnect)
nxGetVMContainerName($clusterConnect,$vmUuid)
nxGetVMDetails($clusterConnect,$uuid)
nxGetVMDetailsV3($clusterConnect,$uuid)
nxGetVMLocalSnaps($clusterConnect,$uuid)
nxGetVMRemoteSnaps($clusterConnect,$uuid)
nxGetVMSnaps($clusterConnect,$uuid)
nxGetVMUuid($clusterConnect,$vmName)
nxGetVMs($clusterConnect)
nxGetVMsCount($clusterConnect)
nxGetVdisks($clusterConnect,$uuid)
nxGetvNetName($clusterConnect,$vNetUuid)
nxGetvNetUuid($clusterConnect,$vNetName)
nxpcApplyCategory($clusterConnect,$categories,$vmUuid,$clusterName,$specV)
nxpcCreateCategory($clusterConnect,$categories)
nxpcFlushCategories($clusterConnect,$vmUuid,$clusterName,$specV)
nxpcGetCategories($clusterConnect)
nxpcGetCategoryValues($clusterConnect,$categoryName)
nxpcGetClusterUuid($clusterConnect,$clusterName)
nxpcGetOffVMs($clusterConnect)
nxpcGetSpecV($clusterConnect,$vmUuid)
nxpcGetVMCategories($clusterConnect,$vmUuid)
nxpcGetVMUuid($clusterConnect,$vmName,$clusterName)
nxpcGetVMforCat($clusterConnect,$categories)
nxpcGetVMs($clusterConnect)
````
