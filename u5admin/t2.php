<?php
$token=$_POST['u5scrtytknfrfrms'] ?? null;
$secret=$username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'];
if (!is_string($token) || (!hash_equals(sha1($secret.date('Ymd')),$token) && !hash_equals(sha1($secret.date('Ymd',time()-60*60*24)),$token))) {
    http_response_code(403);
    $message='The transaction could not be executed because the form token is invalid or expired. Your editor contents have been kept. Copy valuable input before reloading the form.';
    $async=(isset($_POST['u5saveid']) && is_string($_POST['u5saveid']) && $_POST['u5saveid']!=='') ||
        (isset($_POST['u5resourceid']) && is_string($_POST['u5resourceid']) && $_POST['u5resourceid']!=='');
    if ($async) die(htmlXspecialchars($message));
    die('<script>alert("'.$message.'");history.go(-1)</script>');
}
?>