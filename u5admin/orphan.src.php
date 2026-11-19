<?php
require_once('connect.inc.php');
header('Content-Type: application/javascript; charset=windows-1252');
header('Cache-Control: private, no-cache');
$orphanData=@file_get_contents('../fileversions/orphan.js.php');
if ($orphanData===false) { http_response_code(204); exit; }
$orphanEtag='"'.sha1($orphanData).'"';
header('ETag: '.$orphanEtag);
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'])===$orphanEtag) {
http_response_code(304);
exit;
}
echo $orphanData;
?>