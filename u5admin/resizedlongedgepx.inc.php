<?php
if (!isset($resizedlongedgepx)) $resizedlongedgepx = 2000;
list($width, $height) = getimagesize($path.'/'.$file); 

if($width>$height) {
$modwidth = $resizedlongedgepx;
$modheight = round(($height/$width)*$resizedlongedgepx);
}
else {
$modheight = $resizedlongedgepx;
$modwidth = round(($width/$height)*$resizedlongedgepx);
}
?>