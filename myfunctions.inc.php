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
