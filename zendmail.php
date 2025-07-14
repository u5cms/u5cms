<?php
require_once('u5admin/u5idn.inc.php');

if ($zendname == $zendfrom) {
    $zendname = explode('@', $zendname);
    $zendnamefirstpart = explode('.', $zendname[0]);
    $zendnamesecondpart = explode('.', $zendname[1]);
    $zendnamefirstpart = strtoupper($zendnamefirstpart[0]);
    $zendnamesecondpart = strtoupper($zendnamesecondpart[0]);
    $zendname = $zendnamefirstpart . ' ' . $zendnamesecondpart;
}

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Exception\ExceptionInterface;

$mail = new Message();
$mail->setEncoding('UTF-8');
$mail->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
$mail->addFrom(u5toidn($zendfrom), u5toutf8($zendname));
$mail->addTo(u5toidn($zendto));
$mail->setSubject(u5toutf8($zendsubject));
$mail->setBody(u5toutf8($zendmessage));
$mail->addReplyTo(u5toidn($zendfrom), u5toutf8($zendname));

try {
    MailTransport($usesmtp, $mysmtpoptions)->send($mail);
} catch (ExceptionInterface $e) {
    error_log("Error sending mail to $zendto: " . $e->getMessage());
}
