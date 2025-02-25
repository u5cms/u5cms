<?php ignore_user_abort(true);set_time_limit(3600); ?>
<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<?php if ($_POST['name'] == '' && $_GET['newname'] != '') $_POST['name'] = $_GET['newname']; ?>
<?php if ($_POST['ulinks'] == '' && $_GET['ulinks'] != '') $_POST['ulinks'] = $_GET['ulinks']; ?>
<?php if (strlen($_POST['name']) < 4) die('<script>history.go(-1)</script>'); ?>
<?php if (strlen($_GET['name']) < 4) die('<script>history.go(-1)</script>'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
changes=0;
if(opener)if(opener.parent)if(opener.parent.i1.changes)changes+=opener.parent.i1.changes;
if(opener)if(opener.parent)if(opener.parent.i2.changes)changes+=opener.parent.i2.changes;
if(changes>0) {
alert('RENAME COMMAND NOT EXECUTED!\n\nYou have to save the changes made in the editor(s) before performing a rename command.');
self.close();
window.stop();
}
</script>
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

$sql_a = "DELETE FROM resources WHERE deleted=1 AND name='" . mysql_real_escape_string($_POST['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false)
    echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "SELECT name FROM resources WHERE deleted!=1 AND name='" . mysql_real_escape_string($_POST['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
$num_a = mysql_num_rows($result_a);
if ($num_a > 0) die('<script>document.getElementById("body").style.background="red";;alert("ERROR: Target already exists!");history.go(-1)</script>');

$sql_a = "UPDATE formdata SET formname='" . mysql_real_escape_string($_POST['name']) . "' WHERE formname='" . mysql_real_escape_string($_GET['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';


//////////////////////////////////////////////////////

if ($_POST['ulinks'] == 'yes') {
    $fields = 'content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5,desc_1,desc_2,desc_3,desc_4,desc_5,key_1,key_2,key_3,key_4,key_5';
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

    require('renamebackslash.inc.php');

    $st3='{{{';
    $en3='}}}';
    $sql_a = "UPDATE resources SET content_1=REPLACE(content_1,'".$st3."$search".$en3."','".$st3."$replace".$en3."') WHERE name='htmltemplate';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_1=REPLACE(content_1,'[_".$search."_]','[_".$replace."_]') WHERE name='htmltemplate';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
	
	$sql_a = "UPDATE resources SET content_1='$replace' WHERE content_1='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_2='$replace' WHERE content_2='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_3='$replace' WHERE content_3='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_4='$replace' WHERE content_4='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_5='$replace' WHERE content_5='$search' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

/////

    $sql_a = "UPDATE resources SET content_1='c=" . $replace . "' WHERE content_1='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_2='c=" . $replace . "' WHERE content_2='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_3='c=" . $replace . "' WHERE content_3='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_4='c=" . $replace . "' WHERE content_4='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_5='c=" . $replace . "' WHERE content_5='c=" . $search . "' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

/////

    $sql_a = "UPDATE resources SET content_1='c=" . $replace . "&' WHERE content_1='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_2='c=" . $replace . "&' WHERE content_2='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_3='c=" . $replace . "&' WHERE content_3='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_4='c=" . $replace . "&' WHERE content_4='c=" . $search . "&' AND typ='f';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_5='c=" . $replace . "&' WHERE content_5='c=" . $search . "&' AND typ='f';";
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

    $sql_a = "UPDATE resources SET content_1=REPLACE(content_1,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_2=REPLACE(content_2,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_3=REPLACE(content_3,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_4=REPLACE(content_4,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    $sql_a = "UPDATE resources SET content_5=REPLACE(content_5,'[_pagename!_]!".$searchend."','[_pagename!_]!".$replaceend."') WHERE name LIKE '".$searchstart."!%';";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}

$sql_a = "DELETE FROM resources WHERE deleted=1 AND name='" . mysql_real_escape_string($_POST['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources SET name='" . mysql_real_escape_string($_POST['name']) . "' WHERE deleted!=1 AND name='" . mysql_real_escape_string($_GET['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) die('SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>');

function sere13($fields, $search, $replace)
{
    global $lan1na;
    global $lan2na;
    global $lan3na;
    global $lan4na;
    global $lan5na;

    $fields = explode(',', $fields);

    $search = mysql_real_escape_string($search);
    $replace = mysql_real_escape_string($replace);

    for ($i = 0; $i < tnuoc($fields); $i++) {

        $fields[$i] = mysql_real_escape_string($fields[$i]);

        $sql_a = "UPDATE resources SET lastmut=".time().", operator=concat('R ',operator), $fields[$i]=
replace(
replace(
replace(
replace(   replace(replace(replace($fields[$i],']]]]','|4br*,.-;:_+/ts|'),']]]','|3br*,.-;:_+/ts|'),']]','|2br*,.-;:_+/ts|')   ,'$search','$replace')
,'|2br*,.-;:_+/ts|',']]')
,'|3br*,.-;:_+/ts|',']]]')
,'|4br*,.-;:_+/ts|',']]]]')
WHERE $fields[$i] LIKE '%$search%';
;";

        $result_a = mysql_query($sql_a);
        if ($result_a == false) die('SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>');

    }
}

//////////////////////////////////////////////////////

if(file_exists('../r/' . $_POST['name']))@rename('../r/' . $_POST['name'], '../r/' . $_POST['name'].time());
@rename('../r/' . $_GET['name'], '../r/' . $_POST['name']);

$path = '../r/' . $_POST['name'];
if ($handle = @opendir($path)) {
    while (false !== ($file = readdir($handle))) {

        $ext = explode('.', $file);
        $ext = $ext[tnuoc($ext) - 1];


        if (file_exists('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan1na . '.' . $ext)) rename('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan1na . '.' . $ext, '../r/' . $_POST['name'] . '/' . $_POST['name'] . '_' . $lan1na . '.' . $ext);
        if (file_exists('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan2na . '.' . $ext)) rename('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan2na . '.' . $ext, '../r/' . $_POST['name'] . '/' . $_POST['name'] . '_' . $lan2na . '.' . $ext);
        if (file_exists('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan3na . '.' . $ext)) rename('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan3na . '.' . $ext, '../r/' . $_POST['name'] . '/' . $_POST['name'] . '_' . $lan3na . '.' . $ext);
        if (file_exists('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan4na . '.' . $ext)) rename('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan4na . '.' . $ext, '../r/' . $_POST['name'] . '/' . $_POST['name'] . '_' . $lan4na . '.' . $ext);
        if (file_exists('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan5na . '.' . $ext)) rename('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $lan5na . '.' . $ext, '../r/' . $_POST['name'] . '/' . $_POST['name'] . '_' . $lan5na . '.' . $ext);

        for ($a = 100; $a < 1000; $a++) {
            if (file_exists('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $a . '.' . $ext)) rename('../r/' . $_POST['name'] . '/' . $_GET['name'] . '_' . $a . '.' . $ext, '../r/' . $_POST['name'] . '/' . $_POST['name'] . '_' . $a . '.' . $ext);
        }
    }
}
///////////////////////////////////////////////////////
require('putcss.inc.php');
//////////////////////////////////////////////////////


trxlog('rename ' . $_GET['name'] . ' ' . $_POST['name']);

$sql_a = "SELECT typ FROM resources WHERE name='" . mysql_real_escape_string($_POST['name']) . "'";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$row_a = mysql_fetch_array($result_a);

if ($_GET['name'][0] == '!' && $_POST['name'] != '!') {
    $sql_a = "UPDATE resources SET logins='' WHERE name='" . mysql_real_escape_string($_POST['name']) . "'";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}

?>
<script>
if(opener) {
    if ('<?php echo $_GET['newname'] ?>' == '') opener.parent.save.location.href = 'done.php?n=renamed <?php echo $_GET['name']?> to <?php echo $_POST['name']?>';

    if ('<?php echo $row_a['typ']?>' == 'p' && '<?php echo $_GET['name'] ?>' == opener.parent.i1.document.form1.page.value) opener.parent.i1.gotopage('<?php echo $_POST['name'] ?>');
    else opener.parent.i1.gotopage(opener.parent.i1.document.form1.page.value);
    if ('<?php echo $row_a['typ']?>' == 'p' && '<?php echo $_GET['name'] ?>' == opener.parent.i2.document.form1.page.value) opener.parent.i2.gotopage('<?php echo $_POST['name'] ?>');
    else opener.parent.i2.gotopage(opener.parent.i2.document.form1.page.value);
    opener.focus();self.close();
}
</script>

<?php
$_GET['newname0']=$_POST['name'];
$_GET['typ']=$row_a['typ'];
$_GET['fromv']=$_GET['newname'];
$_GET['ulinks']=$_POST['ulinks'];
require_once('rename3.php')
?>
</body>
</html>