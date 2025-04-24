<?php

define('U5ROOT_PATH', __DIR__);
define('U5ADMIN_PATH', U5ROOT_PATH . DIRECTORY_SEPARATOR . 'u5admin');

include __DIR__ . '/vendor/autoload.php';

use Laminas\Mail\Transport\Sendmail as SendmailTransport;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

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

function u5ProhibTravers(string $input): string {
    $parts = explode('?', $input, 2);
    $path = $parts[0];
    $query = $parts[1] ?? '';

    do {
        $last = $path;
        $path = urldecode($path);
    } while ($last !== $path);

    $path = preg_replace('/\s+/', '', $path);

    $dotCount = substr_count($path, '.');
    if ($dotCount > 1) {
        $lastDot = strrpos($path, '.');
        $path = str_replace('.', '', $path);
        $path = substr_replace($path, '.', $lastDot, 0);
    }

    $path = str_replace(['\\', '//'], '/', $path);
    $path = preg_replace('#^/+|/+$#', '', $path);
    $path = preg_replace('#[^a-zA-Z0-9/_\-\!\.]#', '', $path);

    return $query !== '' ? $path . '?' . $query : $path;
}