<?php
//var_dump($_COOKIE);
/* Non-database functions required by FIDK weather system */

//If cookie found, set vars
if (isset($_COOKIE['fromid'])) 	$fromid = $_COOKIE['fromid'];
if (isset($_COOKIE['toid'])) 	$toid = $_COOKIE['toid'];
if (isset($_COOKIE['width'])) 	$width = $_COOKIE['width'];
if (isset($_COOKIE['hours'])) 	$hours = $_COOKIE['hours'];
if (isset($_COOKIE['recent'])) 	$recent = $_COOKIE['recent'];
if (isset($_COOKIE['oobsigs'])) $suppress_oobsigs = $_COOKIE['oobsigs'];


function set_cookie($fromid, $toid, $width, $hours, $recent, $suppress_oobsigs) { // Set cookie
//echo "Set cookie - recent: ", $recent;
	setcookie("fromid", $fromid, strtotime('+3 years'),'/');
	setcookie("toid", $toid, strtotime('+3 years'),'/');
	setcookie("width", $width, strtotime('+3 years'),'/');
	setcookie("width", $width, strtotime('+3 years'),'/');
	setcookie("hours", $hours, strtotime('+3 years'),'/');
	setcookie("recent", $recent, strtotime('+3 years'),'/');
	setcookie("oobsigs", $suppress_oobsigs,strtotime('+3 years'),'/');
	return;
}
?>