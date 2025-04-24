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
    $allowedRoots = ['r'];

    $input = urldecode($input);
    $parts = explode('?', $input, 2);
    $path = $parts[0];
    $query = $parts[1] ?? '';

    $path = preg_replace('/[^A-Za-z0-9.\-_\/!\\\\]/', '', $path);
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#/+#', '/', $path);

    $segments = [];
    foreach (explode('/', $path) as $seg) {
        if ($seg === '' || $seg === '.') continue;
        if ($seg === '..') {
            if ($segments) array_pop($segments);
            else return '.';
        } else {
            $segments[] = $seg;
        }
    }

    if (!$segments) return '.';
    if (!in_array($segments[0], $allowedRoots, true)) return '.';

    $path = implode('/', $segments);
    $t = null;
    if (preg_match('/^t=(\d+)$/', $query, $m)) $t = $m[1];

    return $t !== null ? "$path?t=$t" : $path;
}