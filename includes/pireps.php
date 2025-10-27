
<?php

include_once("fidkwx.php"); //ADDS Wx Classes
include("settings/addsvars.php");

function get_pireps($fromid, $toid, $width, $hours) {
//Get pirepS based on a route with the specified from and to airports 
//in the last $hours hours, return $mostRecent
//Return an array of pirep objects as defined by the xml

	global $routeBbox;
    $pireps = array();  
	if (!(isset($toid))) $toid = $fromid; 
	$fromairport = new fidkairport($fromid);
    $toairport = new fidkairport($toid);

	if (! is_numeric($hours)) $hours = 3;
	
	$bbox = implode(",", $routeBbox);
        
		$addsurl = $GLOBALS["addsHTTPPrefix"]."pirep?bbox=$bbox&format=xml&age=$hours";
		
//echo $addsurl;
	$xmlstring = file_get_contents($addsurl);     //Get XML as string  
	if ($xmlstring == "") return [];  //Nothing found return empty array
	$xml = simplexml_load_string($xmlstring); 
	
	//$xml = simplexml_load_file($addsurl); //Load up object with adds result
//var_dump($xml);
	//Put resulting xml in an array of objects
	foreach ($xml->data->AircraftReport as $pirep) {
	    //echo "pirep Lat: $pirep->latitude";
		$pireps[] = new fidkpirep((string) $pirep->observation_time, 
                        (string) $pirep->receipt_time,
                        (string) $pirep->report_type,
						(string) $pirep->raw_text);
	}
	return($pireps);	
}
function cmppireps($a, $b)  {
//Compare 2 pireps from array to sort by id, then time
        //distance is equal, check ID
        $comp = strcmp($a->id(),$b->id());
        if ($comp != 0) return($comp);
        
        //We now know two stations are the same station, so look at the valid time
       // return ($b->observation_time > $a->observation_time);
	   return ($b->observation_time - $a->observation_time);
}


//Mainline

//Test code
//$fromid = "KHEF";
//$toid = "KCEW";
//$width = 50;
//$hours = 10;
//$recent = false;



$pireps = get_pireps($fromid, $toid, $width, $hours);
$fromairport = new fidkairport($fromid);

//Sort pireps by time in descending order
usort($pireps, "cmppireps");

//Put in arrays by types.  Sort by automated vs reported manually   
foreach ($pireps as $pirep) {        
	if ($pirep->type == "PIREP") $actualpireps[] = $pirep;
    else  $autopireps[] = $pirep;   
}

//Start the table
echo "<table width=100% border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"; 

//Display actual PIREPs 
echo " <tr>
      <td bgcolor=\"#99CCFF\"><div align=\"center\"><strong>PIREPs</strong></div></td>
    </tr>";
if (empty($actualpireps)) echo "<tr><td>NONE FOUND</td></tr>"; 
else foreach ($actualpireps as $pirep) {
	$pirep->display();
}

//Display automated PIREPs
echo " <tr>
      <td bgcolor=\"#99CCFF\"><div align=\"center\"><strong>Automated PIREPs</strong></div></td>
    </tr>";
if (empty($autopireps)) echo "<tr><td>NONE FOUND</td></tr>";
else foreach ($autopireps as $pirep) {
    $pirep->display();
}

echo "</table>";
?>