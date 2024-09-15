<?php
require('connect.inc.php');
if($oneverysaveupdateindexandhtaccesscostly=='yes')include('htaccessandindexer.php');
else echo '<iframe src="htaccess.php?once=once"></iframe';
?>
<iframe name="lastsave0" src="lastsave.php"></iframe>