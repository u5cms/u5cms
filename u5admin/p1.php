<?php
$pidvesa = 'p'; $pidvesascroll = 'p1';
require_once('pidvesacookie.inc.php');
setcookie('subp', 'p1', time() + 3600 * 24 * 365 * 10, '/');
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
<h1 style="display:inline">std&nbsp;Pages <a style="font-size:11px;font-weight:100" href="p2.php">show&nbsp;intranet</a></h1>
<?php require_once('pidvesasubnavi.inc.php'); include('pidvesadiv.end.inc.php'); ?>
<table cellpadding="1">
    <?php
    $orderby = 'name';
    if ($_GET['pvs_s'] == 'date') $orderby = 'lastmut DESC';

    $sql_a = "SELECT * FROM resources WHERE name NOT LIKE '!%' AND typ='$pidvesa' AND name LIKE '" . mysql_real_escape_string(str_replace('*', '%', $_GET['f'])) . "' AND deleted=$delstatus ORDER by $orderby";
    $result_a = mysql_query($sql_a);

    if ($result_a == false) {
        echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
    }

    $num_a = mysql_num_rows($result_a);

    for ($i_a = 0; $i_a < $num_a; $i_a++) {
        $row_a = mysql_fetch_array($result_a);

        $pend = '';
        $onechar = false;
        if ($lan1na[0] != $lan2na[0] && $lan1na[0] != $lan3na[0] && $lan2na[0] != $lan3na[0]) $onechar = true;
        if ($onechar) {
            $lngpnd_d = $lan1na[0];
            $lngpnd_e = $lan2na[0];
            $lngpnd_f = $lan3na[0];
        } else {
            $lngpnd_d = $lan1na;
            $lngpnd_e = $lan2na;
            $lngpnd_f = $lan3na;
        }
        if ($lngpnd_e == '2' || $lngpnd_e == '20') $lngpnd_e = '';
        if ($lngpnd_f == '3' || $lngpnd_f == '30') $lngpnd_f = '';

        if (str_replace(' ', '', $row_a['ishomepage']) == 1) $pend .= ' <span title="homepage (as defined in S of the PIDVESA-Navigation)" style="background:yellow">&nbsp;&larr;&nbsp;</span>';
        if (str_replace(' ', '', $row_a['logins']) != '') $pend .= ' <span title="closed user group (logins)" style="color:white;background:green">&nbsp;c&nbsp;</span>';
        if (str_replace(' ', '', $row_a['hidden']) == 1) $pend .= ' <span title="hidden (offline)" style="color:white;background:black">&nbsp;H&nbsp;</span>';
        if (str_replace(' ', '', $row_a['hidden']) == -1) $pend .= ' <span title="indexing off (search engines)" style="color:white;background:orange">&nbsp;i&nbsp;</span>';
        if (str_replace(' ', '', $row_a['hidden']) == 2) $pend .= ' <span title="hidden (htaccess forcer only)" style="color:white;background:black">&nbsp;h&nbsp;</span>';

        if (str_replace(' ', '', $row_a['title_d']) == '') $pend .= ' <span title="' . $lngpnd_d . ' title missing in metadata" style="color:red">' . $lngpnd_d . '</span>';
        if (str_replace(' ', '', $row_a['title_e']) == '') $pend .= ' <span title="' . $lngpnd_e . ' title missing in metadata" style="color:red">' . $lngpnd_e . '</span>';
        if (str_replace(' ', '', $row_a['title_f']) == '') $pend .= ' <span title="' . $lngpnd_f . ' title missing in metadata" style="color:red">' . $lngpnd_f . '</span>';

        if (str_replace(' ', '', $row_a['content_d']) == '') $pend .= ' <span title="' . $lngpnd_d . ' content missing" style="color:red">' . strtoupper($lngpnd_d) . '</span>';
        if (str_replace(' ', '', $row_a['content_e']) == '') $pend .= ' <span title="' . $lngpnd_e . ' content missing" style="color:red">' . strtoupper($lngpnd_e) . '</span>';
        if (str_replace(' ', '', $row_a['content_f']) == '') $pend .= ' <span title="' . $lngpnd_f . ' content missing" style="color:red">' . strtoupper($lngpnd_f) . '</span>';


        $lspan1 = '<span id="o_' . $row_a['name'] . '">';
        $lspan2 = '</span>';

        if ($row_a['name'] != 'navigation') $Rrename = 'R';
        else $Rrename = '';
        echo '<tr onmouseover="this.style.background=\'yellow\'" onmouseout="this.style.background=\'#ffffcc\'" id="tr1_' . $row_a['name'] . '" bgcolor="#FFFFCC">

<td style="background:#ffcc66" id="tdL_' . $row_a['name'] . '"><a title="insert as link in the left editor" href="javascript:void(0)" onclick="parent.i1.doteleins(\'linktext:' . $row_a['name'] . '\')">&lt;</a>
</td>

<td style="background:#ffcc66" id="tdR_' . $row_a['name'] . '"><a title="insert as link in the right editor" href="javascript:void(0)" onclick="parent.i2.doteleins(\'linktext:' . $row_a['name'] . '\')">&lt;</a>
</td>

<td width="99%" style="word-break:break-all" title="Open right (+Alt=left). ' . date('Ymd Hi', $row_a['lastmut']) . ' ' . $row_a['operator'] . ' ' . ehtml(substr($row_a['content_d'], 0, 150)) . '"><a id="a_' . $row_a['name'] . '" href="javascript:void(0)" onclick="if(event.altKey)parent.i1.lgotopage(\'' . $row_a['name'] . '\');else parent.i2.gotopage(\'' . $row_a['name'] . '\')">' . $row_a['name'] . '</a>' . $pend . '
</td>

<td>
<a title="view" href="javascript:void(0)" onclick="window.open(\'../?c=' . $row_a['name'] . '&l=\'+parent.i2.lansel)">V</a>
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
</tr>';

        if ($_GET['pvs_p'] == 'on') {
            echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#FFFFCC"><td colspan="8" style="word-break:break-all">';
            echo ehtml(substr($row_a['content_d'], 0, 150));
            if (strlen(trim($row_a['content_d'])) > 150) echo '&hellip;';
            echo '<hr></td></tr>';
        } else echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#ffffff"><td colspan="8"></td></tr>';
    }
    ?>
</table>
<?php include('filter.inc.php'); ?>

<?php include('fileandtextversions.inc.php') ?>
<?php include('focus2.php') ?>
</body>
</html>
