<?php 
  set_include_path('..'); 
  ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name = "viewport" content = "initial-scale = 1.0">
<title>FIDK PDA 7-Day</title>
<link href="FIDKPDAWx.css" rel="stylesheet" type="text/css"> 
</head>

<body>

<h1 align="center" class="pageHeading">FIDK Weather </h1>
<p align="center" class="menutext">Current GMT is: <?php
    echo ' '. gmdate ("M d Y H:i:s");
  ?>
</p>
 
<?php 
  include("includes/pdaMenuBar.php"); 
  include("includes/airportInfo.php");
  ?>


<br>
<div class="regularText" id="pageContent">
<strong><a href="http://graphical.weather.gov/sectors/conusWeek.php#tabs">NWS Graphical Forecast Page</a> </strong>
<br>
<strong><a href="http://www.nws.noaa.gov/mdl/forecast/graphics/MAV/">GFS Model Graphics Page</a> </strong>
<p class="pageHeading">7 Day Forecast </p>
<div class="subHeading">12 Hour </div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/basicwx/92fndfd.gif" alt="12 Hour" width="100%"> </p>
<div class="subHeading">24 Hour</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/basicwx/94fndfd.gif" alt="24 Hour" width="100%"> </p>
<div class="subHeading">36 Hour</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/basicwx/96fndfd.gif" alt="36 Hour" width="100%"> </p>
<div class="subHeading">48 Hour</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/basicwx/98fndfd.gif" alt="48 Hour" width="100%"> </p>
<div class="subHeading">Day 3 Fronts</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/medr/9jh.gif" alt="Day 3 Fronts" width="100%"> </p>
<div class="subHeading">Day 4 Fronts</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/medr/9kh.gif" alt="Day 4 Fronts" width="100%"> </p>
<div class="subHeading">Day 5 Fronts</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/medr/9lh.gif" alt="Day 5 Fronts" width="100%"> </p>
<div class="subHeading">Day 6 Fronts</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/medr/9mh.gif" alt="Day 6 Fronts" width="100%"> </p>
<div class="subHeading">Day 7 Fronts</div><br>
<p align="center"> <IMG src="http://www.wpc.ncep.noaa.gov/medr/9nh.gif" alt="Day 7 Fronts" width="100%"> </p>

</div>

</body>
</html>
