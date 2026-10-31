<?php
require_once __DIR__.'/request.inc.php';
require_once __DIR__.'/codeassets.inc.php';
if(preg_match('/\.php\/+/',($_SERVER['REQUEST_URI'] ?? ''))){header('Location: '.(($_SERVER['HTTPS']??'')==='on'?'https':'http').'://'.($_SERVER['HTTP_HOST'] ?? '').preg_replace('/(\.php)\/+.*/i','$1',parse_url(($_SERVER['REQUEST_URI'] ?? ''),PHP_URL_PATH)).(($_SERVER['QUERY_STRING']??'')?('?'.$_SERVER['QUERY_STRING']):''),true,301);exit;}

define('U5ROOT_PATH', __DIR__);
define('U5ADMIN_PATH', U5ROOT_PATH . DIRECTORY_SEPARATOR . 'u5admin');

// A dependency-provided IDN API must not silently change stored CMS identity
// keys on installations where that capability was absent before autoloading.
define('U5CMS_IDN_AVAILABLE_BEFORE_AUTOLOAD', function_exists('idn_to_ascii') && function_exists('idn_to_utf8'));
include __DIR__ . '/vendor/autoload.php';

use Laminas\Mail\Transport\Sendmail as SendmailTransport;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;


function MailTransport($useSmtp, $options) {
    if ($useSmtp == 'yes') {
        $transport = new SmtpTransport();
        $transport->setOptions($options);
    } else {
        $transport = new SendmailTransport();
    }
    return $transport;
}

function u5ProhibTravers(string $input, string $base): string {
    $parts = explode('?', $input, 2);
    $path = $parts[0];
    $query = $parts[1] ?? '';

    $base = realpath($base);
    $real = realpath($path);

    if ($base === false || $real === false || ($real !== $base && strpos($real, rtrim($base, '/\\') . DIRECTORY_SEPARATOR) !== 0)) {
        return '';
    }

    return $query !== '' ? $real . '?' . $query : $real;
}
