<?php

require_once('connect.inc.php');
require_once('u5admin/u5idn.inc.php');

use Laminas\Mail\Message;

$mail = new Message();
// Set message encoding; this only affects headers!
$mail->setEncoding('UTF-8');
// Set the message content type for the body
$mail->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
$mail->addFrom('michael@rollis.ch', 'Michael ㌀ Smith');
$mail->addTo('michael@rollis.ch');
$mail->addTo('michael.rolli@unibe.ch');
$mail->addTo('stemind@gmail.com');
$mail->addTo('stefan.minder@bk.admin.ch');
$mail->setSubject("Das isch ä Tescht");
$mail->setBody("Das isch ä ümlüttescht");
$mail->addReplyTo("michael@rollis.ch", "öpper angers");

MailTransport($usesmtp, $mysmtpoptions)->send($mail);
