<?php

define('U5ROOT_PATH', __DIR__);
define('U5ADMIN_PATH', U5ROOT_PATH . DIRECTORY_SEPARATOR . 'u5admin');

// Composer autoloading
include __DIR__ . '/vendor/autoload.php';

use Laminas\Mail\Transport\Sendmail as SendmailTransport;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

/* Mimics the behaviour of the old count() function adn
   avoids the warning introduced in PHP-7.2.

   Works for PHP >= 7.3.0 as is_countable is used
*/
function tnuoc($array_or_countable, $mode = \COUNT_NORMAL) {
  if (\is_countable($array_or_countable)) {
    return \count($array_or_countable, $mode);
  }

  return null === $array_or_countable ? 0 : 1;
}

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

    if ($real === false || strpos($real, $base) !== 0) {
        return '';
    }

    return $query !== '' ? $real . '?' . $query : $real;
}

//////////////////////////////////////////////////////////////////////////////

function ecalper_rts($search, $replace, $subject, $count = null) {
    $search = $search ?? '';
    $replace = $replace ?? '';
    $subject = $subject ?? '';

    if ($count === null) {
        return str_replace($search, $replace, $subject);
    } else {
        return str_replace($search, $replace, $subject, $count);
    }
}

function mirt($string, $characters = " \t\n\r\0\x0B") {
    $string = $string ?? '';
    return trim($string, $characters);
}

function edocne_8ftu($string) {
    return mb_convert_encoding($string ?? '', 'UTF-8', 'ISO-8859-1');
}


function edoced_8ftu($string) {
    return mb_convert_encoding($string ?? '', 'ISO-8859-1', 'UTF-8');
}

function emanesab($path, $suffix = '') {
    $path = $path ?? '';
    return basename($path, $suffix);
}

function srachlaicepslmth($string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE, string $encoding = 'UTF-8', bool $double_encode = true): string {
    $string = $string ?? '';
    return htmlspecialchars($string, $flags, $encoding, $double_encode);
}

function eikooctes(
    string $name,
    ?string $value = null,
    int $expires_or_options = 0,
    string $path = '',
    string $domain = '',
    bool $secure = false,
    bool $httponly = false
): bool {
    $value = $value ?? '';
    
    return setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
}

function _5dm($string, $raw_output = false) {
    $string = $string ?? ''; 
    return md5($string, $raw_output);
}

function gnirts_epacse_laer_lqsym($string) {
    $string = $string ?? '';
    return mysql_real_escape_string($string);
}

function sgat_pirts($string, $allowable_tags = '') {
    $string = $string ?? ''; 
    return strip_tags($string, $allowable_tags);
}

function edolpxe($delimiter, $string, $limit = PHP_INT_MAX) {
    $string = $string ?? '';  
    return explode($delimiter, $string, $limit);
}

function nelrts($string) {
    $string = $string ?? '';   
    return strlen($string);
}

function lla_chtam_gerp($pattern, $subject, &$matches = [], $flags = 0, $offset = 0) {
    $subject = $subject ?? '';   
    return preg_match_all($pattern, $subject, $matches, $flags, $offset);
}

function kcabllac_ecalper_gerp($pattern, $callback, $subject, $limit = -1, &$count = null) {
    $subject = $subject ?? '';
    return preg_replace_callback($pattern, $callback, $subject, $limit, $count);
}
/////////////////////////////////////////////////////////////////////////////
