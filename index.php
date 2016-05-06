<?php
ini_set('display_errors', 'On');
ini_set('html_errors', 0);
//include('libs/xmltoarray.php');

$apiKeyFile = fopen("../apiKey.cnf", 'r') or die ("Unable to open ../apiKey.cnf");
$apiKey = rtrim(fgets($apiKeyFile)); fclose($apiKeyFile);

if (!extension_loaded('simplexml')) {
	echo 'SimpleXML is NOT loaded! Please install SimpleXML - Apache will require php5-cli</br> #apt-get install php5-cli'; exit;
} else {
	$xmlNode = "https://partners.dattobackup.com/xml2.php?type=status&apiKey={$apiKey}";
	$sxml = simplexml_load_file($xmlNode);
}

$sTable = "";
$fleetSize = $sxml->attributes();




//------ Testing Block ------
// foreach($sxml->Device->BackupVolumes->BackupVolume as $result){
// 	var_dump($result->LastBackupStatus);
// 	var_dump($result->Agent);
// }
//------ End Testing Block ------



foreach ($sxml->Device as $result){										// Iterate over each device
		//var_dump($sxml->attributes());
	$itrLastBackupStatus = "online";   									// dummy load for default state

	foreach ($result->BackupVolumes->BackupVolume as $volume){  		// Iterate over each agent on device/ set one status per device
		$itrLastBackupStatus = ($volume->LastBackupStatus == "Fail") ? "offline" : $itrLastBackupStatus; 
		// Should add exclusions? for out of service devices or hidden devices		
		//print $itrLastBackupStatus;
	}
	$sTable .= '
		<div class="' .$itrLastBackupStatus. '">
		<div class="entity " onclick="window.location.href=\'#\'">
		<h2>'.$result->Hostname.'</h2>
		<p>Last online: '.$result->Lastseen.'</p>
		<p>Last check: 34 seconds ago</p>
		</div>
		</div>';


	// ---------------------------------
	$mysplod = explode(" ", $result->Lastseen);
	$devDate = $mysplod[0];
	$devTime = $mysplod[1];

	$dateSplit = preg_split('/\s[—–-]\s/', $devDate);

	//var_dump($dateSplit); echo "</br>";	
	// ----------------------------------		
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>BACKUP MONITOR</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, minimum-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<meta http-equiv="refresh" content="15"/>
	 
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
	<link href="css/bootstrap-multiselect.min.css" rel="stylesheet">
	<link href="css/style.css" rel="stylesheet">
	 
	<script type="text/rocketscript" data-rocketsrc="js/jquery-1.7.1.min.js"></script>
	<script type="text/rocketscript" data-rocketsrc="js/bootstrap.min.js"></script>
	<script type="text/rocketscript" data-rocketsrc="js/bootstrap-multiselect.min.js"></script>
	<script type="text/rocketscript" data-rocketsrc="js/scripts.js"></script>
</head>
<body data-spy="scroll" data-target=".subnav" data-offset="50" class="black_background">
 
<div id="main-container">
	<div class="page-header"><div class="header-label"><h1>Status - <?php echo $fleetSize; ?> Device Fleet</h1></div></div>
<div id="main-content">


<div id="page-container">
	<div id="flashmessage" class="hide"></div>

	<div class="tab-content">
		<div id="flow-layout" class="tab-pane active">
			<div class="entity-container">

				<?php echo $sTable; ?>

			</div>
		</div>
	</div>
</div>


 
</body>
</html>
