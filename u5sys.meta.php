<?php
require_once ('connect.inc.php');
require_once('login.inc.php');
require_once ('globals.inc.php');

$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND name='" . (mysql_real_escape_string($_GET['c'])) . "'";
$result_a = mysql_query($sql_a);

if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);

if ($num_a==0 || str_replace(' ','',$row_a['name']=='')) {
$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
$row_a = mysql_fetch_array($result_a);
}

$sql_b="SELECT * FROM titlefixum";
$result_b=mysql_query($sql_b);
if ($result_b==false) echo 'SQL_b-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';
$row_b = mysql_fetch_array($result_b);

if ($_GET['l']==$lan3na) $lancode=$lan3na;
else if ($_GET['l']==$lan2na) $lancode=$lan2na;
else $lancode=$lan1na;   
?>
<html lang="<?php echo $lancode ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-language" content="<?php echo $lancode ?>" />
<?php 
if (str_replace(' ','',ehtml(def($row_a['desc_d'],$row_a['desc_e'],$row_a['desc_f'])))!='') echo '<meta name="description" content="'.ehtml(def($row_a['desc_d'],$row_a['desc_e'],$row_a['desc_f'])).'" />';
if (str_replace(' ','',ehtml(def($row_a['key_d'],$row_a['key_e'],$row_a['key_f'])))!='') echo '<meta name="keywords" content="'.ehtml(def($row_a['key_d'],$row_a['key_e'],$row_a['key_f'])).'" />';
?>
<?php if ($row_a['hidden']==-1) echo '<meta name="robots" content="noindex, nofollow" />' ?>
<title><?php echo ehtml(def($row_a['title_d'],$row_a['title_e'],$row_a['title_f'])) ?><?php echo ehtml(def($row_b['d'],$row_b['e'],$row_b['f'])) ?></title>
<link rel="stylesheet" href="fallback.css" type="text/css" />
<?php
if(strpos($canonicalprotocolanddomain,'http://')===0 || strpos($canonicalprotocolanddomain,'https://')===0) {
if(($num_a==0||$row_a['ishomepage']==1)&&$_GET['c']!='_search'&&$_GET['c']!='_sitemap')$canonicalcalculatedurl=$canonicalprotocolanddomain;
else $canonicalcalculatedurl=$canonicalprotocolanddomain.str_replace('index.php','',$_SERVER['REQUEST_URI']);
if(strpos($canonicalcalculatedurl,'?')<1)$canonicalcalculatedurl.='?';
if(strpos($canonicalcalculatedurl,'?l=')<1 && strpos($canonicalcalculatedurl,'&l')<1)$canonicalcalculatedurl.='&l='.$lancode;
$canonicalcalculatedurl=str_replace('//?','/?',str_replace('?&','?',$canonicalcalculatedurl));
echo '<link rel="canonical" href="'.$canonicalcalculatedurl.'" />
';
if($lan1na!='' && $lan1na<1) echo '<link rel="alternate" hreflang="'.$lan1na.'" href="'.
str_replace('&l='.$lan2na,'&l='.$lan1na,str_replace('?l='.$lan2na,'?l='.$lan1na,
str_replace('&l='.$lan3na,'&l='.$lan1na,str_replace('?l='.$lan3na,'?l='.$lan1na,
$canonicalcalculatedurl)))).'" />';

if($lan2na!='' && $lan2na<1) echo '<link rel="alternate" hreflang="'.$lan2na.'" href="'.
str_replace('&l='.$lan1na,'&l='.$lan2na,str_replace('?l='.$lan1na,'?l='.$lan2na,
str_replace('&l='.$lan3na,'&l='.$lan2na,str_replace('?l='.$lan3na,'?l='.$lan2na,
$canonicalcalculatedurl)))).'" />';

if($lan3na!='' && $lan3na<1) echo '<link rel="alternate" hreflang="'.$lan3na.'" href="'.
str_replace('&l='.$lan1na,'&l='.$lan3na,str_replace('?l='.$lan1na,'?l='.$lan3na,
str_replace('&l='.$lan2na,'&l='.$lan3na,str_replace('?l='.$lan2na,'?l='.$lan3na,
$canonicalcalculatedurl)))).'" />';
}
else if($canonicalprotocolanddomain!='') echo '<script>alert("Error in config.php: The value for $canonicalprotocolanddomain must begin with the protocol https:// or http://")</script>';
?>