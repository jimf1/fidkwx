
<?php

include_once("fidkwx.php"); //ADDS Wx Classes
include("settings/addsvars.php");

function get_tafs($fromid, $toid, $width, $hours) {
//Get TAFs based on a route with the specified from and to airports 
//in the last $hours hours, return $mostRecent
//Return an array of taf objects as defined by the xml
    global $routeBbox;
    $tafs = array();  
	$fromairport = new fidkairport($fromid);
    $toairport = new fidkairport($toid); 

    if (!(isset($toid))) $toid = $fromid; 
	if (! is_numeric($hours)) $hours = 1;

    $bbox = implode(",", $routeBbox);
	
	$addsurl = $GLOBALS["addsHTTPPrefix"]."taf?&bbox=$bbox&format=xml&metar=false&hours=$hours";
		
//echo $addsurl;
	$xmlstring = file_get_contents($addsurl);     //Get XML as string  
    $_SESSION['tafxml'] = $xmlstring;
    $xml = simplexml_load_string($xmlstring);     //convert to an object
//var_dump($xml);
	//Put resulting xml in an array of objects
	foreach ($xml->data->TAF as $taf) {
	    //echo "taf Lat: $taf->latitude";
		$tafs[] = new fidktaf((string) $taf->station_id, 
                        (string) $taf->latitude,
                        (string) $taf->longitude,
						(string) $taf->raw_text,
                        (string) $taf->issue_time,
                        (string) $taf->valid_time_from,
                        (string) $taf->valid_time_to);
	}
	return($tafs);	
}
function cmptafs($a, $b)  {
//Compare 2 tafs from array to sort by distance, then id, then valid time
    $adistance = round($a["distance"]);
    $bdistance = round($b["distance"]);
    if ($adistance != $bdistance) {
        return ($adistance-$bdistance);
    }
    else {
        //distance is equal, check ID
        $comp = strcmp($a["taf"]->id,$b["taf"]->id);
        if ($comp != 0) return($comp);
        
        //We now know two stations are the same station, so look at the valid time
        if ($a["taf"]->issue_time == $b["taf"]->issue_time) return(0);
        return ($a["taf"]->issue_time > $b["taf"]->issue_time) ? -1 : 1;
    }
}

//Mainline

//Test code
//$fromid = "KHEF";
//$toid = "KHEF";
//$width = 50;
//$hours = 10;
//$recent = false;
 

$tafs = get_tafs($fromid, $toid, $width, $hours);
$fromairport = new fidkairport($fromid);

//Put in array and include distance for sorting 
foreach ($tafs as $taf) {
    $tafarray[] = array ("taf" => $taf, "distance" =>$taf->distance_from($fromairport));
}
//Sort tafs by time in descending order
usort($tafarray, "cmptafs");

//Start the table
echo "<table id=\"taftable\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\">"; 

//Display actual tafs 
if (empty($tafarray)) echo "<tr><td>NONE FOUND</td></tr>"; 
else foreach ($tafarray as $tafelement) {
    $tafelement["taf"]->display($fromairport);
}

echo "</table>";
?>