<?php
if(!isset($_POST['g-recaptcha-response']))die('<script>location.href=document.referrer;</script>');
$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($u5googlerecaptchasecret) .  '&response=' . urlencode($_POST['g-recaptcha-response']);
$response = file_get_contents($url);
$responseKeys = json_decode($response,true);
if(!$responseKeys["success"])die('<script>location.href=document.referrer;</script>');
?>