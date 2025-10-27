<?php 
  set_include_path('..'); 
  ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name = "viewport" content = "initial-scale = 1.0">
<title>Useful Links</title>
<link href="FIDKPDAWx.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="favicon.ico" > 
</head>

<body>

<h1 align="center" class="pageHeading">FIDK Weather </h1>
<p align="center" class="menutext">Current GMT is: <?php
		echo ' '. gmdate ("M d Y H:i:s");
	?>
</p>
 
 <?php include("includes/pdaMenuBar.php"); ?>


<p align="left" class="pageHeading">Useful Links </p>
<div class="regularText" id="pageContent">
  <p class="subHeading">Graphical Weather Products </p>
  <p><a href="https://aviationweather.gov/gfa/#gairmet" target="_blank">Graphical AIRMETs</a><br>
   <a href="https://aviationweather.gov/gfa/#sigmet" target="_blank">Graphical SIGMETs</a><br>
   <a href="https://aviationweather.gov/gfa/#afd" target="_blank">Forecast Discussions</a><br>
   <a href="https://tfr.faa.gov/tfr3/?page=map" target="_blank">TFR Map</a><br>
   <a href="https://notams.aim.faa.gov/notamSearch/nsapp.html#/" target="_blank">NOTAM Search</a><br>
   <a href="https://aviationweather.gov/gfa/#progchart" target="_blank">Prog Chart</a><br>
   <a href="http://www.1800wxbrief.com/">AFSS</a><br>
   </p>
  
 
<p class="subHeading">Airports and Fuel</p>
  <a href="http://www.airnav.com/airports/" target="_blank">Airnav Airport Info</a><br>
  <a href="http://www.airnav.com/fuel/local.html" target="_blank">Airnav Fuel Prices</a></p>
</div>
