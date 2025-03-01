<?php 
if(isset($u5phperrorreporting)&&$u5phperrorreporting=='on')error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
require_once('../myfunctions.inc.php');
eikooctes('fv', $_GET['f'], time()+10,'/');
?>
<script type="text/javascript">
parent.history.go(-1);
</script>