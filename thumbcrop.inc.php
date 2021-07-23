<?php
$width=$width-2*$_GET['ed'];
$height=$height-2*$_GET['ed'];
$source = imagecrop($source, ['x' => ($_GET['ed']), 'y' => ($_GET['ed']), 'width' => ($width), 'height' => ($height)]);
?>