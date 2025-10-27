<?php

include_once("fidkwx.php"); //ADDS Wx Classes
include_once("settings/addsvars.php");


function get_sigmets($fromid, $toid, $hours) {
//Get sigmets for the continental US
//Return an array of sigmet objects as defined by the xml

    $sigmets = array();  
	$fromairport = new fidkairport($fromid);
    $toairport = new fidkairport($toid);

	if (! is_numeric($hours)) $hours = 3;
	
	$addsurl = $GLOBALS["addsHTTPPrefix"]."airsigmet?format=xml";
		
//echo $addsurl;
	$xml = simplexml_load_file($addsurl); //Load up object with adds result
//var_dump($xml);
	//Put resulting xml in an array of objects
	foreach ($xml->data->AIRSIGMET as $sigmet) {
	    //echo "sigmet Lat: $sigmet->latitude";
        //Put polygon points in an array because the SimpleXmlElement arrays aren't really arrays
       $pointArray = array();
       foreach($sigmet->area->point as $point) $pointArray[] = (array) $point;
		$sigmets[] = new fidksigmet((string) $sigmet->airsigmet_type, 
                        (string) $sigmet->hazard["type"],
						(string) $sigmet->valid_time_from, 
						(string) $sigmet->valid_time_to, 
						(string) $sigmet->raw_text,
                        $pointArray);
	}
	return($sigmets);	
}
 function cmpsigmets($a, $b)  {
//Compare 2 sigmets from array to sort by most recent valid to time first

    $timescore = $b->validTimeTo - $a->validTimeTo;
    
    //Sigmets that are out of bounds (not along route) are displayed last
    $ooba = $a->isOutOfBounds();
    $oobb = $b->isOutOfBounds();
    
    if ($ooba == $oobb) return $timescore; //use time to determine
    if ($ooba) return (1);                 //b is higher (goes lower in the list)
    return -1;                             //a is higher

}


//Mainline

 
$sigmets = get_sigmets($fromid, $toid, $hours);
$fromairport = new fidkairport($fromid);

if ($suppress_oobsigs) {
    $newsigmets = [];
    foreach ($sigmets as $sigmet) {
        if ($sigmet->isOutOfBounds()) continue;
        $newsigmets[] = $sigmet;
    }
    $sigmets = $newsigmets;
}

foreach ($sigmets as $sigmet) {        
	if ($sigmet->type == "OUTLOOK") $outlooks[] = $sigmet;
    else if ($sigmet->type == "SIGMET") $sigmets[] = $sigmet;
    else echo "************* New airsigment type found: *********** $sigmet->type";   
}

//Sort sigmets by time in descending order
usort($sigmets, "cmpsigmets");

//Put in arrays by types.  Can be "outlook" or "sigmet"   


//Start the table
echo "<table width=100% border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"; 

//Display outlooks 
echo " <tr>
      <td bgcolor=\"#99CCFF\"><div align=\"center\"><strong>Significant Weather Outlook</strong></div></td>
    </tr>";
if (empty($outlooks)) echo "<tr><td>NONE FOUND</td></tr>"; 
else foreach ($outlooks as $outlook) {
	$outlook->display();
}

//Display sigmets 
echo " <tr>
      <td bgcolor=\"#99CCFF\"><div align=\"center\"><strong>SIGMETs</strong> 
      </div
      ></td>
    </tr>";
if (empty($sigmets)) echo "<tr><td>NONE FOUND</td></tr>";
else foreach ($sigmets as $sigmet) {
    $sigmet->display();
}
echo "</table>";
 
?>