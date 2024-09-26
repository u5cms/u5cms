<?php ignore_user_abort(true);set_time_limit(3600); require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

<body>
<table style="margin-bottom:-30px">
    <?php
    $_GET['name'] = basename($_GET['name']);
    $totalsizes = 0;
    $i = 0;

    $thislan = '_'.$lan1na;
    include('files.calc.php');

    $thislan = '_'.$lan2na;
    include('files.calc.php');

    $thislan = '_'.$lan3na;
    include('files.calc.php');

    $thislan = '_'.$lan4na;
    include('files.calc.php');

    $thislan = '_'.$lan5na;
    include('files.calc.php');

    $thislan = '';
    include('files.calc.php');


    if ($i == 0) echo 'none';
    ?>
</table>
<iframe name="totalsizes" width="1" height="1" frameborder="0"
        src="totalsizes.oneparent.php?name=<?php echo $_GET['name'] ?>&t=<?php echo $totalsizes ?>"></iframe>
</body>
</html>