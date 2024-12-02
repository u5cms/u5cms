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

        if (str_replace(' ', '', $row_a['title_1']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_1 . ' title missing in metadata" style="color:red">' . $lngpnd_1 . '</span>';
        if (str_replace(' ', '', $row_a['title_2']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_2 . ' title missing in metadata" style="color:red">' . $lngpnd_2 . '</span>';
        if (str_replace(' ', '', $row_a['title_3']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_3 . ' title missing in metadata" style="color:red">' . $lngpnd_3 . '</span>';
        if (str_replace(' ', '', $row_a['title_4']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_4 . ' title missing in metadata" style="color:red">' . $lngpnd_4 . '</span>';
        if (str_replace(' ', '', $row_a['title_5']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_5 . ' title missing in metadata" style="color:red">' . $lngpnd_5 . '</span>';

        if (str_replace(' ', '', $row_a['content_1']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_1 . ' content missing" style="color:red">' . strtoupper($lngpnd_1) . '</span>';
        if (str_replace(' ', '', $row_a['content_2']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_2 . ' content missing" style="color:red">' . strtoupper($lngpnd_2) . '</span>';
        if (str_replace(' ', '', $row_a['content_3']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_3 . ' content missing" style="color:red">' . strtoupper($lngpnd_3) . '</span>';
        if (str_replace(' ', '', $row_a['content_4']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_4 . ' content missing" style="color:red">' . strtoupper($lngpnd_4) . '</span>';
        if (str_replace(' ', '', $row_a['content_5']) == '' && $row_a['typ']!='c') $pend .= ' <span title="' . $lngpnd_5 . ' content missing" style="color:red">' . strtoupper($lngpnd_5) . '</span>';


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
