<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><!-- InstanceBegin template="/Templates/fidkwxpage2.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<!-- InstanceBeginEditable name="doctitle" -->
<title>FIDK Weather RADAR</title>
<!-- InstanceEndEditable --><link href="FIDKWx.css" rel="stylesheet" type="text/css">
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<link rel="shortcut icon" href="favicon.ico" > 
</head>

<body>

<h1 align="center" class="pageHeading">FIDK Weather </h1>
<p align="center" class="menutext">Current GMT is: <?php
		echo ' '. gmdate ("M d Y H:i:s");
	?>
</p>
 
 <?php include("includes/menuBar.php"); ?>

<!-- InstanceBeginEditable name="EditRegion3" -->
<p align="left" class="pageHeading">RADAR	</p>
<div class="regularText" id="pageContent">
  <p>Here are some RADAR images from various sites. </p>
<p align="center">
    <img src="https://s.w-x.co/staticmaps/wu/wxtype/none/usa/animate.png" alt="USA RADAR Loop"   id="RADAR">                  
</p>
</div>
<a href="https://radar.weather.gov"> NWS RADAR</a> 
<!-- InstanceEndEditable -->
<p align="left" class="pageHeading">&nbsp;</p>
</body>
<!-- InstanceEnd --></html>
