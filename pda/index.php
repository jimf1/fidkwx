<?php 
  set_include_path('..'); //All includes should be relative to the parent's path
  include "includes/cookies.php";
  include "includes/tripform.php"; 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name = "viewport" content = "initial-scale = 1.0">
<title>FIDK PDA Weather</title>
<link href="FIDKPDAWx.css" rel="stylesheet" type="text/css"> 
</head>

<body>

<h1 align="center" class="pageHeading">FIDK Weather </h1>
<p align="center" class="menutext">Current GMT is: <?php
		echo ' '. gmdate ("M d Y H:i:s");
	?>
</p>
 
<?php include("includes/pdaMenuBar.php"); ?>

<table  border="0" align="center" cellpadding="3" cellspacing="0"> 
    <tr>
    <form action="https://www.airnav.com/airports/get" method="post" name="airportform" class="fidkform">
    <td nowrap bgcolor="#6699FF">      
	<input name="Airports" type="submit" class="button" id="Airports" class="menutext" value="Airport Info">      
	<input name="s" type="text" id="airport" value="City or ID" size="20" class="menutext" onFocus="this.value=''"> 
    </td>     
	</tr>     
	</form>
</table>  

<div class="regularText" id="pageContent">
  <table width="100%"  border="0" align="center" cellspacing="0" cellpadding="3">
    <tr>
      <td><form action="index.php" method="post" name="tripform" id="tripform">
          <table  align="center" border="0" cellpadding="3" cellspacing="0">
            <tr>
              <td colspan="7" bgcolor="#6699FF" class="menutext"><div align="center"><strong>Plan a Trip </strong></div></td>
            </tr>
            <tr>
              <td bgcolor="#6699FF"><div align="right"><span class="menutext">From:</span></div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF">
                <input name="fromid" type="text" id="fromid" class="menutext" value="<?php echo $fromid ?>" size="6" maxlength="4">
              </td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF"><div align="right"><span class="menutext">To:</span></div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF">
                <input name="toid" type="text" id="toid" class="menutext" value="<?php echo $toid ?>" size="6" maxlength="4">
              </td>
            </tr>
            <tr>
              <td colspan="5" bgcolor="#6699FF"><div align="right"><span class="menutext">Width of route for reports: </span></div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF">
                <input name="width" type="text" id="width" class="menutext" value="<?php echo $width ?>" size="6" maxlength="4">
             </td>
            </tr>
            <tr>
              <td colspan="5" bgcolor="#6699FF" class="menutext"><div align="right">Prior hours of weather: </div></td>
              <td bgcolor="#6699FF">&nbsp;</td>
              <td bgcolor="#6699FF">
                <input name="hours" type="text" id="hours" class="menutext" value="<?php echo $hours ?>" size="6" maxlength="4"
>
              </td>
            </tr>
            <tr>
              <td colspan="7" bgcolor="#6699FF"><div align="right"><span class="menutext">Save Info for Later?</span>
                      <input type="checkbox" class="menutext" name="savecookie" value="yes">
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
    </tr>
  </table>
  <br>
  Welcome to the FIDK Weather site. Your briefing follows. Remember, this does not substitute for a real FSS briefing!
 
  <table border="3" cellspacing="0" cellpadding="0">
  <tr>
      <td colspan="2"><div align="center">Vectors are relative to <?php echo $fromid ?>. <br> 
        <?php include("settings/version.php"); ?></td>
    </tr>
    <tr>
      <td bgcolor="#99CCFF"><div align="center"><strong><a name="METARs"></a>METARs</strong></div></td>

    </tr>
    <tr>
      <td valign="top"><?php include("includes/metars.php"); ?></td>
    </tr>
	<tr>
      <td bgcolor="#99CCFF"><div align="center"><strong><a name="TAFs"></a>TAFs</strong></div></td>
	</tr>
	<tr>
      <td valign="top"><?php include("includes/tafs.php"); ?></td>
 	</tr>
    
    <tr> 
      <td colspan="2"><a name="SIGMETs"></a><?php include("includes/sigmets.php"); ?></td>
    </tr>
    <tr>
      <td colspan="2"><a name="PIREPs"></a><?php include("includes/pireps.php"); ?></td>
    </tr>
  </table>
</div>
</html>
