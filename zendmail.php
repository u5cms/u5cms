<?php 
require_once('u5admin/u5idn.inc.php');

if($usesmtp=='yes') $transport = new Zend_Mail_Transport_Smtp($smtpservername, $mysmtpconfigdata);
$mail = new Zend_Mail('UTF-8');
$mail->setFrom(u5toidn($zendfrom), u5toutf8($zendname));
$mail->addTo(u5toidn($zendto));
$mail->setSubject(u5toutf8($zendsubject));
$mail->setReplyTo(u5toidn($zendfrom));

$mail->setBodyText(u5toutf8($zendmessage));

if($usesmtp=='yes') $mail->send($transport);
else $mail->send();
?>