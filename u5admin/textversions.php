<?php
require_once('connect.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>text versions <?php echo $_GET['c'] ?></title>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

<body onresize="resizer()">
<h1>Versions of

    <select name="page" onchange="location.href='textversions.php?c='+this.value">
        <option value="_">&nbsp;</option>
        <?php
        $allnames = '';
        $sql_a = "SELECT typ, name, deleted FROM resources WHERE deleted!=1 AND typ!='s' ORDER by typ DESC, name ASC";
        $result_a = mysql_query($sql_a);

        if ($result_a == false) {
            echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
        }

        $num_a = mysql_num_rows($result_a);

        for ($i_a = 0; $i_a < $num_a; $i_a++) {
            $row_a = mysql_fetch_array($result_a);

            if ($row_a['deleted'] == 2) $isarchived = '(a)';
            else $isarchived = '';

            $selected = '';
            if ($_GET['c'] == $row_a['name']) $selected = 'selected="selected"';
            echo '<option value="' . $row_a['name'] . '" ' . $selected . '>' . $row_a['typ'] . ': ' . $row_a['name'] . ' ' . $isarchived . '</option>';
            $allnames .= ',' . $row_a['name'] . ',';
        }
        ?>
    </select>


</h1>

<?php

$sql_a = "SELECT * FROM resources_log WHERE name='" . mysql_real_escape_string($_GET['c']) . "' ORDER BY lastmut DESC";
$result_a = mysql_query($sql_a);

if ($result_a == false) {
    echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a = 0; $i_a < $num_a; $i_a++) {
    $row_a = mysql_fetch_array($result_a);
	
	    echo '<br><a style="text-decoration:none" title="localize (where linked in)" href="localize.php?name=' . $row_a['name'] . '">L</a>&nbsp;';
    echo '<a style="text-decoration:none" title="focus in repository" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {opener.parent.i3.location.href=\'focus.php?c=' . $row_a['name'] . '\'}">F</a>&nbsp;';

    echo '<h2 style="display:inline;line-height:42px">' . date('Y-m-d H:i:s', $row_a['lastmut']) . ' ' . $row_a['operator'] . '</h2>';

if($row_a['typ']=='c') {
echo '
<table>
<tr><td><textarea rows="2" id="t' . $i_a . 'a1">' . htmlXspecialchars($row_a['title_1']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a2">' . htmlXspecialchars($row_a['title_2']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a3">' . htmlXspecialchars($row_a['title_3']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a41">' . htmlXspecialchars($row_a['title_4']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a51">' . htmlXspecialchars($row_a['title_5']) . '</textarea></td></tr>
<tr><td><textarea rows="2" id="t' . $i_a . 'a4">' . htmlXspecialchars($row_a['desc_1']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a5">' . htmlXspecialchars($row_a['desc_2']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a6">' . htmlXspecialchars($row_a['desc_3']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a42">' . htmlXspecialchars($row_a['desc_4']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a52">' . htmlXspecialchars($row_a['desc_5']) . '</textarea></td></tr>
<tr><td><textarea rows="11" id="t' . $i_a . 'a7">' . htmlXspecialchars($row_a['content_1']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a8">' . htmlXspecialchars($row_a['content_2']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a9">' . htmlXspecialchars($row_a['content_3']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a43">' . htmlXspecialchars($row_a['content_4']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a53">' . htmlXspecialchars($row_a['content_5']) . '</textarea></td></tr>
</table>
';	
}
else {
echo '
<table>
<tr><td><textarea rows="2" id="t' . $i_a . 'a1">' . ehtml($row_a['title_1']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a2">' . ehtml($row_a['title_2']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a3">' . ehtml($row_a['title_3']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a41">' . ehtml($row_a['title_4']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a51">' . ehtml($row_a['title_5']) . '</textarea></td></tr>
<tr><td><textarea rows="2" id="t' . $i_a . 'a4">' . ehtml($row_a['desc_1']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a5">' . ehtml($row_a['desc_2']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a6">' . ehtml($row_a['desc_3']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a42">' . ehtml($row_a['desc_4']) . '</textarea></td><td><textarea rows="2" id="t' . $i_a . 'a52">' . ehtml($row_a['desc_5']) . '</textarea></td></tr>
<tr><td><textarea rows="11" id="t' . $i_a . 'a7">' . ehtml($row_a['content_1']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a8">' . ehtml($row_a['content_2']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a9">' . ehtml($row_a['content_3']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a43">' . ehtml($row_a['content_4']) . '</textarea></td><td><textarea rows="11" id="t' . $i_a . 'a53">' . ehtml($row_a['content_5']) . '</textarea></td></tr>
</table>
';
}
}
?>

<script>
    function sizer() {
        for (i = 0; i < 1000; i++) {
            if (document.getElementById('t' + i + 'a1')) document.getElementById('t' + i + 'a1').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a2')) document.getElementById('t' + i + 'a2').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a3')) document.getElementById('t' + i + 'a3').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a4')) document.getElementById('t' + i + 'a4').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a5')) document.getElementById('t' + i + 'a5').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a6')) document.getElementById('t' + i + 'a6').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a7')) document.getElementById('t' + i + 'a7').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a8')) document.getElementById('t' + i + 'a8').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a9')) document.getElementById('t' + i + 'a9').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a41')) document.getElementById('t' + i + 'a41').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a51')) document.getElementById('t' + i + 'a51').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a42')) document.getElementById('t' + i + 'a42').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a52')) document.getElementById('t' + i + 'a52').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a43')) document.getElementById('t' + i + 'a43').style.width = document.documentElement.clientWidth / divi - res + 'px';
            if (document.getElementById('t' + i + 'a53')) document.getElementById('t' + i + 'a53').style.width = document.documentElement.clientWidth / divi - res + 'px';
        }
    }

    function resizer() {
        if(window.innerWidth>1000)divi=5;
		else divi=3;
        res = 30;
        if (document.getElementById('t0a1')) if (document.getElementById('t0a1').style.width != document.documentElement.clientWidth / 3 - res) sizer();
    }
    setTimeout("resizer()", 1);
</script>

<script>
    window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
