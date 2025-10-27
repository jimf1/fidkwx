<?php 
include("settings/addsvars.php");

//Class definition for fidkairport.  Requires database

function routeBBox ($airport1, $airport2, $width) {
//Calclulate a bbox between $airport1 and $airport2 with a width of $width
// Parms
//   $airport1 and $airport2 are of class AWCairport
//   $width is width of corridor in nautical miles
//Returns
// A bbox in the form NWlat, NWlon, SElat, SElon

//First calc boxes at endpoints of trip
$airport1Box = $airport1->calcRectangle($width);
$airport2Box = $airport2->calcRectangle($width);

//A bbox is 2 lat, lon values for the corners, so let's calc SE and NW corners!
$answer = [];
$answer["SELat"] = min($airport1Box["lolat"], $airport2Box["lolat"]);
$answer["SELon"] = min($airport1Box["lolon"], $airport2Box["lolon"]);
$answer["NWLat"] = max($airport1Box["hilat"], $airport2Box["hilat"]);
$answer["NWLon"] = max($airport1Box["hilon"], $airport2Box["hilon"]);

return $answer;

//return (implode(',', $answer));
}

class fidkairport  {
//Store additional info about METARS, TAFS, etc.

    var $id;
    var $latitude;
    var $longitude;
    var $name;
    
   	function __construct($newid) {
//		   		if (strlen($newid) < 4)
//			     		$newid = "K".$newid;     //usfif database stores the leading K
			     	$this->id = $newid;
			     	$this->getData();
    	}
    
	function dbconnect ($hostname, $user, $password, $schema) {
	/* Try to connect to database, returns:
	   TRUE if database connection was successful
	   FALSE if database connection was NOT successful */
	/* Get mysql instance */
		$link = mysqli_connect($hostname, $user, $password);
		if (! $link) {
		    print "Could not connect to database : " . mysqli_connect_error() . "Continuing ...";
		    return $link;
		}
		elseif (mysqli_select_db($link, $schema)) {
			return $link;
		}
		else {
			print "Could not select database : " . mysqli_error($link) . "Continuing ...";
		    	return $link;
		}
	}
	function getData() {
		// Get name and lat/lon from database.  If that fails, use the API.
		$this->getDataFromDB();
		if ($this->name == "")
			$this->getDataFromAWC();
		if ($this->name == "") { //Couldn't find anything, use defaults
			$this->name = "Unknown";
			$this->latitude = 999;
			$this->longitude = 999;
		}
		
	}
	function getDataFromDB() {
	
	    if (!is_null($this->latitude) && !is_null($this->longitude)) { //Already set
		    return;
		    }

	    // Lookup airport (airport) and return latitude and longitude in decimal format
	    require('settings/dbvars.php');
	       
	    $airport = $this->id;
	  
	   //Connecting, selecting database 
	   //If not found, database is not required but function will return empty
	   $link = $this->dbconnect($hostname, $user, $password, $schema);
	   if (! $link ) return $answer;
	   // Get lat/long of airport requested (in decimal format) and name as optimization
	   $query = "SELECT NAME, WGS_DLAT, WGS_DLONG FROM ARPT where ICAO = '$airport' OR FAA_HOST_ID = '$airport'";
	   $result = mysqli_query($link, $query) or die("Airport Lat/Long Query failed : " . mysqli_error($link));

	   // Store lat/long of airport requested (in decimal format), also name as an optimization
	   $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	   if (!empty($row)) {
		$this->latitude = $row["WGS_DLAT"];
		$this->longitude = $row["WGS_DLONG"];
		$this->name = $row["NAME"];
	   }
	   // Free resultset
	   mysqli_free_result($result);

	}

	function getDataFromAWC() {
        //Go to AWC with id and get airport data
        $addsurl = $GLOBALS["addsHTTPPrefix"]."airport?ids=$this->id&format=json";


        $JSONstring = file_get_contents($addsurl);     //Get JSON results
		$jimTest = $http_response_header;
		$response_code = substr($http_response_header[0], 9, 3);
		if (empty($JSONstring)){ //No results
			return;
		}
        $airport = json_decode($JSONstring)[0];

        $this->latitude = $airport->lat;
        $this->longitude = $airport->lon;
        $this->name = $airport->name;

       // var_dump($this);
    }

	
	function calcRectangle($radius) {
	
	     // Answer 4 lat/long points that describe a rectangle based on $radius
		//Initialize
		$longhome = $this->longitude;
		$lathome = $this->latitude;
 
		//Figure the radius of the request -- OK, its a square 
		// 1NM = 1/60 degree of Latitude at equator, so fudge via the real Latitude 
		$fudge = 1 / cos(deg2rad($lathome));
		$lolong = $longhome - (($radius / 60) * $fudge);
		$hilong = $longhome + (($radius / 60) * $fudge);

		// 1NM of latitude = 1/60 degree of Longitude 
		$lolat = $lathome - ($radius / 60);
		$hilat = $lathome + ($radius / 60);
	   
	   	$answer = array ("lolat" => $lolat, 
						"lolon" => $lolong, 
						"hilat" => $hilat, 
						"hilon" => $hilong);
		return $answer;
}
	
	function get_Name() {
		return $this->name;
		if (!is_null($this->name)) { //Already set
			return($this->name);
			}
	
	}

	function routeBBox ($airport, $width) {
		//Calclulate a bbox to another airport with a width of $width
		// Parms
		//   $airport is class fidkairport
		//   $width is width of corridor in nautical miles
		//Returns
		// A bbox in the form NWlat, NWlon, SElat, SElon

		//First calc boxes at endpoints of trip
		$airport1Box = $this->calcRectangle($width);
		$airport2Box = $airport->calcRectangle($width);

		//A bbox is 2 lat, lon values for the corners, so let's calc SE and NW corners!
		$answer = [];
		$answer["SELat"] = min($airport1Box["lolat"], $airport2Box["lolat"]);
		$answer["SELon"] = min($airport1Box["lolon"], $airport2Box["lolon"]);
		$answer["NWLat"] = max($airport1Box["hilat"], $airport2Box["hilat"]);
		$answer["NWLon"] = max($airport1Box["hilon"], $airport2Box["hilon"]);

		return (implode(',', $answer));

	}
	
	function display() {
	    //Display yourself
	    echo "ID: ".$this->id."<br>";
	    echo "Lat: ".$this->latitude."<br>";
	    echo "long: ".$this->longitude."<br>";
	    echo "Name: ".$this->name; "<br>";
	}

}

//Test code
//$airport = new fidkairport("KHEF");
//$airport->display();
  

?>
