
<?php
include_once("fidkwx.php"); //ADDS Wx Classes
include("settings/addsvars.php");
//include_once("settings/globalvars.php");


function get_metars($fromid, $toid, $width, $hours, $recent) {
//Get METARS based on a route with the specified from and to airports 
//in the last $hours hours, return $mostRecent
//Return an array of metar objects as defined by the xml
	global $routeBbox;

	if (!(isset($toid))) $toid = $fromid; 
	if (! is_numeric($hours)) $hours = 3;

	$fromairport = new fidkairport($fromid);
	$toairport = new fidkairport($toid);
	//Set routeBbox
	$routeBbox = routeBBox($fromairport, $toairport, $width); 
	$bbox = implode(',', $routeBbox);  //Give it to awc as a string

	$addsurl = $GLOBALS["addsHTTPPrefix"]."metar?&bbox=$bbox&format=xml&taf=false&hours=$hours";
	
    $xmlstring = file_get_contents($addsurl);     //Get XML as string  
    $_SESSION['metarxml'] = $xmlstring;
    $xml = simplexml_load_string($xmlstring);     //convert to an object
//var_dump($xml);
if (empty($xml->data)) {
	echo "No METARS, check From and To airports";
	die();
}
	//Put resulting xml in an array of objects
	foreach ($xml->data->METAR as $metar) {
	//echo "Metar Lat: $metar->latitude";
		$metars[] = new fidkmetar((string) $metar->station_id, 
						(string) $metar->latitude, 
						(string) $metar->longitude, 
						(string) $metar->raw_text, 
						(string) $metar->flight_category,
						(string) $metar->observation_time);
	}
	return($metars);	
}
function cmpmetars($a, $b)  {
//Compare 2 metars from array to sort by distance, then id, then valid time
	$adistance = round($a["distance"]);
	$bdistance = round($b["distance"]);
	if ($adistance != $bdistance) {
		return ($adistance-$bdistance);
	}
	else {
		//distance is equal, check ID
		$comp = strcmp($a["metar"]->id,$b["metar"]->id);
		if ($comp != 0) return($comp);
		
		//We now know two stations are the same station, so look at the valid time
		if ($a["metar"]->validTimeAsInt() == $b["metar"]->validTimeAsInt()) return(0);
		return ($a["metar"]->validTimeAsInt() > $b["metar"]->validTimeAsInt()) ? -1 : 1;
	}

}


//Mainline

//Test code
//$fromid = "KHEF";
//$toid = "KCHO";
//$width = 50;
//$hours = 3;
//$recent = false



$metars = get_metars($fromid, $toid, $width, $hours, $recent);
	if (! is_numeric($hours)) $hours = 3;
	if (is_null($recent)) $recent = true;
//	else $recent = $recent == "1";


//If the user only wants the most recent, weed out the one's that are old and return the result
$latest = [];
if ($recent) {
	foreach ($metars as $metar){
		if (array_key_exists($metar->id, $latest)) {
			if ($latest["$metar->id"]->validTimeAsInt() < $metar->validTimeAsInt()) {
				$latest["$metar->id"] = $metar;
			}
			} else {
				$latest[$metar->id] = $metar;
			}
		
		}
	$metars = $latest;
	}

$fromairport = new fidkairport($fromid);

//Put METARS in an array to sort by distance
foreach ($metars as $metar) {
	$metararray[] = array ("metar" => $metar, "distance" =>$metar->distance_from($fromairport));
}
//Sort metars by distance, then time
usort($metararray, "cmpmetars");
echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" id=\"metartable\">";    
if (empty($metararray)) echo "<tr><td>NONE FOUND</td></tr>"; 
else foreach ($metararray as $metarelement) {
//var_dump($metar);
$metarelement["metar"]->display($fromairport);
}
echo "</table>";
?>