<?php require_once('connect.inc.php'); ?>
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

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="font-size:9px">
<table>
    <?php
    $totalsizes = 0;
    $i = 0;
	$_GET['name'] = basename($_GET['name']);

    $thislan = '_'.$lan1na;
    include('filespreview.calc.php');

    $thislan = '_'.$lan2na;
    include('filespreview.calc.php');

    $thislan = '_'.$lan3na;
    include('filespreview.calc.php');

    $thislan = '';
    include('filespreview.calc.php');


    if ($i == 0) echo 'none';
    ?>
</table>
<iframe name="totalsizes" width="1" height="1" frameborder="0"
        src="totalsizes.php?name=<?php echo $_GET['name'] ?>&t=<?php echo $totalsizes ?>"></iframe>
</body>

</html>
