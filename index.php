<?php
ini_set('display_errors', 'On');
ini_set('html_errors', 0);

require_once('../config.php');												// Return variables $apiKeyDatto and $apiKeySTC
require_once('modules/datto.inc');											// Return variables $apiKeyDatto and $apiKeySTC
require_once('modules/stc.inc');											// Return variables $apiKeyDatto and $apiKeySTC

$sTable = "";
$sTableB = "";
$fleetDatto = 0;
$fleetSTC = 0;

	

// ----- DATTO ------ LOAD DATA
if ($apiKeyDatto){
	if (!extension_loaded('simplexml')) {   							
		echo 'SimpleXML is NOT loaded! Please install SimpleXML - Apache will require php5-cli</br> #apt-get install php5-cli'; exit;
	} else {
		$xmlNode = "https://partners.dattobackup.com/xml2.php?type=status&apiKey={$apiKeyDatto}";
		$sxml = simplexml_load_file($xmlNode);
	}

	//$fleetDatto = $sxml->attributes();									// Total appliances



	// ------ BUILD DATTO TABLE ------
	foreach ($sxml->Device as $result){										// Iterate over each device			
		//$itrLastBackupStatus = "online";   								// dummy load for default state

		foreach ($result->BackupVolumes->BackupVolume as $volume){  		// Iterate over each agent on device/ set one status per device
			$itrLastBackupStatus = ($volume->LastBackupStatus == "Fail") ? "offline" : "online";

			foreach ($listIgnoreDatto as $ignore) {								// Ignore list for Datto
				$itrLastBackupStatus = ($result->Hostname == $ignore && $volume->LastBackupStatus == "Fail") ? "online" : $itrLastBackupStatus;}

			$sTable .= '
				<div class="' .$itrLastBackupStatus. '">
				<div class="entity " onclick="window.location.href=\'#\'">
				<h2>'.$result->Hostname.'</h2>
				<h2>'.$result->Agent.'</h2>
				<p>Last online: '.$result->Lastseen.'</p>
				<p>Last check: 34 seconds ago</p>
				</div>
				</div>';

				$fleetDatto++;

				$mysplod = explode(" ", $result->Lastseen);
				$devDate = $mysplod[0];
				$devTime = $mysplod[1];
				$dateSplit = preg_split('/\s[—–-]\s/', $devDate);
		}
	}
	// ------ END BUILD DATTO TABLE ------



}
// ------ END DATTO -----



// ------ STORAGECRAFT ------ LOAD DATA
if ($apiKeySTC){
	if (!$hostSTC || !$portSTC) {
		echo 'ShadowControl PORT or HOST IP not found.  Please verify configuration file. CONFIG.PHP'; exit;
	} else {
			$ch = curl_init();
			// set URL and other appropriate options
			curl_setopt($ch, CURLOPT_URL, "https://{$hostSTC}/api/reports/status/");
			curl_setopt($ch, CURLOPT_PORT , $portSTC);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("CMD_TOKEN: {$apiKeySTC}"));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

			$result = curl_exec($ch);  curl_close($ch);
			$dataSTC = json_decode($result,true);

			$countSTC=count($dataSTC);
	}

	// ------ BUILD STC TABLE ------
	//$itrLastBackupStatus = "online";   									// dummy load for default state
	foreach ($dataSTC as $result){
		$fleetSTC++;
		$itrLastBackupStatus = ($result["status"] != "ok") ? "offline" : "online"; 

		foreach ($listIgnoreSTC as $ignore) {								// Ignore list for ShadowControl
			$itrLastBackupStatus = ($result["name"] == $ignore && $result["status"] != "ok") ? "online" : $itrLastBackupStatus;
		}

		$sTableB .= '
			<div class="' .$itrLastBackupStatus. '">
			<div class="entity " onclick="window.location.href=\'#\'">
			<h2>'.$result["name"].'</h2>
			<p>Last online: '.$result["machine_details"]["last_boot"].'</p>
			<p>Last check: 34 seconds ago</p>
			</div>
			</div>';
	 } 
	// ------ END BUILD STC TABLE ------



}
// ------ END STORAGECRAFT ------










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
	<div class="page-header"><div class="header-label"><h1>Backup Server Status -- Total servers: <?php echo $fleetDatto+$fleetSTC; ?></h1></div></div>
<div id="main-content">


<div id="page-container">
	<div id="flashmessage" class="hide"></div>

	<div class="tab-content">
		<div id="flow-layout" class="tab-pane active">
			<div class="entity-container">

				<div class="page-header"><div class="header-label"><h1>Status - Datto Fleet: <?php echo $fleetDatto; ?> </h1></div></div>
				<?php echo $sTable; ?>

				<div class="page-header"><div class="header-label"><h1>Status - StorageCraft Fleet: <?php echo $fleetSTC; ?> </h1></div></div>
				<?php echo $sTableB; ?>

			</div>
		</div>
	</div>
</div>


 
</body>
</html>
