<?php

require_once('u5admin/u5idn.inc.php');

use Laminas\Mail\Message;

$mail = new Message();
$mail->setEncoding('UTF-8');
$mail->addFrom(u5toidn($zendfrom), u5toutf8($zendname));
$mail->addTo(u5toidn($zendto));
$mail->setSubject(u5toutf8($zendsubject));
$mail->setBody(u5toutf8($zendmessage));
$mail->addReplyTo(u5toidn($zendfrom), u5toutf8($zendname));

MailTransport($usesmtp, $mysmtpoptions)->send($mail);
