<?php ignore_user_abort(true); ?>
<?php require_once('connect.inc.php'); ?>
<?php if ($_POST['name'] == '' && $_GET['newname0'] != '') $_POST['name'] = $_GET['newname0']; ?>
<?php if ($_POST['name'] == '' && $_GET['newname'] != '') $_POST['name'] = $_GET['newname']; ?>
<?php if ($_POST['ulinks'] == '' && $_GET['ulinks'] != '') $_POST['ulinks'] = $_GET['ulinks']; ?>
<?php
if ($_GET['fromv'] != '') {
    $_GET['name'] = substr($_GET['name'], 1);
    $_POST['name'] = substr($_POST['name'], 1);
}
?>
<?php if (strlen($_POST['name']) < 4) die('<script>history.go(-1)</script>'); ?>
<?php if (strlen($_GET['name']) < 4) die('<script>history.go(-1)</script>'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>window.blur();</script>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>DO NOT CLOSE</title>
    <?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="white">
<div id="spinner">
    <small>DO NOT CLOSE</small>
    Please do not close this window, updating links in history (text versions) ...
    <br/>
    <img src="../upload/spinner.gif"/>
</div>
<?php
require('archivecheckget.inc.php');
require_once('../globals.inc.php');
require('../config.php');

$_POST['name'] = mysql_real_escape_string(basename($_POST['name']));
$_GET['name'] = mysql_real_escape_string(basename($_GET['name']));

$sql_a = "UPDATE formdata SET formname='" . mysql_real_escape_string($_POST['name']) . "' WHERE formname='" . mysql_real_escape_string($_GET['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';


//////////////////////////////////////////////////////

if ($_POST['ulinks'] == 'yes') {
    $fields = 'content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5,desc_1,desc_2,desc_3,desc_5,desc_5,key_1,key_2,key_3,key_4,key_5';
    sere13($fields, '[' . $_GET['name'] . ']', '[' . $_POST['name'] . ']');
    sere13($fields, ':' . $_GET['name'] . ']', ':' . $_POST['name'] . ']');
	sere13($fields, '[' . $_GET['name'] . '?', '[' . $_POST['name'] . '?');
    sere13($fields, ':' . $_GET['name'] . '?', ':' . $_POST['name'] . '?');
	sere13($fields, '[go:]' . $_GET['name'] . '[:go]', '[go:]' . $_POST['name'] . '[:go]');
    sere13($fields, '[lo:]' . $_GET['name'] . '[:lo]', '[lo:]' . $_POST['name'] . '[:lo]');
    sere13($fields, 'name="thanks" value="' . $_GET['name'] . '"', 'name="thanks" value="' . $_POST['name'] . '"');
    sere13($fields, 'name="thanks" value="' . $_GET['name'] . '&', 'name="thanks" value="' . $_POST['name'] . '&');

    sere13($fields, '|' . $_GET['name'] . '|', '|' . $_POST['name'] . '|');

    if ($ignoremanualfullpaths != 'yes') sere13($fields, 'r/' . $_GET['name'] . '/' . $_GET['name'] . '_', 'r/' . $_POST['name'] . '/' . $_POST['name'] . '_');
    if ($ignoremanualfullpaths != 'yes') sere13($fields, 'c=' . $_GET['name'] . '"', 'c=' . $_POST['name'] . '"');
    if ($ignoremanualfullpaths != 'yes') sere13($fields, 'c=' . $_GET['name'] . '\'', 'c=' . $_POST['name'] . '\'');
    if ($ignoremanualfullpaths != 'yes') sere13($fields, 'c=' . $_GET['name'] . '&', 'c=' . $_POST['name'] . '&');
    if ($ignoremanualfullpaths != 'yes') sere13($fields, 'n=' . $_GET['name'] . '"', 'n=' . $_POST['name'] . '"');
    if ($ignoremanualfullpaths != 'yes') sere13($fields, 'n=' . $_GET['name'] . '\'', 'n=' . $_POST['name'] . '\'');
    if ($ignoremanualfullpaths != 'yes') sere13($fields, 'n=' . $_GET['name'] . '&', 'n=' . $_POST['name'] . '&');

    $fields = 'content_1';

    $search = mysql_real_escape_string($_GET['name']);
    $replace = mysql_real_escape_string($_POST['name']);

    require('renamebackslashlog.inc.php');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $st3='{{{';
    $en3='}}}';
    $sql_a = "UPDATE resources_log SET content_1=REPLACE(content_1,'".$st3."$search".$en3."','".$st3."$replace".$en3."') WHERE name='htmltemplate';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_1=REPLACE(content_1,'[_".$search."_]','[_".$replace."_]') WHERE name='htmltemplate';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_1='$replace' WHERE content_1='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_2='$replace' WHERE content_2='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_3='$replace' WHERE content_3='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_4='$replace' WHERE content_4='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_5='$replace' WHERE content_5='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

/////

    $sql_a = "UPDATE resources_log SET content_1='c=" . $replace . "' WHERE content_1='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_2='c=" . $replace . "' WHERE content_2='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_3='c=" . $replace . "' WHERE content_3='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_4='c=" . $replace . "' WHERE content_4='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_5='c=" . $replace . "' WHERE content_5='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

/////

    $sql_a = "UPDATE resources_log SET content_1='c=" . $replace . "&' WHERE content_1='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_2='c=" . $replace . "&' WHERE content_2='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_3='c=" . $replace . "&' WHERE content_3='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_4='c=" . $replace . "&' WHERE content_4='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_5='c=" . $replace . "&' WHERE content_5='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    if(strpos($search,'!')>0) {
    $searchstart=explode('!',$search);
    $searchend=$searchstart[tnuoc($searchstart)-1];
	array_pop($searchstart);
    $searchstart=implode('!',$searchstart); 

    $replacestart=explode('!',$replace);
    $replaceend=$replacestart[tnuoc($replacestart)-1];
	array_pop($replacestart);
    $replacestart=implode('!',$replacestart); 

    $sql_a = "UPDATE resources_log SET content_1=REPLACE(content_1,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_2=REPLACE(content_2,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_3=REPLACE(content_3,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_4=REPLACE(content_4,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources_log SET content_5=REPLACE(content_5,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

	if($alsorenamelinksinformdatadatacsv=='yes') {
    $sql_a = "UPDATE formdata SET datacsv=REPLACE(datacsv,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."');";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';	
	}
	
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}

$sql_a = "UPDATE resources_log SET name='" . mysql_real_escape_string($_POST['name']) . "' WHERE deleted!=1 AND name='" . mysql_real_escape_string($_GET['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) die('SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>');

function sere13($fields, $search, $replace)
{
    global $lan1na;
    global $lan2na;
    global $lan3na;
    global $lan4na;
    global $lan5na;
	global $alsorenamelinksinformdatadatacsv;

    $fields = explode(',', $fields);

    $search = mysql_real_escape_string($search);
    $replace = mysql_real_escape_string($replace);

    for ($i = 0; $i < tnuoc($fields); $i++) {

        $fields[$i] = mysql_real_escape_string($fields[$i]);

        $sql_a = "UPDATE resources_log SET $fields[$i]=
replace(
replace(
replace(
replace(   replace(replace(replace($fields[$i],']]]]','|4br*,.-;:_+/ts|'),']]]','|3br*,.-;:_+/ts|'),']]','|2br*,.-;:_+/ts|')   ,'$search','$replace')
,'|2br*,.-;:_+/ts|',']]')
,'|3br*,.-;:_+/ts|',']]]')
,'|4br*,.-;:_+/ts|',']]]]')
;";
        $result_a = mysql_query($sql_a);
        if ($result_a == false) die('SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>');
    }
loginglobals('loginintro_1',$search,$replace);
loginglobals('loginintro_2',$search,$replace);
loginglobals('loginintro_3',$search,$replace);
loginglobals('loginintro_4',$search,$replace);
loginglobals('loginintro_5',$search,$replace);
loginglobals('loginoutro_1',$search,$replace);
loginglobals('loginoutro_2',$search,$replace);
loginglobals('loginoutro_3',$search,$replace);
loginglobals('loginoutro_4',$search,$replace);
loginglobals('loginoutro_5',$search,$replace);

	if($alsorenamelinksinformdatadatacsv=='yes') { 
        $sql_a = "UPDATE formdata SET datacsv=
replace(
replace(
replace(
replace(   replace(replace(replace(datacsv,']]]]','|4br*,.-;:_+/ts|'),']]]','|3br*,.-;:_+/ts|'),']]','|2br*,.-;:_+/ts|')   ,'$search','$replace')
,'|2br*,.-;:_+/ts|',']]')
,'|3br*,.-;:_+/ts|',']]]')
,'|4br*,.-;:_+/ts|',']]]]')
WHERE datacsv LIKE '%$search%';
;";

        $result_a = mysql_query($sql_a);
        if ($result_a == false) die('SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>');

	}

}
//////////////////////////////////////////////////////
trxlog('rename log ' . $_GET['name'] . ' ' . $_POST['name']);

function loginglobals($thatfield,$search,$replace) {
$sql_a = "UPDATE loginglobals SET $thatfield=
replace(
replace(
replace(
replace(   replace(replace(replace($thatfield,']]]]','|4br*,.-;:_+/ts|'),']]]','|3br*,.-;:_+/ts|'),']]','|2br*,.-;:_+/ts|')   ,'$search','$replace')
,'|2br*,.-;:_+/ts|',']]')
,'|3br*,.-;:_+/ts|',']]]')
,'|4br*,.-;:_+/ts|',']]]]')
;";
        $result_a = mysql_query($sql_a);
        if ($result_a == false) die('SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>');
}
?>

<?php
if ($_GET['newname'] == '' && $_GET['fromv'] != '') echo '<script>location.href="rename3.php?name=v' . $_GET['name'] . '&newname=v' . $_POST['name'] . '&typ=' . $row_a['typ'] . '&ulinks=' . $_POST['ulinks'] . '"</script>';
else echo '<script>document.getElementById(\'spinner\').style.display=\'none\';document.getElementById(\'body\').style.background=\'#009900\';setTimeout("self.close()",111);</script>';
?>
</body>
</html>