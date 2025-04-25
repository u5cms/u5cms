<?php
require_once('connect.inc.php');
if (isset($_POST['s'])) {
    if ($_POST['snptrm'] == 'off') setcookie('snptrm', 'off', time() + 3600 * 24 * 365 * 10, '/');
    if ($_POST['snptrm'] != 'off') setcookie('snptrm', 'on', time() + 3600 * 24 * 365 * 10, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>code snippets search</title>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

<body onresize="resizer()">
<h1>Search for code snippets <span style="font-size:40%"><a href="snippets.php">single line</a></span></h1>

<form name="sf" method="post">
    <?php
    if ($_POST['snptrm'] == 'off') $posts = $_POST['s'];
    else $posts = trim($_POST['s']) ?>
    <textarea style="width:700px;height:100px" id="id_s" name="s"><?php echo ehtml($posts) ?></textarea><br/><br/>
    <div style="margin-left:300px"><input type="submit" value="search"/>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <small><span
                onclick="if(document.getElementById('id_snptrm').checked==true)document.getElementById('id_snptrm').checked=false;else document.getElementById('id_snptrm').checked=true">no trim</span><input
                type="checkbox" value="off" name="snptrm" id="id_snptrm"/></small>
    </div>
<?php require('t1.php') ?></form>
<script>
    document.getElementById('id_s').focus();
    <?php if($_POST['snptrm']=='off') echo "document.getElementById('id_snptrm').checked=true;"?>
    <?php if(!isset($_POST['s']) && $_COOKIE['snptrm']=='off')  echo "document.getElementById('id_snptrm').checked=true;"?>
</script>
<?php

if (!isset($_POST['s'])) {
   include('selfclose.inc.php');
   exit;
   }


$s = mysql_real_escape_string($posts);

$sql_a = "SELECT name, typ, deleted FROM resources WHERE name!='-' AND deleted!=1 AND name LIKE '$s'";
$result_a = mysql_query($sql_a);

if ($result_a == false) echo 'SQL_a-Query failed!...!<p>';

$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);

if ($row_a['deleted'] == '2') $inarh = ' <span style="color:white;background:black">a</span> ';

if ($num_a > 0) {

    if ($row_a['deleted'] == 2) $isarchived = '<small><small>(a)</small></small>';
    else $isarchived = '';

    echo '<br><a style="text-decoration:none" title="localize (where linked in)" href="localize.php?name='.$row_a['name'].'"><span id="oo_'.$row_a['name'].'">L</span></a>&nbsp;<a style="text-decoration:none" title="focus in repository" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {opener.location.href=\'focus.php?c=' . $row_a['name'] . '\'}">F</a>&nbsp;';

    echo '&nbsp;The name <big>' . $row_a['name'] . '</big> ' . $isarchived . ' is a type <big>' . $row_a['typ'] . $inarh . '</big> object ';
    if ($row_a['typ'] == 'p') echo '<a style="text-decoration:none" title="Open right (+Alt=left)" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {if(event.altKey)opener.parent.i1.lgotopage(\'' . $row_a['name'] . '\');else opener.parent.i2.gotopage(\'' . $row_a['name'] . '\')}"> &rarr;</a><br>';
    else  echo '<a style="text-decoration:none" title="show link to data" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else{opener.location.href=\'open.php?name=' . $row_a['name'] . '&typ=' . $row_a['typ'] . '\'}"> &rarr;&hellip;</a><br>';
}

$sql_a = "SELECT * FROM resources WHERE name!='-' AND deleted!=1 AND typ!='s' AND (
content_1 LIKE '%$s%' OR
content_2 LIKE '%$s%' OR
content_3 LIKE '%$s%' OR
content_4 LIKE '%$s%' OR
content_5 LIKE '%$s%' OR

desc_1 LIKE '%$s%' OR
desc_2 LIKE '%$s%' OR
desc_3 LIKE '%$s%' OR
desc_4 LIKE '%$s%' OR
desc_5 LIKE '%$s%' OR

title_1 LIKE '%$s%' OR
title_2 LIKE '%$s%' OR
title_3 LIKE '%$s%' OR
title_4 LIKE '%$s%' OR
title_5 LIKE '%$s%') ORDER BY lastmut DESC;
";

$result_a = mysql_query($sql_a);

if ($result_a == false) {
    echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

if($num_a==1)$plurals='';
else $plurals='s';
if($num_a==0)$colon='.';
else $colon=':';
echo '<h5>String or substring found in '.$num_a.' object'.$plurals.$colon.'</h5>';

for ($i_a = 0; $i_a < $num_a; $i_a++) {
    $row_a = mysql_fetch_array($result_a);

    if ($row_a['deleted'] == 2) $isarchived = '<small><small>(a)</small></small>';
    else $isarchived = '';

    echo '<h2 style="color:gray"><a style="text-decoration:none;font-size:16px" title="localize (where linked in)" href="localize.php?name='.$row_a['name'].'"><span id="o_'.$row_a['name'].'">L</span></a>&nbsp;<a style="text-decoration:none;font-size:16px" title="focus in repository" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {opener.location.href=\'focus.php?c=' . $row_a['name'] . '\'}">F</a>&nbsp;' . $row_a['typ'] . ': <span style="color:black">' . $row_a['name'] . ' ' . $isarchived . '</span> <small>' . date('Y-m-d H:i:s', $row_a['lastmut']) . ' ' . $row_a['operator'];

    if ($row_a['typ'] == 'p') echo '<a style="text-decoration:none" title="Open right (+Alt=left)" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {if(event.altKey)opener.parent.i1.lgotopage(\'' . $row_a['name'] . '\');else opener.parent.i2.gotopage(\'' . $row_a['name'] . '\')}"> &rarr;</a><br>';
    else  echo '<a style="text-decoration:none" title="show link to data" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else{opener.location.href=\'open.php?name=' . $row_a['name'] . '&typ=' . $row_a['typ'] . '\'}"> &rarr;&hellip;</a><br>';


    echo '</small></h2>';

    echo '
<table>
<tr><td><textarea rows="2" id="t' . $i_a . 'a1">' . ehtml($row_a['title_1']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a2">' . ehtml($row_a['title_2']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a3">' . ehtml($row_a['title_3']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a41">' . ehtml($row_a['title_4']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a51">' . ehtml($row_a['title_5']) . '</textarea></td></tr>
<tr><td><textarea rows="2" id="t' . $i_a . 'a4">' . ehtml($row_a['desc_1']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a5">' . ehtml($row_a['desc_2']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a6">' . ehtml($row_a['desc_3']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a42">' . ehtml($row_a['desc_4']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a52">' . ehtml($row_a['desc_5']) . '</textarea></td></tr>
<tr><td><textarea rows="11" id="t' . $i_a . 'a7">' . ehtml($row_a['content_1']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a8">' . ehtml($row_a['content_2']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a9">' . ehtml($row_a['content_3']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a43">' . ehtml($row_a['content_4']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a53">' . ehtml($row_a['content_5']) . '</textarea></td></tr>
</table>

';
}
?>

<script>
    function sizer() {
        for (i = 0; i < 1000; i++) {
            if (document.getElementById('t' + i + 'a1')) document.getElementById('t' + i + 'a1').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a2')) document.getElementById('t' + i + 'a2').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a3')) document.getElementById('t' + i + 'a3').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a4')) document.getElementById('t' + i + 'a4').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a5')) document.getElementById('t' + i + 'a5').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a6')) document.getElementById('t' + i + 'a6').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a7')) document.getElementById('t' + i + 'a7').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a8')) document.getElementById('t' + i + 'a8').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a9')) document.getElementById('t' + i + 'a9').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a41')) document.getElementById('t' + i + 'a41').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a51')) document.getElementById('t' + i + 'a51').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a42')) document.getElementById('t' + i + 'a42').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a52')) document.getElementById('t' + i + 'a52').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a43')) document.getElementById('t' + i + 'a43').style.width = document.documentElement.clientWidth / 3 - res + 'px';
            if (document.getElementById('t' + i + 'a53')) document.getElementById('t' + i + 'a53').style.width = document.documentElement.clientWidth / 3 - res + 'px';
        }
    }

    function resizer() {
        res = 30;
        if (document.getElementById('t0a1')) if (document.getElementById('t0a1').style.width != document.documentElement.clientWidth / 3 - res) sizer();
    }
    setTimeout("resizer()", 1);
</script>

<script>
    window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php') ?>
<?php 
$sqlget="SELECT * FROM resources WHERE deleted!=1";
$h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$sqlget)
?>
<iframe style="display:none" src="filter2.inc.php?sql=<?php echo rawurlencode($sqlget)?>&h=<?php echo $h ?>"></iframe>
</body>
</html>