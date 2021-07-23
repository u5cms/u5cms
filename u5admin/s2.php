<?php
$pidvesa = 's'; $pidvesascroll = 's2';
$donotshowtogglearchive = 1;
require_once('pidvesacookie.inc.php');
setcookie('subs', 's2', time() + 3600 * 24 * 365 * 10, '/');
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
<h1 style="display:inline">Save&nbsp;history <a style="font-size:11px;font-weight:100" href="s1.php">special&nbsp;functions</a></h1>
<?php require_once('pidvesasubnavi.inc.php'); include('pidvesadiv.end.inc.php'); ?>
<script>
document.getElementById('sortascdesc').style.visibility='hidden';
document.getElementById('newbutton').style.visibility='hidden';
</script>
<table cellpadding="1">
    <?php
    $orderby = 'name';
    if ($_GET['pvs_s'] == 'date') $orderby = 'lastmut DESC';

    $sql_a = "SELECT * FROM resources WHERE typ!='s' AND name!='-' AND name LIKE '" . mysql_real_escape_string(str_replace('*', '%', $_GET['f'])) . "' AND deleted!=1 ORDER BY lastmut DESC";
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
        if ($row_a['typ'] == 'a') $pend .= ' <span title="album" style="color:white;background:blue">&nbsp;a&nbsp;</span>';
		if (file_exists('../r/' . $row_a['name'] . '/.htaccess')) $pend .= ' <span title=".htaccess written on ' . date('Y-m-d H:i:s', filemtime('../r/' . $row_a['name'] . '/.htaccess')) . ' (enforces closed user group login on filesystem side)" style="color:white;background:green">.</span>';
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

        if(trim($row_a['typ'])!='')require('s2_'.str_replace('a','i',$row_a['typ']).'.php');
    }
    ?>
</table>
<?php include('filter.inc.php'); ?>

<?php include('fileandtextversions.inc.php') ?>
<?php include('focus2.php') ?>
</body>
</html>
