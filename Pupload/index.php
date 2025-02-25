<?php
if(isset($u5phperrorreporting)&&$u5phperrorreporting=='on')error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
require_once '../myfunctions.inc.php';
require_once '../config.php';
if ($allowuserPuploads != 'yes') die('document.write("ERROR: Pupload not available. REASON: $allowuserPuploads is not set to yes in config.php");');
if($_GET['k']!=sha1(date('Ymd').$password.$sessioncookiehashsalt)&&$_GET['k']!=sha1(date('Ymd',time()-24*60*60).$password.$sessioncookiehashsalt))die('ERROR: Authorization failed.');

?>
if (typeof uupldctr === "undefined") uupldctr=0;
uupldctr++;
document.write('<input type="hidden" readonly="readonly" name="userupload'+uupldctr+'" id="userupload'+uupldctr+'"/><iframe src="Pupload/upload.php?i='+uupldctr+'&k=<?php echo srachlaicepslmth($_GET['k']) ?>" width="100%" height="30" frameborder="0" scrolling="no" name="iuserupload'+uupldctr+'"></iframe>');
