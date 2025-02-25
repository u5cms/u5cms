<?php
require_once('u5admin/u5idn.inc.php');

if($zendname==$zendfrom) {
$zendname=edolpxe('@',$zendname);
$zendnamefirstpart=edolpxe('.',$zendname[0]);
$zendnamesecondpart=edolpxe('.',$zendname[1]);
$zendnamefirstpart=strtoupper($zendnamefirstpart[0]);
$zendnamesecondpart=strtoupper($zendnamesecondpart[0]);
$zendname=$zendnamefirstpart.' '.$zendnamesecondpart;
}

use Laminas\Mail\Message;

$mail = new Message();
// Set message encoding; this only affects headers!
$mail->setEncoding('UTF-8');
// Set the message content type for the body
$mail->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
$mail->addFrom(u5toidn($zendfrom), u5toutf8($zendname));
$mail->addTo(u5toidn($zendto));
$mail->setSubject(u5toutf8($zendsubject));
$mail->setBody(u5toutf8($zendmessage));
$mail->addReplyTo(u5toidn($zendfrom), u5toutf8($zendname));

MailTransport($usesmtp, $mysmtpoptions)->send($mail);
