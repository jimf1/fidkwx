<?php
//var_dump($_POST);
//echo "recent in tripform: ".$recent."<br>";
//Default to cookie values check form, set page variables and set cookie if needed
if (sizeof($_POST) > 0) {
    if (isset($_POST["fromid"])) $fromid = $_POST["fromid"];
    if (isset($_POST["toid"])) $toid = $_POST["toid"];
    if (isset($_POST["width"])) $width = $_POST["width"];
    if (isset($_POST["hours"])) $hours = $_POST["hours"];
    if (isset($_POST["mostrecent"])) $recent = true; 
        else $recent = false;
    if (isset($_POST["oobsigs"])) $suppress_oobsigs = true; 
        else $suppress_oobsigs = false;
}

//echo "recent: ".$recent;
//Basic edits on user inputs for trip calcs

//Global variables Defaults
if (!isset($fromid)) $fromid = "KHEF";
//if (strlen($fromid) < 3) $fromid = "KHEF";
if (!isset($toid))  $toid = "KHEF";
if (!isset($width)) $width = 50;
if (!isset($hours)) $hours = 1;
if (!isset($recent)) $recent = true;
if (!isset($suppress_oobsigs)) $suppress_oobsigs = true;
$routeBbox = [];

//See if user had leading K
//if (strlen($fromid) < 4) $fromid = "K" .$fromid;
//if (strlen($toid) < 4) $toid = "K" .$toid;

//Numeric limitations
$width = (float) $width;
$hours = (float) $hours;
if ($width > 100) $width = 100;
if ($hours > 48) $hours = 48;


//convert to uppercase 	
$fromid = strtoupper ($fromid);
$toid = strtoupper ($toid);


if (isset($_POST["savecookie"]) and $_POST["savecookie"] == "yes") set_cookie($fromid, $toid, $width, $hours, $recent, $suppress_oobsigs) ;

?>