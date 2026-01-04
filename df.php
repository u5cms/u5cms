<?php
require_once('connect.inc.php');
require_once('mime.inc.php');

$path='fileversions/useruploads/';
if ($handle = @opendir($path)) {
while (false !== ($f = readdir($handle))) {
if (strlen(str_replace('.','',$f))>0) $allfiles.=$f.',';
}
}

$allfiles=explode(',',$allfiles);

for ($i=0;$i<tnuoc($allfiles)-1;$i++) {
if ($sticksessiontoip=='yes')
$serverremoteaddr=$_SERVER['REMOTE_ADDR'].$_COOKIE['u'].$_COOKIE['p'];
else
$serverremoteaddr=$_COOKIE['u'].$_COOKIE['p'];

$fhash1=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$allfiles[$i].date('Ymd')).'_URL_NON_TRANSFERABLE_AND_TIME_LIMITED';
$fhash2=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$allfiles[$i].date('Ymd',time()-12*60*60)).'_URL_NON_TRANSFERABLE_AND_TIME_LIMITED';

if ($fhash1==$_GET['f'] || $fhash2==$_GET['f']) $f=$allfiles[$i];
}

if (!isset($f) || $f=='' || !is_file('fileversions/useruploads/'.$f)) {
header("HTTP/1.1 404 Not Found");
die();
}

$f='fileversions/useruploads/'.$f;

session_cache_limiter('none');
header('Cache-control: max-age='.(60*60*24*365*10));
header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365*10));
header('Last-Modified: '.gmdate(DATE_RFC1123,filemtime($f)));

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
header('HTTP/1.1 304 Not Modified');
die();
}

$ext=explode('.',$f);
$ext=$ext[tnuoc($ext)-1];

if (strpos($f,'.jpg')>0 || strpos($f,'.JPG')>0 || strpos($f,'.jpeg')>0 || strpos($f,'.JPEG')>0) {

if (!isset($stdimagequality)) $stdimagequality=80;
if (!isset($_GET['h'])) $_GET['h']='';
if (!isset($_GET['w'])) $_GET['w']='';

if ($_GET['h']>0 && $_GET['w']>0) {
list($width,$height)=getimagesize($f);
if ($width>$height) $_GET['h']='';
if ($height>$width) $_GET['w']='';
}

if ($_GET['h']>0) {

$h=$_GET['h'];
header('Content-type: image/jpeg');
if ($revealorigfilenames=='yes')
header("Content-Disposition:inline;filename=".basename($f));
else
header("Content-Disposition:inline;filename=".explode('_',basename($_GET['f']),2)[0].'.'.$ext);

list($width,$height)=getimagesize($f);
$modheight=$h;
$modwidth=round(($width/$height)*$h);

$tn=imagecreatetruecolor($modwidth,$modheight);
$source=imagecreatefromjpeg($f);

imagecopyresampled($tn,$source,0,0,0,0,$modwidth,$modheight,$width,$height);
imagejpeg($tn,NULL,$stdimagequality);
}
else if ($_GET['w']>0) {

$w=$_GET['w'];
header('Content-type: image/jpeg');
if ($revealorigfilenames=='yes')
header("Content-Disposition:inline;filename=".basename($f));
else
header("Content-Disposition:inline;filename=".explode('_',basename($_GET['f']),2)[0].'.'.$ext);

list($width,$height)=getimagesize($f);
$modwidth=$w;
$modheight=round(($height/$width)*$w);

$tn=imagecreatetruecolor($modwidth,$modheight);
$source=imagecreatefromjpeg($f);

imagecopyresampled($tn,$source,0,0,0,0,$modwidth,$modheight,$width,$height);
imagejpeg($tn,NULL,$stdimagequality);
}
else {

header("Content-type: ".$m[strtolower($ext)]);
if ($revealorigfilenames=='yes')
header("Content-Disposition:inline;filename=".basename($f));
else
header("Content-Disposition:inline;filename=".explode('_',basename($_GET['f']),2)[0].'.'.$ext);

$file=@fopen($f,"rb");
while (!feof($file)) {
print(@fread($file,1024*8));
ob_flush();
flush();
}
}
}
else {

header("Content-type: ".$m[strtolower($ext)]);
if ($revealorigfilenames=='yes')
header("Content-Disposition:inline;filename=".basename($f));
else
header("Content-Disposition:inline;filename=".explode('_',basename($_GET['f']),2)[0].'.'.$ext);

$file=@fopen($f,"rb");
while (!feof($file)) {
print(@fread($file,1024*8));
ob_flush();
flush();
}
}
?>