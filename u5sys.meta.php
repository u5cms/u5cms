<?php
require_once ('connect.inc.php');
require_once('login.inc.php');
require_once ('globals.inc.php');

$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND name='" . (mysql_real_escape_string($_GET['c'])) . "'";
$result_a = mysql_query($sql_a);

if ($result_a == false) echo 'SQL_a-Query failed!...!<p>';
$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);

if ($num_a==0 || str_replace(' ','',$row_a['name'])=='') {
	$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
	$result_a = mysql_query($sql_a);
	if ($result_a == false) echo 'SQL_a-Query failed!...!<p>';
	$row_a = mysql_fetch_array($result_a);
}

$sql_b="SELECT * FROM titlefixum";
$result_b=mysql_query($sql_b);
if ($result_b==false) echo 'SQL_b-Query failed!...!<p>';
$row_b = mysql_fetch_array($result_b);

if ($_GET['l']==$lan5na) $lancode=$lan5na;
else if ($_GET['l']==$lan4na) $lancode=$lan4na;
else if ($_GET['l']==$lan3na) $lancode=$lan3na;
else if ($_GET['l']==$lan2na) $lancode=$lan2na;
else $lancode=$lan1na;
?>
<html lang="<?php echo $lancode ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<meta http-equiv="content-language" content="<?php echo $lancode ?>" />
<?php
if (str_replace(' ','',ehtml(def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5'])))!='') echo '<meta name="description" content="'.ehtml(def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5'])).'" />';
if (str_replace(' ','',ehtml(def($row_a['key_1'],$row_a['key_2'],$row_a['key_3'],$row_a['key_4'],$row_a['key_5'])))!='') echo '<meta name="keywords" content="'.ehtml(def($row_a['key_1'],$row_a['key_2'],$row_a['key_3'],$row_a['key_4'],$row_a['key_5'])).'" />';
?>
<?php if ($row_a['hidden']==-1) echo '<meta name="robots" content="noindex, nofollow" />' ?>
<title><?php echo ehtml(def($row_a['title_1'],$row_a['title_2'],$row_a['title_3'],$row_a['title_4'],$row_a['title_5'])) ?><?php echo ehtml(def($row_b['1'],$row_b['2'],$row_b['3'],$row_b['4'],$row_b['5'])) ?></title>
<link rel="stylesheet" href="fallback.css" type="text/css" />
<?php
if(strpos($canonicalprotocolanddomain,'http://')===0 || strpos($canonicalprotocolanddomain,'https://')===0) {
	if(($num_a==0||$row_a['ishomepage']==1)&&$_GET['c']!='_search'&&$_GET['c']!='_sitemap')$canonicalcalculatedurl=$canonicalprotocolanddomain;
	else $canonicalcalculatedurl=$canonicalprotocolanddomain.str_replace('index.php','',$_SERVER['REQUEST_URI']);
	if(strpos($canonicalcalculatedurl,'?')<1)$canonicalcalculatedurl.='?';
	if(!preg_match('/(?:^|&)l=/', substr($canonicalcalculatedurl, strpos($canonicalcalculatedurl, '?') + 1)))$canonicalcalculatedurl.='&l='.$lancode;
	$canonicalcalculatedurl=str_replace('//?','/?',str_replace('?&','?',$canonicalcalculatedurl));
	echo '<link rel="canonical" href="'.$canonicalcalculatedurl.'" />
';

	list($canonicalPath, $canonicalQuery) = explode('?', $canonicalcalculatedurl, 2);
	foreach (array($lan1na, $lan2na, $lan3na, $lan4na, $lan5na) as $alternateLanguage) {
		if ($alternateLanguage == '') continue;
		$alternateUrl = $canonicalPath.'?'.preg_replace('/(^|&)l=[^&]*/', '${1}l='.rawurlencode($alternateLanguage), $canonicalQuery);
		echo '<link rel="alternate" hreflang="'.$alternateLanguage.'" href="'.$alternateUrl.'" />';
	}
}
else if($canonicalprotocolanddomain!='') echo '<script>alert("Error in config.php: The value for $canonicalprotocolanddomain must begin with the protocol https:// or http://")</script>';
?>
