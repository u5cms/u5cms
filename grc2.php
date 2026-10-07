<?php
function u5turnstileReject($message) {
    echo '<script>if(parent && typeof parent.u5turnstileReset==="function")parent.u5turnstileReset();alert(' . json_encode($message) . ');</script>';
    exit;
}

$token = $_POST['cf-turnstile-response'] ?? null;
if (!is_string($token) || trim($token)==='' || strlen($token)>2048) {
    u5turnstileReject('Turnstile verification is missing or expired. Please try again.');
}
if (!isset($u5turnstilesecret) || !is_string($u5turnstilesecret) || trim($u5turnstilesecret)==='') {
    u5turnstileReject('Turnstile is not configured. Please contact the website administrator.');
}

$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
$parameters = array('secret'=>$u5turnstilesecret, 'response'=>$token);
if (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
    $parameters['remoteip'] = $_SERVER['REMOTE_ADDR'];
}
$postdata = http_build_query($parameters);

if (function_exists('curl_init')) {
    $request = curl_init($url);
    curl_setopt_array($request, array(
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>$postdata,
        CURLOPT_HTTPHEADER=>array('Content-Type: application/x-www-form-urlencoded'),
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_CONNECTTIMEOUT=>5,
        CURLOPT_TIMEOUT=>10,
        CURLOPT_SSL_VERIFYPEER=>true,
        CURLOPT_SSL_VERIFYHOST=>2
    ));
    $response = curl_exec($request);
    curl_close($request);
} else {
    $context = stream_context_create(array('http'=>array(
        'method'=>'POST',
        'header'=>"Content-Type: application/x-www-form-urlencoded\r\n",
        'content'=>$postdata,
        'timeout'=>10,
        'follow_location'=>0
    )));
    $response = @file_get_contents($url, false, $context);
}

$responseKeys = is_string($response) ? json_decode($response, true) : null;
if (!is_array($responseKeys) || ($responseKeys['success'] ?? false)!==true) {
    u5turnstileReject('Turnstile verification failed. Please try again.');
}

// The verification token is transport data, not a user-entered form field.
unset($_POST['cf-turnstile-response']);
echo '<script>window.addEventListener("load",function(){if(parent && typeof parent.u5turnstileReset==="function")parent.u5turnstileReset();});</script>';
?>