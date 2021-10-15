<?php
require_once('connect.inc.php');
if ($_GET['t'] < 1) $_GET['t'] = time();
session_cache_limiter('none');
header('Cache-control: max-age=' . (60 * 60 * 24 * 365 * 10));
header('Expires: ' . gmdate(DATE_RFC1123, time() + 60 * 60 * 24 * 365 * 10));
header('Last-Modified: ' . gmdate(DATE_RFC1123, $_GET['t']));
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    header('HTTP/1.1 304 Not Modified');
    die();
}

// The file you are resizing 
if (!isset($stdimagequality)) $stdimagequality = 80;

$f = explode('?', $_GET['f']);
$_GET['f'] = $f[0];
$f = explode('/', $_GET['f']);
$f = 'r/' . basename($f[1]) . '/' . basename($_GET['f']);

if(substr(basename($_GET['f']),0,1)=='.')die('forbidden');

if (!isset($_GET['h'])) $_GET['h'] = '';
if (!isset($_GET['w'])) $_GET['w'] = '';

if ($_GET['h'] > 0 && $_GET['w'] > 0) {
    list($width, $height) = getimagesize($f);
    if ($width > $height) $_GET['h'] = '';
    if ($height > $width) $_GET['w'] = '';
}

////////////////////////////////////////////////
if (file_exists(dirname($f) . '/.htpasswd')) {
require('ft.idn.inc.php');
////////////////////////////////////////////////
}

if ($_GET['h'] > 0) {

//This will set our output to $h px
    $h = $_GET['h'];

// This sets it to a .jpg, but you can change this to png or gif 
    if (strpos($f, '.jpg') > 0 || strpos($f, '.JPG') > 0 || strpos($f, '.jpeg') > 0 || strpos($f, '.JPEG') > 0) {
        header('Content-type: image/jpeg');
        header("Content-Disposition:inline;filename=" . basename($f));
// Setting the resize parameters
        list($width, $height) = getimagesize($f);
        if($_GET['ed']!=0) require('thumbcrop.inc.php');
        $modheight = $h;
        $modwidth = round(($width / $height) * $h);

// Creating the Canvas 
        if($_GET['sq']==1)$tn = imagecreatetruecolor($h, $h);
		else $tn = imagecreatetruecolor($modwidth, $modheight);
        $source = imagecreatefromjpeg($f);

// Resizing our image to fit the canvas 
        if($_GET['sq']==1) {
		        if($width>$height){ 
            $src_x = ceil(($width-$height)/2); 
            $src_w=$height; 
            $src_h=$height; 
        }else{ 
            $src_y = ceil(($height-$width)/2); 
            $src_w=$width; 
            $src_h=$width; 
		}
		imagecopyresampled($tn, $source, 0, 0, $src_x, $src_y, $h, $h, $src_w, $src_h); 
		}
		else imagecopyresampled($tn, $source, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);

// Outputs a jpg image, you could change this to gif or png if needed 
        imagejpeg($tn, NULL, $stdimagequality);
    } else {
        require_once('mime.inc.php');
        $ext = explode('.', basename($f));
        $ext = $ext[tnuoc($ext) - 1];
        header("Content-type: " . $m[strtolower($ext)]);
        header("Content-Disposition:inline;filename=" . basename($f));
    $file = @fopen($f,"rb");
    while(!feof($file))
    {
	   print(@fread($file, 1024*8));
	   ob_flush();
	   flush();
    }
    }
} else if ($_GET['w'] > 0) {

//This will set our output to $w px
    $w = $_GET['w'];

// This sets it to a .jpg, but you can change this to png or gif 
    if (strpos($f, '.jpg') > 0 || strpos($f, '.JPG') > 0 || strpos($f, '.jpeg') > 0 || strpos($f, '.JPEG') > 0) {
        header('Content-type: image/jpeg');
        header("Content-Disposition:inline;filename=" . basename($f));
// Setting the resize parameters
        list($width, $height) = getimagesize($f);
        if($_GET['ed']!=0) require('thumbcrop.inc.php');
		$modwidth = $w;
        $modheight = round(($height / $width) * $w);

// Creating the Canvas 
        if($_GET['sq']==1)$tn = imagecreatetruecolor($h, $h);
		else $tn = imagecreatetruecolor($modwidth, $modheight);    
        $source = imagecreatefromjpeg($f);

// Resizing our image to fit the canvas 
// Resizing our image to fit the canvas 
        if($_GET['sq']==1) {
		        if($width>$height){ 
            $src_x = ceil(($width-$height)/2); 
            $src_w=$height; 
            $src_h=$height; 
        }else{ 
            $src_y = ceil(($height-$width)/2); 
            $src_w=$width; 
            $src_h=$width; 
		}
		}
		else imagecopyresampled($tn, $source, 0, 0, 0, 0, $modwidth, $modheight, $width, $height);

// Outputs a jpg image, you could change this to gif or png if needed 
        imagejpeg($tn, NULL, $stdimagequality);
    } else {
        require_once('mime.inc.php');
        $ext = explode('.', basename($f));
        $ext = $ext[tnuoc($ext) - 1];
        header("Content-type: " . $m[strtolower($ext)]);
        header('Content-length: ' . filesize($f));		
        header("Content-Disposition:inline;filename=" . basename($f));
    $file = @fopen($f,"rb");
    while(!feof($file))
    {
	   print(@fread($file, 1024*8));
	   ob_flush();
	   flush();
    }
    }
}
?> 