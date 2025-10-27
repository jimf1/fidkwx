<?php 
session_start();
include "includes/cookies.php"; 
include "includes/tripform.php";
// //Debug - override cookies and forms
//   $fromid = "KEKN";
//   $toid = "KEKN";
//   $recent = 0;
//   $width = 50;

$_SESSION["fromid"] = $fromid;
$_SESSION["toid"] = $toid;
$_SESSION["width"] = $width;



 ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><!-- InstanceBegin template="/Templates/fidkwxpage2.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<!-- InstanceBeginEditable name="doctitle" -->
<title>FIDK Weather</title>
<!-- InstanceEndEditable --><link href="FIDKWx.css" rel="stylesheet" type="text/css">
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<link rel="shortcut icon" href="favicon.ico" > 
</head>

<body>

<h1 align="center" class="pageHeading">Jim's FIDK Weather </h1>
<p align="center" class="menutext">Current GMT is: <?php
		echo ' '. gmdate ("M d Y H:i:s");
	?>
</p>

 <?php include("includes/menuBar.php"); ?>


<!-- InstanceBeginEditable name="EditRegion3" -->
<p align="left" class="pageHeading">Home</p>
<div class="regularText" id="pageContent">
  <table width="100%"  border="0" cellspacing="0" cellpadding="3">
    <tr>
      <td><form action="index.php" method="post" name="tripform" id="tripform">
          <table  border="0" cellpadding="3" cellspacing="0">
            <tr>
              <td colspan="7" bgcolor="#6699FF" class="menutext"><div align="center"><strong>Plan a Trip </strong></div></td>
            </tr>
            <tr>
              <td bgcolor="#6699FF"><div align="right"><span class="menutext">From:</span></div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF"><span class="menutext">
                <input name="fromid" type="text" id="fromid" value="<?php echo $fromid ?>" size="4" maxlength="4"

>
              </span></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF"><div align="right"><span class="menutext">To:</span></div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF"><span class="menutext">
                <input name="toid" type="text" id="toid" value="<?php echo $toid ?>" size="4" maxlength="4"

>
              </span></td>
            </tr>
            <tr>
              <td colspan="5" bgcolor="#6699FF"><div align="right"><span class="menutext">Width of route for reports: </span></div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF"><span class="menutext">
                <input name="width" type="text" id="width" value="<?php echo $width ?>" size="4" maxlength="4"
>
              </span></td>
            </tr>
            <tr>
              <td colspan="5" bgcolor="#6699FF" class="menutext"><div align="right">Prior hours of weather: </div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF"><span class="menutext">
                <input name="hours" type="text" id="hours" value="<?php echo $hours ?>" size="4" maxlength="4"
>
              </span></td>
            </tr>
            <tr>
              <td colspan="7" bgcolor="#6699FF"><div align="right"><span class="menutext">Save Info for Later?</span>
                      <input type="checkbox" name="savecookie" value="yes">
              </div></td>
            </tr>
            <tr>
              <td colspan="7" bgcolor="#6699FF"><div align="right"><span class="menutext">Only most recent reports?</span>
                      <input name="mostrecent" type="checkbox" id="mostrecent" value="yes" <?php if ($recent) echo "checked" ?> >
              </div></td>
            </tr>
             <tr>
              <td colspan="7" bgcolor="#6699FF"><div align="right"><span class="menutext">Suppress distant SIGMETS?</span>
                      <input name="oobsigs" type="checkbox" id="oobsigs" value="yes" <?php if ($suppress_oobsigs) echo "checked" ?> >
              </div></td>
            </tr>
            <tr>
              <td colspan="7" bgcolor="#6699FF"><div align="center"><span class="menutext"> <br>
                        <input name="Submit" type="submit" class="button" value="Trip Search">
              </span></div></td>
            </tr>
          </table>
      </form></td>
      <td>        
          <table width="75%"  border="2" cellspacing="0" cellpadding="3">
            <tr>
              <td colspan="2" class="LIFR"><div align="center"><strong class="subHeading">Color codes used in text weather: </strong></div></td>
            </tr>
            <tr>
              <td class="LIFR"><strong>LIFR</strong></td>
              <td>Ceiling less than 500 and/or vis less than 1 mile.</td>
            </tr>
            <tr>
              <td class="IFR"><strong>IFR</strong></td>
              <td>Ceiling between 500 and 1000 and/or vis between 1 and 3 miles.</td>
            </tr>
            <tr>
              <td class="MVFR"><strong>MVFR</strong></td>
              <td>Ceiling between 1000 and 3000 and/or vis between 3 and 5 miles. </td>
            </tr>
            <tr>
              <td class="VFR"><strong>VFR</strong></td>
              <td>Ceiling greater than 3000 and vis greater than 5 miles. </td>
            </tr>
            <tr>
              <td class="UNK"><strong>UNK</strong></td>
              <td>Unable to determine flight conditions. </td>
            </tr>
            <tr>
              <td colspan="2" class="old">Red times indicate data over an hour old. </td>
            </tr>
            <tr>
              <td colspan="2" class="UNK">Gray SIGMETS and PIREPS indicate data over an hour old. </td>
            </tr>
            <tr>
              <td colspan="2" class="oobasp">Purple SIGMETS indicate data outside of the route (turn off by checking Suppress Distant SIGMETS). </td>
            </tr>
        </table>
     </td></tr>
  </table>
  <p>Welcome to the FIDK Weather site. Your briefing follows. Remember, this does not substitute for a real FSS briefing!</p>
  <table width="100%"  border="0" cellspacing="0" cellpadding="3"> 
    <tr>
        <td><a href="http://www.usairnet.com/weather/maps/current/flight-rules/" target="_blank"><img src="https://www.usairnet.com/weather/images/flight-rules.png" alt="Flight Rules" height="325" border="0"></a></td>
        <td><a href="https://www.wunderground.com/maps/radar/current" target="_blank"><img src="https://s.w-x.co/staticmaps/wu/wxtype/none/usa/animate.png" alt="RADAR" height="325" border="0"></a></td>
    <tr>
    </table>        
  <table width="100%"  border="1" cellspacing="0" cellpadding="0">
    <tr>
      <td colspan="2"><div align="center">Vectors are relative to <?php echo $fromid ?>. <br>
		<?php include("./settings/version.php"); ?></div></td>
    </tr>
    <tr>
      <td bgcolor="#99CCFF"><div align="center"><strong>METARs</strong></div></td>
      <td bgcolor="#99CCFF"><div align="center"><strong>TAFs</strong></div></td>
    </tr>
    <tr>
      <td valign="top"><?php include("includes/metars.php"); ?></td>
      <td valign="top"><?php include("includes/tafs.php"); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php include("includes/sigmets.php"); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php include("includes/pireps.php"); ?></td>
    </tr>
  </table>
</div>

<!-- InstanceEndEditable -->
<p align="left" class="pageHeading">&nbsp;</p>
</body>
<!-- InstanceEnd --></html>
