<!--
########################################################################
##
# Script : [+]~ 1337 Multiple CMS Scaner Online v0.4 (0!IIIV) ~[+]
##
# Author : KedAns-Dz ( ked-h [ at ] hotmail [ dot ] com )
##
# Home : www.1337day.com
##
# Greets to : Dz Offenders Cr3W - Algerian Cyber Army - Inj3ct0r Team
##
#########################################################################

// Script Functions , start ..!
-->
<html>
<head>
<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>1337 Multiple CMS Scaner Online v0.4 (0!IIIV) by KedAns-Dz</title>
<meta content="KedAns-Dz , Inj3ct0r Team , 1337 Multiple CMS Scaner Online" name="description">
<link href="http://209.217.227.77/~forumant/favicon.ico" type="image/x-icon" rel="shortcut icon" />
<style>
body,input,table,select{background: black; font-family:Verdana,tahoma; color: #008000; font-size:11px; }
a:link,a:active,a:visited{text-decoration: none;color: red;}
a:hover {text-decoration: underline; color: red;}
table,td,tr,#gg{ border-style:solid; text-decoration:bold; }
tr:hover,td:hover{background-color: #FFFFCC; color:green;}
.oo:hover{background-color: black; color:white;}
</style>
</head>

<body>

<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center"><font size="4">1337 Multiple CMS Scaner Online v0.4 (0!IIIV) | T0olKit By : KedAns-Dz</font></p><br>
<form method="POST">
<p align="center"><input type="text" name="site" size="65" value="127.0.0.1"><input type="submit" value="Scan.."></p>
</form><center>
<?php
@set_time_limit(0);
@error_reporting(0);

function check_exploit($cpmxx)
{
	
	$link = "http://packetstormsecurity.org/search/files/?q=$cpmxx";
	
	$result = @file_get_contents($link);
	
	if (eregi("No Results Found", $result)) {
		
		echo "<td>Not Found</td><td><a href='http://www.google.dz/#hl=en&q=download+$cpmxx'>Download</a></td></tr>";
		
	} else {
		
		echo "<td><a href='$link'>Found</a></td><td><=</td></tr>";
		
	}
}

/* Joomla Conf */
function check_com($url)
{
	
	$source = @file_get_contents($url);
	
	preg_match_all('{option,(.*?)/}i', $source, $f);
	preg_match_all('{option=(.*?)(&amp;|&|")}i', $source, $f2);
	preg_match_all('{/components/(.*?)/}i', $source, $f3);
	
	$arz = array_merge($f2[1], $f[1], $f3[1]);
	
	$coms = array();
	
	if (count($arz) == 0) {
		echo "<tr><td colspan=3>[ Joomla ] ...Nothing Found !</td></tr>";
	}
	
	foreach (array_unique($arz) as $x) {
		$coms[] = $x;
	}
	
	foreach ($coms as $comm) {
		
		echo "<tr><td>$comm</td>";
		check_exploit($comm);
	}
	
}

/* WordPress Conf */

function get_plugins($url)
{
	
	$source = @file_get_contents($url);
	
	preg_match_all("#/plugins/(.*?)/#i", $source, $f);
	
	$arz = array_unique($f[1]);
	
	if (count($arz) == 0) {
		echo "<tr><td colspan=3>[ Wordpress ] ...Nothing Found !</td></tr>";
	}
	
	foreach ($arz as $plugin) {
		
		echo "<tr><td>$plugin</td>";
		
		check_exploit($plugin);
		
	}
	
}

/**************************************************************/
/* Nuke's Conf */

function get_numod($url)
{
	
	$source = @file_get_contents($url);
	
	preg_match_all('{?name=(.*?)/}i', $source, $f);
	preg_match_all('{?name=(.*?)(&amp;|&|l_op=")}i', $source, $f2);
	preg_match_all('{/modules/(.*?)/}i', $source, $f3);
	
	$arz = array_merge($f2[1], $f[1], $f3[1]);
	
	$cpm = array();
	
	if (count($arz) == 0) {
		echo "<tr><td colspan=3>[ Nuke's ] ...Nothing Found !</td></tr>";
	}
	
	foreach (array_unique($arz) as $x) {
		
		$cpm[] = $x;
	}
	
	foreach ($cpm as $nmod) {
		
		echo "<tr><td>$nmod</td>";
		
		check_exploit($nmod);
		
	}
	
}

/*****************************************************/
/* Xoops Conf */

function get_xoomod($url)
{
	
	$source = @file_get_contents($url);
	
	preg_match_all('{/modules/(.*?)/}i', $source, $f);
	
	$arz = array_merge($f[1]);
	
	$cpm = array();
	
	if (count($arz) == 0) {
		echo "<tr><td colspan=3>[ Xoops ] ...Nothing Found !</td></tr>";
	}
	
	foreach (array_unique($arz) as $x) {
		
		$cpm[] = $x;
	}
	
	foreach ($cpm as $xmod) {
		
		echo "<tr><td>$xmod</td>";
		
		check_exploit($xmod);
		
	}
	
}

/**************************************************************/

function sec($site)
{
	preg_match_all('{http://(.*?)(/index.php)}siU', $site, $sites);
	if (eregi("www", $sites[0][0])) {
		return $site = str_replace("index.php", "", $sites[0][0]);
	} else {
		return $site = str_replace("http://", "http://www.", str_replace("index.php", "", $sites[0][0]));
	}
}

$npages = 50000;

if ($_POST) {
	$ip       = trim(strip_tags($_POST['site']));
	$npage    = 1;
	$allLinks = array();
	
	
	while ($npage <= $npages) {
		
		$x = @file_get_contents('http://www.bing.com/search?q=ip%3A' . $ip . '+index.php?option=com&first=' . $npage);
		
		
		if ($x) {
			preg_match_all('(<div class="sb_tlst">.*<h3>.*<a href="(.*)".*>(.*)</a>.*</h3>.*</div>)siU', $x, $findlink);
			
			foreach ($findlink[1] as $fl)
				$allLinks[] = sec($fl);
			
			
			$npage = $npage + 10;
			
			if (preg_match('(first=' . $npage . '&amp)siU', $x, $linksuiv) == 0)
				break;
		}
		
		else
			break;
	}
	
	
	$allDmns = array();
	
	foreach ($allLinks as $kk => $vv) {
		
		$allDmns[] = $vv;
	}
	
	echo '<table border="1"  width=\"80%\" align=\"center\">
<tr><td width=\"30%\"><b>Server IP&nbsp;&nbsp;&nbsp;&nbsp; : </b></td><td><b>' . $ip . '</b></td></tr>			
<tr><td width=\"30%\"><b>Sites Found&nbsp; : </b></td><td><b>' . count(array_unique($allDmns)) . '</b></td></tr>
</table>';
	echo "<br><br>";
	
	echo '<table border="1" width="80%" align=\"center\">';
	
	foreach (array_unique($allDmns) as $h3h3) {
		
		echo '<tr id=new><td><b><a href=' . $h3h3 . '>' . $h3h3 . '</a></b></td><td><b>PacketStorm</b></td><td><b>Challenge of Exploiting ..!</b></td></tr>';
		
		check_com($h3h3);
		get_plugins($h3h3);
		get_numod($h3h3);
		get_xoomod($h3h3);
	}
	
	echo "</table>";
	
}
?></center>
<br><p align="center">
Coded By : <a href='http://facebook.com/KedAns'>KedAns-Dz</a> | <a href='http://1337day.com/'>Inj3ct0r 1337day Exploit Database</a><br>
Made in Algeria | CopyCenter (^.^) 2o12
</p>
</body>
</html>
<!-- ' Thanks to Lagripe-Dz aNd K!LLer-Dz'-->
