<?php
$pidvesa = 'f'; $pidvesascroll = 'f';
require_once('pidvesacookie.inc.php');
setcookie('subi', 'f', time() + 3600 * 24 * 365 * 10, '/');
require_once('connect.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <script src="shortcut.js"></script>
    <?php include('repoctrls.inc.php'); ?>
    <?php require('backendcss.php'); ?></head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php include('pidvesadiv.inc.php'); require_once('pidvesanavi.inc.php'); ?>
<h1 style="display:inline">free&nbsp;Images <a style="font-size:11px;font-weight:100" href="i1.php">show&nbsp;std&nbsp;images</a></h1>
<?php require_once('pidvesasubnavi.inc.php'); include('pidvesadiv.end.inc.php'); ?>
<table cellpadding="1">
    <?php
    $orderby = 'name';
    if ($_GET['pvs_s'] == 'date') $orderby = 'lastmut DESC';

    $sql_a = "SELECT * FROM resources WHERE typ='$pidvesa' AND name LIKE '" . mysql_real_escape_string(str_replace('*', '%', $_GET['f'])) . "' AND deleted=$delstatus ORDER by $orderby";
    $result_a = mysql_query($sql_a);

    if ($result_a == false) {
        echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
    }

    $num_a = mysql_num_rows($result_a);

    for ($i_a = 0; $i_a < $num_a; $i_a++) {
        $row_a = mysql_fetch_array($result_a);

        $pend = '';
        $onechar = false;
        $unique_lans = array_unique(array($lan1na[0], $lan2na[0], $lan3na[0], $lan4na[0], $lan5na[0]));
        if (sizeof($unique_lans) == 5) $onechar = true;
        if ($onechar) {
            $lngpnd_1 = $lan1na[0];
            $lngpnd_2 = $lan2na[0];
            $lngpnd_3 = $lan3na[0];
            $lngpnd_4 = $lan4na[0];
            $lngpnd_5 = $lan5na[0];
        } else {
            $lngpnd_1 = $lan1na;
            $lngpnd_2 = $lan2na;
            $lngpnd_3 = $lan3na;
            $lngpnd_4 = $lan4na;
            $lngpnd_5 = $lan5na;
        }
        if ($lngpnd_2 == '2' || $lngpnd_2 == '20') $lngpnd_2 = '';
        if ($lngpnd_3 == '3' || $lngpnd_3 == '30') $lngpnd_3 = '';
        if ($lngpnd_4 == '4' || $lngpnd_4 == '40') $lngpnd_4 = '';
        if ($lngpnd_5 == '5' || $lngpnd_5 == '50') $lngpnd_5 = '';


        if ($row_a['typ'] == 'a') $pend .= ' <span title="album" style="color:white;background:blue">&nbsp;a&nbsp;</span>';

        if (file_exists('../r/' . $row_a['name'] . '/.htaccess')) $pend .= ' <span title=".htaccess written on ' . date('Y-m-d H:i:s', filemtime('../r/' . $row_a['name'] . '/.htaccess')) . ' (enforces closed user group login on filesystem side)" style="color:white;background:green">.</span>';

        if (str_replace(' ', '', $row_a['ishomepage']) == 1) $pend .= ' <span title="homepage (as defined in S of the PIDVESA-Navigation)" style="background:yellow">&nbsp;&larr;&nbsp;</span>';
        if (str_replace(' ', '', $row_a['logins']) != '') $pend .= ' <span title="closed user group (logins)" style="color:white;background:green">&nbsp;c&nbsp;</span>';
        if (str_replace(' ', '', $row_a['hidden']) == 1) $pend .= ' <span title="hidden (offline)" style="color:white;background:black">&nbsp;H&nbsp;</span>';
        if (str_replace(' ', '', $row_a['hidden']) == -1) $pend .= ' <span title="indexing off (search engines)" style="color:white;background:orange">&nbsp;i&nbsp;</span>';
        if (str_replace(' ', '', $row_a['hidden']) == 2) $pend .= ' <span title="hidden (htaccess forcer only)" style="color:white;background:black">&nbsp;h&nbsp;</span>';

        if (str_replace(' ', '', $row_a['desc_1']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_1 . ' alt-text missing in metadata" style="color:red">' . $lngpnd_1 . '</span>';
        if (str_replace(' ', '', $row_a['desc_2']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_2 . ' alt-text missing in metadata" style="color:red">' . $lngpnd_2 . '</span>';
        if (str_replace(' ', '', $row_a['desc_3']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_3 . ' alt-text missing in metadata" style="color:red">' . $lngpnd_3 . '</span>';
        if (str_replace(' ', '', $row_a['desc_4']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_4 . ' alt-text missing in metadata" style="color:red">' . $lngpnd_4 . '</span>';
        if (str_replace(' ', '', $row_a['desc_5']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_5 . ' alt-text missing in metadata" style="color:red">' . $lngpnd_5 . '</span>';

        if (str_replace(' ', '', $row_a['title_1']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_1 . ' short caption missing in metadata" style="color:red">' . strtoupper($lngpnd_1) . '</span>';
        if (str_replace(' ', '', $row_a['title_2']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_2 . ' short caption missing in metadata" style="color:red">' . strtoupper($lngpnd_2) . '</span>';
        if (str_replace(' ', '', $row_a['title_3']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_3 . ' short caption missing in metadata" style="color:red">' . strtoupper($lngpnd_3) . '</span>';
        if (str_replace(' ', '', $row_a['title_4']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_4 . ' short caption missing in metadata" style="color:red">' . strtoupper($lngpnd_4) . '</span>';
        if (str_replace(' ', '', $row_a['title_5']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_5 . ' short caption missing in metadata" style="color:red">' . strtoupper($lngpnd_5) . '</span>';


        $lspan1 = '<span id="o_' . $row_a['name'] . '">';
        $lspan2 = '</span>';

        $lspan3 = '';
        $lspan2 = '';

        if (!file_exists('../r/' . $row_a['name'])) {
            $lspan3 = '<span title="no file!" style="color:red">';
            $lspan4 = '</span>';
        }

        if ($row_a['name'] != 'logo') $Rrename = 'R';
        else $Rrename = '';


        echo '<tr onmouseover="this.style.background=\'yellow\'" onmouseout="this.style.background=\'#ffffcc\'" id="tr1_' . $row_a['name'] . '" bgcolor="#FFFFCC">

<td style="background:#ffcc66" id="tdL_' . $row_a['name'] . '"><a title="insert in left editor" href="javascript:void(0)" onclick="parent.i1.doteleins(\'' . $row_a['name'] . '\')">&lt;</a>
</td>

<td style="background:#ffcc66" id="tdR_' . $row_a['name'] . '"><a title="insert in right editor" href="javascript:void(0)" onclick="parent.i2.doteleins(\'' . $row_a['name'] . '\')">&lt;</a>
</td>

<td width="99%" style="word-break:break-all" title="' . date('Ymd Hi', $row_a['lastmut']) . ' ' . $row_a['operator'] . ' ' . ehtml(substr($row_a['desc_1'], 0, 150)) . '"><a href="javascript:void(0)" style="color:black;cursor:text" id="a_' . $row_a['name'] . '">' . $row_a['name'] . '</a>' . $pend . '
</td>



<td>
<a title="rename" href="javascript:void(0)" onclick="f1=window.open(\'rename.php?name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">' . $Rrename . '</a>
</td>

<td>
<a title="localize (where linked in)" href="javascript:void(0)" onclick="f1=window.open(\'localize.php?name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">' . $lspan1 . 'L' . $lspan2 . '</a>
</td>

<td>
<a title="copy" href="javascript:void(0)" onclick="f1=window.open(\'copy.php?name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">C</a>
</td>

<td>
<a title="delete or define archive status" href="javascript:void(0)" onclick="f1=window.open(\'delete.php?name=' . md5($row_a['name']) . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">D</a>
</td>

<td>
<a title="metadata (alt-text, captions a.s.o.)" href="javascript:void(0)" onclick="f1=window.open(\'metaf.php?typ=' . $row_a['typ'] . '&name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">M</a>
</td>
';

        echo '<td>
<a title="upload / replace file" href="javascript:void(0)" onclick="f1=window.open(\'upload.php?name=' . $row_a['name'] . '&typ=' . $row_a['typ'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">' . $lspan3 . 'U' . $lspan4 . '</a>
</td></tr>';

        if ($_GET['pvs_p'] == 'on') {
            echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#FFFFCC"><td colspan="9" style="word-break:break-all">';
            include('getfile.inc.php');

            if (file_exists('../r/' . $row_a['name'] . '/' . ($file_1))) echo '<a target="_blank" href="../f.php?f=r/' . $row_a['name'] . '/' . ($file_1) . '?t=' . @filemtime('../r/' . $row_a['name'] . '/' . ($file_1)) . '" title="' . date('Y-m-d H:i', @filemtime('../r/' . $row_a['name'] . '/' . ($file_1))) . ' ../r/' . $row_a['name'] . '/' . ($file_1) . '">';
            echo '<img border="0" width="100" align="left" src="../thumb.php?w=100&t=' . @filemtime('../r/' . $row_a['name'] . '/' . ($file_1)) . '&f=r/' . $row_a['name'] . '/' . ($file_1) . '" />';
            if (file_exists('../r/' . $row_a['name'] . '/' . ($file_1))) echo '</a>';

            echo ehtml(substr(trim($row_a['desc_1']), 0, 80));
            if (strlen(trim($row_a['desc_1'])) > 80) echo '&hellip;';
            echo '<hr></td></tr>';

        } else echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#ffffff"><td colspan="9"></td></tr>';
    }
    ?>
</table>
<?php include('filter.inc.php'); ?>

<?php include('fileandtextversions.inc.php') ?>
<?php include('focus2.php') ?>
</body>
</html>
