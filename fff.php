<?php
set_time_limit(3600);
require_once('connect.inc.php');
$t=isset($_GET['t']) && is_scalar($_GET['t']) ? (int)$_GET['t'] : 0;
if(!isset($_GET['t'])){
$t=explode('?t=',$_SERVER['QUERY_STRING']);
$t=explode('&',$t[1]);
$t=$t[0];
}

$filebase = 'fileversions/_dbbackup';
$f = explode('?', $_GET['f']);
$_GET['f'] = $f[0];
$f = explode('/', $_GET['f']);
$f = $filebase . '/' . basename($_GET['f']);
$f=u5ProhibTravers($f, $filebase);

if(substr(basename($_GET['f']),0,1)=='.')die('forbidden');

if($usesessioninsteadofbasicauth=='no') {
    if ($t != '' && $_GET['s'] != '') $f .= '?t=' . $t . '&s=' . $_GET['s'];
    else if ($t != '') $f .= '?t=' . $t;
    header("Location: " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $f));
	exit;
}

require_once('filecache.inc.php');
////////////////////////////////////////////////
if (file_exists('fileversions/.htpasswd')) {
require('ffft.idn.inc.php');
////////////////////////////////////////////////

    if (!file_exists($f)) die('File ' . $f . ' does not exist.');
    u5FileCacheHeaders($f,true);
    require_once('mime.inc.php');
    $ext = explode('.', basename($f));
    $ext = $ext[tnuoc($ext) - 1];
    header("Content-type: " . $m[strtolower($ext)]);
	  header('Content-length: ' . filesize($f));
    header("Content-Disposition:inline;filename=" . basename($f));
    $file = @fopen($f,"rb");
    if ($file===false) { http_response_code(404); exit; }
    while(!feof($file))
    {
	   print(@fread($file, 1024*8));
	   ob_flush();
	   flush();
    }
    fclose($file);
} else {
    $s = $_GET['s'] ?? '';
    if ($t != '' && $s != '') $f .= '?t=' . $t . '&s=' . $s;
    else if ($t != '') $f .= '?t=' . $t;
    header("Location: " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $f));
}
?>
