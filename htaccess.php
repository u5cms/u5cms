<?php
require_once __DIR__.'/background-work.inc.php';
if (u5BackgroundWorkBootstrap('provision')) return;
u5BackgroundWorkBegin('provision');
ignore_user_abort(true);
// Keep the existing allowance for the initial catalogue on large installations.
if (PHP_SAPI!=='cli' && function_exists('set_time_limit')) set_time_limit(36000);
if (PHP_SAPI==='cli') {
    require __DIR__.'/config.php';
    require_once __DIR__.'/mysql.php';
    require_once __DIR__.'/myfunctions.inc.php';
    if (!mysql_connect($host,$username,$password) || !mysql_select_db($db) || !mysql_query('SET NAMES latin1')) {
        fwrite(STDERR,"Cannot connect to the configured CMS database.\n");exit(1);
    }
} else {require_once __DIR__.'/connect.inc.php';}
require_once __DIR__.'/provision.inc.php';
try {
    $repair=PHP_SAPI==='cli' && in_array('--rebuild',$argv ?? array(),true);
    if (!u5ProvisionRun(__DIR__,$repair)) {
        if (PHP_SAPI==='cli') {fwrite(STDERR,"Access update still pending. Retry after the active operation completes.\n");exit(2);}
        http_response_code(202);
        $attempt=isset($_GET['u5workretry']) && is_scalar($_GET['u5workretry']) ? (int)$_GET['u5workretry'] : 0;
        if ($attempt>=0 && $attempt<3) u5BackgroundWorkState('wait','?u5workretry='.($attempt+1),10000);
        else u5BackgroundWorkState('pending');
        u5BackgroundWorkFinish();return;
    }
    if (PHP_SAPI==='cli') exit(0);
} catch (Throwable $error) {
    error_log('u5CMS provisioning: '.$error->getMessage());
    http_response_code(503);
    echo 'Access update pending. Please try again.';
    if (PHP_SAPI==='cli') exit(1);
    u5BackgroundWorkFinish();
    return;
}
u5BackgroundWorkState('done',null,0,true);
u5BackgroundWorkFinish();
if (($_SERVER['HTTP_X_U5_BACKGROUND_WORK'] ?? '')==='1') return;
?>
<script>
if(parent)if(parent.parent)if(parent.parent.parent)if(parent.parent.parent.i3)if(parent.parent.parent.i3.location.href.indexOf('i1.php')>1 || parent.parent.parent.i3.location.href.indexOf('f.php')>1 || parent.parent.parent.i3.location.href.indexOf('d.php')>1 || parent.parent.parent.i3.location.href.indexOf('v1.php')>1) {
parent.parent.parent.i3.location.reload();
}
</script>
<?php
echo"<script>if(parent)if(parent.parent)if(parent.parent.document.getElementById('htaccess'))parent.parent.document.getElementById('htaccess').style.display='block'</script>";
echo '<audio id="doneaudio" src="u5admin/provisioningdone.mp3" autoplay />';

?>
<script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>
<script>top.document.title=':'+top.document.title</script>
