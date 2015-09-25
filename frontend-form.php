<?php
$vip = "";
foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) 
{
	 if (array_key_exists($key, $_SERVER) === true) {
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				 if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						$vip =  $ip;
				 }
			}
	 }
}

$json = file_get_contents('http://getcitydetails.geobytes.com/GetCityDetails?fqcn='. $vip); 
$data = json_decode($json);
$mapflag = 0;
$fileuri = WP_PLUGIN_DIR.'/estros-visitorsmap/ips/ips.xml'; 
//if($data->geobyteslongitude == "")
//{
$xml = simplexml_load_file($fileuri);	

if($data->geobytesfqcn == "")
	$data->geobytesfqcn = "Unknown City";

foreach ($xml->children() as $child)
{
	if($child->city == $data->geobytesfqcn)
	{
		//$child->nodeValue++;
		$mapflag = 1;
		$child->visits = (int)($child->visits) + 1;
	}
	//echo "<div style='text-align: center;'> child: ".$child->city."</div>";
}
if(!$mapflag)
{
	$gip = $xml->addChild('ip');
	if($data->geobytesfqcn != "")
	{
		$gip->addChild('value',$data->geobytesremoteip);
		$gip->addChild('longitude',$data->geobyteslongitude);
		$gip->addChild('latitude',$data->geobyteslatitude);
		$gip->addChild('city',$data->geobytesfqcn);
		$gip->addChild('country',$data->geobytescountry);
		$gip->addChild('population',$data->geobytespopulation);
		$gip->addChild('visits',1);
	}
}
$xml->asXML($fileuri) or die('Could not save XML file!');
?>