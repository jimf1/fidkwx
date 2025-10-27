<?php

//Classes for storing and displaying weather reports (METAR and TAF)
//fidkwx is parent class and contains logic about stations (distance, bearing, etc.)

include_once "fidkairport.php";  //Need this for calculations from another airport
include_once "decodetaf.php"; //Need this to decode TAF a la PHPWeather
include_once "includes/util.php";

class fidkwx  {
//Store generic information about weather reports
//Calculate distance and bearing from given airport (fidkairport)

    var $id;
    var $raw_text;
    var $latitude;
    var $longitude;                                     
    var $distance = 0;
    var $radial = 0;
    var $station;

	function __construct($id, $latitude, $longitude, $rawtext) {
	//Create object and set variables
	//Public function

		$this->id = $id;
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->raw_text = $rawtext;
		$this->station = new fidkairport($id);
	}
     function idas3() {
     //Return the ID as a 3 character identifier
     if (strlen($this->id)==4) return (substr($this->id,1,3));
     return($this->id);
     }

	function distance_from($fidkairport) {

	//Initialize distance from $fidkairport in each result of the weather request
		if ($this->id == $fidkairport->id) { //It's the same dern place
			$this->distance = 0;
			return($this->distance);
			}
		if ($this->distance == 0) { //Need to find distance
			$this->great_circle($fidkairport);
			}
		return($this->distance);

		}
		
	function get_Name() {
		return($this->station->get_Name());
        //return;
    	}
    	
	function bearing_from($fidkairport) {
	
		//Initialize radial from $fidkairport in each result of the weather request
			if ($this->id == $fidkairport->id) { //It's the same dern place
				$this->radial = 0;
				return($this->radial);
				}
			if ($this->radial == 0) { //Need to find distance
				$this->great_circle($fidkairport);
				}
			return($this->radial);
	
		}
	function card_heading($fidkairport) {
		//Return cardinal heading from fidkairport to here
		$this->bearing_from($fidkairport); //Initialize vector
		if ($this->id == $fidkairport->id)
		    return("");
		    
		elseif ($this->radial > 337.51 || $this->radial <= 22.50) $card = "N";
		elseif ($this->radial > 22.50 & $this->radial <= 67.50)   $card = "NE";
		elseif ($this->radial > 67.50 & $this->radial <= 112.50)  $card = "E";
		elseif ($this->radial > 112.50 & $this->radial <= 157.50) $card = "SE";
		elseif ($this->radial > 157.50 & $this->radial <= 202.50) $card = "S";
		elseif ($this->radial > 202.50 & $this->radial <= 247.50) $card = "SW";
		elseif ($this->radial > 247.50 & $this->radial <= 292.50) $card = "W";
		elseif ($this->radial > 292.50 & $this->radial <= 337.50) $card = "NW";
		
		if ($this->distance_from($fidkairport) == 0) return "";
		return(round($this->distance_from($fidkairport)).$card);
	}

	function great_circle ($fidkairport) {
		/*
		Calculate great circle distance and bearing between the from and to points
		All lat/long are assumed to be in degrees and in decimal, (not minutes)

		Sets bearing and distance in the current object
		*/

		/* Don't know why, but DAFIF gives some longititudes in negative values,  presumably
		   this is to simplify calcs, but it doesn't seem to work well.  So use the old method and convert
		   all parms to positive values as the formulas expect */
           $latfrom =  $fidkairport->latitude;
           $longfrom = $fidkairport->longitude;
           $latto =    $this->latitude;
           $longto =   $this->longitude;

		if (!empty($latfrom))   $latfrom = abs($latfrom);
		if (!empty($longfrom))  $longfrom = abs($longfrom);
		if (!empty($latto))     $latto = abs($latto);
		if (!empty($longto))    $longto = abs($longto);
		
		$radearth = 3443.75; // radius of Earth in NM.  (In case it changes)

		/* Convert degrees to radians since all PHP computations need radians */
		if (!empty($latto)) $latto = deg2rad($latto);
		if (!empty($latfrom))$latfrom = deg2rad($latfrom);
		if (!empty($longto))$longto = deg2rad($longto);
		if (!empty($longfrom))$longfrom = deg2rad($longfrom);

		/* Calculate absolute angle between from and to */
		$cosangle = cos($latto) * cos($latfrom) * cos($longto - $longfrom) + sin($latto) * sin($latfrom);
		$angle = acos($cosangle);  //Angle between from and to in radians (without regard to lat/long)

		$distance = $radearth * $angle;

		/* This was ARRL method.  Used the one that follows because it eliminates some rounding */
		// $d = deg2rad($distance/60); //need distance radians of change for calc below
		// $heading = rad2deg(acos((sin($latto) - (sin($latfrom) * cos($d))) / (cos($latfrom) * sin($d))));

		/* Calc heading */
		$heading = rad2deg(acos((sin($latto) - (sin($latfrom) * $cosangle)) / (cos($latfrom) * sin($angle))));
		if (sin($longfrom - $longto) < 0) $heading = 360 - $heading;

		//$answer = array('BEARING' => $heading,'DISTANCE' => $distance);
		 // return $answer;
		$this->distance = $distance;
		$this->radial = $heading;
		}
}

class fidkmetar extends fidkwx {
//Store additional info about METARS

	var $flight_category;
	var $observation_time; 
    
    function __construct($id, $latitude, $longitude, $rawtext, $flight_category, $observation_time) {

    	parent::__construct($id, $latitude, $longitude, $rawtext); //Call parent constructor
    	
    	if (strlen($flight_category) > 2)
    		$this->flight_category = $flight_category;
    	else
    		$this->flight_category = "UNK";
    		
    	$this->observation_time = $observation_time;
    }
        
    function validTimeAsInt() {
    //Return valid time as an integer
    	return(strtotime($this->observation_time));
    }
    
    function isOld() {
    //Calculate the age of the observation.  Return true if over 1hr old
    	$age = time() - strtotime($this->observation_time);
    	return($age > 3600);
    }
    
    function validTime() {
    //Get valid time from the raw data
    	return(substr($this->raw_text,11,7));
    }
    
    function observation() {
    //Get observation separate from the valid time for display purposes
    	return(substr($this->raw_text,18));
    }
    function display($fidkairport) {
    //display METARs in a pleasing way
    //Assumed to be within a table

	echo "<tr>";
	echo "<td valign=\"top\">";
	echo "<span class=\"card\">";
	if (isset($fidkairport)) {
		echo $this->card_heading($fidkairport);
	}
	echo "</span>";
	echo "</td>";
	echo "<td valign=\"top\">";
    echo "<strong> <span class=\"$this->flight_category\">";   
	echo "<a href=\"http://www.airnav.com/airport/".$this->idas3()."\"";
	echo " title=\"".$this->get_Name()."\" target=\"_blank\">";
	
	echo $this->id;
	echo "</a> </span> </strong> ";
	echo "</td>";
	echo "<td valign=\"top\">";
	echo "<span class=\"data\">";
	if ($this->isOld()) echo "<span class=\"old\">";
	echo $this->validTime();
	if ($this->isOld()) echo "</span>";
	echo $this->observation(), '<br>';
	echo "</span>";
	echo "</td>";
	echo "</tr>";
	echo "\n";
	}
}
class fidktaf extends fidkwx {
//Store additional info about METARS

    var $flight_category;
    var $issue_time;
    var $valid_time_from;
    var $valid_time_to;
    var $forecast;          //array of forecasts
     
    
    function __construct($id, $latitude, $longitude, $rawtext, $issue_time, $valid_time_from, $valid_time_to) {

        parent::__construct($id, $latitude, $longitude, $rawtext); //Call parent constructor
            
        $this->issue_time = strtotime($issue_time);
        $this->valid_time_from = strtotime($valid_time_from);
        $this->valid_time_to = strtotime($valid_time_to);
    }
    
    function isOld() {
    //Calculate the age of the observation.  Return true if over 1hr old
        return(time() > $this->valid_time_to);
    }
    
    function validTime() {
    //Get valid time from the raw data
        return(substr($this->raw_text,13,6));
    }
    
    function observation() {
    //Get observation separate from the valid time for display purposes
        return(substr($this->raw_text,12));
    }
    function display($fidkairport) {
    //display TAFs in a pleasing way
    //Assumed to be within a table

    echo "<tr>";
    echo "<td valign=\"top\">";
    echo "<span class=\"card\">";
    if (isset($fidkairport)) {
        echo $this->card_heading($fidkairport);
    }
    echo "</span>";
    echo "</td>";
    echo "<td valign=\"top\">";
    echo "<span class=\"TAFLEAD\">";
    echo "<a href=\"http://www.airnav.com/airport/".$this->idas3()."\"";
    echo " title=\"".$this->get_Name()."\" target=\"_blank\">";
    echo $this->id." ";
    echo "</a>";
    echo "</span>";
   
    $cssstyle = "TAF";
    if ($this->isOld()) $cssstyle = "old";
    echo "<span class=\"$cssstyle\">"; 
    date_default_timezone_set('UTC');
    echo date("dHi",$this->issue_time)."Z"."<br>"; 
    echo "</span>";  
    echo "</td>"; 
    echo "<span class=\"TAF\">";
    echo "<tr>";
    echo "<td></td><td>";
    $sometaf = new fidkweather($this->raw_text);
    $sometaf->decode_taf();
    $sometaf->display_taf();
 //   echo $this->raw_text;
    echo "</span>";  
    echo "</td>";
    echo "</tr>";
    echo "</tr>";
    echo "\n";
    }

     function format() { //Format color-coded TAF
        $sometaf = new fidkweather($this->raw_text);
        $sometaf->decode_taf();
        return($sometaf->format_taf());
     }
}	
class fidksigmet extends fidkwx {
//Store additional info about sigmets
	var $validTimeFrom;
	var $validTimeTo;
    var $hazard;
    var $type;
    var $points;
    var $oob;
    
    function __construct($type, $hazard, $validTimeFrom, $validTimeTo, $rawtext, $points) {
    	//parent::__construct("sigmet",0,0,$rawtext); //Call parent constructor
        $this->type = $type;
        $this->hazard = $hazard;
    	$this->validTimeFrom = strtotime($validTimeFrom);
        $this->validTimeTo = strtotime($validTimeTo); 	
        $this->raw_text = $rawtext;	
        $this->points = $points;
    }
    
    function isOld() {
    //Calculate the age of the airsigment.  Return true if valid time has passed
    	$age = time() - strtotime($this->validTimeTo);
    	return($this->validTimeTo < time());
    }

    function isOutOfBounds() {
        global $routeBbox;
        //Calculate whether the sigmet intersects the route of flight
        //Also, lazy assign this value to oob
        if ((count($this->sigmet_bbox()) == 4) && (count($routeBbox) == 4)) {
            $this->oob = !intersects($this->sigmet_bbox(),$routeBbox);
            return $this->oob;              
        } else {
            $this->oob = false;
            return $this->oob;
        }
        
    }
    
    function display() {
    //display yourself in a pleasing way
    //Assumed to be within a table
	echo "<tr>";
	echo "<td>";
    if ($this->isOld()) echo "<span class=\"oldasp\">";
    else if ($this->isOutOfBounds()) echo "<span class=\"oobasp\">";
    else echo "<span class=$this->type>";   
    echo nl2br($this->raw_text)."<br>"; 
	echo "</span>";
	echo "</td>";
	echo "</tr>";
	echo "\n";
}
	
    function sigmet_bbox() {
        //Calculate a bbox from the given polygon in $points
        //This is used to crudely compare hazard area to briefing area
        $answer =[];
        $maxLat = -999;
        $maxLon = -999;
        $minLat = 999;
        $minLon = 999;
        foreach ($this->points as $point) {
            $maxLat = max($point["latitude"], $maxLat);
            $maxLon = max($point["longitude"], $maxLon);
            $minLat = min($point["latitude"], $minLat);
            $minLon = min($point["longitude"], $minLon);

        }
        $answer["minLat"] = $minLat;
        $answer["minLon"] = $minLon;
        $answer["maxLat"] = $maxLat;
        $answer["maxLon"] = $maxLon;

        return $answer;
    }
}
    
class fidkpirep extends fidkwx {
//Store additional info about sigmets
    var $observation_time;
    var $receipt_time;
    var $type;
    
    function __construct($obsTime, $receiptTime, $type, $rawtext) {
       // parent::__construct("pirep",0,0,$rawtext); //Call parent constructor
        $this->raw_text = $rawtext;
        $this->observation_time = strtotime($obsTime);
        $this->receipt_time = strtotime($receiptTime);
        $this->type = $type;
    }
    
    function isOld() {
    //Calculate the age of the airsigment.  Return true if valid time has passed
        $age = time() - $this->observation_time;
        $oldage = 60 * 60 * 3; //3 Hours
        return($age > $oldage);
    }
    
    function isUrgent() {
        //Is this an urgent PIREP?
        $pirepTokens = explode(' ',$this->raw_text);
        $pirepType = $pirepTokens[1];
        return($pirepType == "UUA");
    }
    
    function id() {
        //Is this an urgent PIREP?
        $pirepTokens = explode(' ',$this->raw_text);
        return($pirepTokens[0]);
    }
    
    function display() {
    //display yourself in a pleasing way 
    //Assumed to be within a table
    echo "<tr>";
    echo "<td>";
    $cssclass = "PIREP";
    if ($this->isUrgent()) $cssclass = "UPIREP"; 
    if ($this->isOld()) $cssclass = "oldasp";
    echo "<span class=$cssclass>";   
    echo nl2br($this->raw_text)."<br>"; 
    echo "</span>";
    echo "</td>";
    echo "</tr>";
    echo "\n";
}
	
}
?>