<?php 
// ini_set('display_errors', 'On');
// ini_set('html_errors', 0);

$ch = curl_init();

// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, "https:///api/reports/status/");
curl_setopt($ch, CURLOPT_PORT , 8443);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('CMD_TOKEN: '));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

$result = curl_exec($ch);  curl_close($ch);
$result = json_decode($result,true);

$count=count($result);
echo $Str='<h1>Total : '. $count .'</h1>';

foreach ($result as $value){
	echo $value["status"] . "</br>";
	echo $value["name"] . "</br>";		
	echo $value["machine_details"]["last_boot"] . "</br>";
		
 } 
	echo "</html>";
?>

