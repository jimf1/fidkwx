<?php 
  set_include_path('..'); 
  ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name = "viewport" content = "initial-scale = 1.0">
<title>FIDK PDA RADAR</title>
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
include("includes/airportInfo.php")
?>


<div class="regularText" id="pageContent">

<p> <b>RADAR</b> </p>
<p align="center"> <img src="https://s.w-x.co/staticmaps/wu/wxtype/none/usa/animate.png" alt="USA Radar" ></p>

</div>
</body>
</html>
