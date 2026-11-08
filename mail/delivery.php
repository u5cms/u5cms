<?php
require_once __DIR__.'/addresses.php';

function u5mail_phpmailer($options, $useSmtp, $requiresUtf8 = true) {
    require_once __DIR__.'/phpmailer/src/Exception.php';
    require_once __DIR__.'/phpmailer/src/OAuthTokenProvider.php';
    require_once __DIR__.'/phpmailer/src/PHPMailer.php';
    require_once __DIR__.'/phpmailer/src/SMTP.php';
    $mail = new U5MailVendor\PHPMailer\PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'quoted-printable';
    if ($useSmtp !== 'yes') {
        if ($requiresUtf8) throw new RuntimeException('International email local-parts require an SMTPUTF8-capable SMTP server. Configure $usesmtp="yes".');
        $mail->isMail();
        return $mail;
    }
    if (!is_object($options) || !method_exists($options, 'getConnectionConfig')) throw new RuntimeException('SMTP options are missing.');
    $mail->isSMTP();
    $mail->Host = $options->getHost();
    $mail->Port = $options->getPort();
    $mail->Helo = $options->getName();
    $config = $options->getConnectionConfig();
    $type = strtolower($options->getConnectionClass());
    $types = array('smtp'=>'', 'login'=>'LOGIN', 'plain'=>'PLAIN', 'crammd5'=>'CRAM-MD5');
    if (!array_key_exists($type, $types)) throw new RuntimeException('This SMTP authentication adapter has not been configured for SMTPUTF8.');
    $mail->SMTPAuth = $type !== 'smtp';
    $mail->AuthType = $types[$type];
    $mail->Username = isset($config['username']) ? $config['username'] : '';
    $mail->Password = isset($config['password']) ? $config['password'] : '';
    $mail->SMTPAutoTLS = false; // Respect the configured transport, including test/local relays.
    if (!empty($config['ssl'])) {
        if (!in_array($config['ssl'], array('ssl', 'tls'), true)) throw new RuntimeException('Unknown SMTP encryption setting.');
        $mail->SMTPSecure = $config['ssl'];
    }
    if (!empty($config['stream_context_options'])) $mail->SMTPOptions = $config['stream_context_options'];
    return $mail;
}

/* All address fields entering here are UTF-8 transport addresses, not CMS keys. */
function u5mail_send(array $fields, $html = false, array $attachments = array()) {
    global $usesmtp, $mysmtpoptions;
    $from = u5mail_address_decoded($fields['from']);
    $to = isset($fields['to']) ? $fields['to'] : array();
    $cc = isset($fields['cc']) ? $fields['cc'] : array();
    $bcc = isset($fields['bcc']) ? $fields['bcc'] : array();
    foreach (array_merge($to, $cc, $bcc) as $address) if (u5mail_address_decoded($address) !== $address) throw new InvalidArgumentException('Unnormalised delivery address.');
    if (!$to && !$cc && !$bcc) throw new InvalidArgumentException('Email has no recipients.');
    $name = isset($fields['name']) ? $fields['name'] : '';
    foreach (array($name, $fields['subject']) as $header) if (preg_match('/[\r\n\x00]/', $header)) throw new InvalidArgumentException('Control characters in email header.');
    $utf8 = u5mail_requires_utf8(array_merge(array($from), $to, $cc, $bcc));
    // The fallback also makes mail work on old distributions lacking intl, without
    // introducing global constants that could change legacy CMS identity keys.
    if ($utf8 || !defined('IDNA_ERROR_EMPTY_LABEL')) {
        $mail = u5mail_phpmailer($mysmtpoptions, $usesmtp, $utf8);
        $mail->setFrom($from, $name);
        $mail->addReplyTo($from, $name);
        foreach ($to as $address) $mail->addAddress($address);
        foreach ($cc as $address) $mail->addCC($address);
        foreach ($bcc as $address) $mail->addBCC($address);
        $mail->Subject = $fields['subject'];
        $mail->Body = $fields['body'];
        if ($html) { $mail->isHTML(true); $mail->AltBody = strip_tags($fields['body']); }
        foreach ($attachments as $file) $mail->addAttachment($file);
        return $mail->send();
    }
    $mail = new Laminas\Mail\Message();
    $mail->setEncoding('UTF-8');
    $mail->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
    $mail->addFrom($from, $name);
    $mail->addReplyTo($from, $name);
    $mail->addTo($to);
    if ($cc) $mail->addCc($cc);
    if ($bcc) $mail->addBcc($bcc);
    $mail->setSubject($fields['subject']);
    if ($html) {
        $text = new Laminas\Mime\Part(strip_tags($fields['body']));
        $text->type = Laminas\Mime\Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Laminas\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $part = new Laminas\Mime\Part($fields['body']);
        $part->type = Laminas\Mime\Mime::TYPE_HTML;
        $part->charset = 'utf-8';
        $part->encoding = Laminas\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
        $body = new Laminas\Mime\Message();
        $body->setParts(array($text, $part));
        $mail->setBody($body);
        $mail->getHeaders()->get('Content-Type')->setType('multipart/alternative');
    } else $mail->setBody($fields['body']);
    if ($attachments) throw new RuntimeException('Use the existing native MIME backup path for ordinary addresses.');
    MailTransport($usesmtp, $mysmtpoptions)->send($mail);
    return true;
}

/* Native mail duplicates retain their old route for ordinary ASCII addresses. */
function u5mail_native($to, $subject, $body, $headers = '') {
    global $mymail;
    try {
        $addresses = u5mail_addresses($to);
        if (!u5mail_requires_utf8($addresses)) return mail(implode(',', $addresses), $subject, $body, $headers);
        // The main zendmail invocation performs the supported UTF-8 SMTP send;
        // do not duplicate it on the unverified native PHP mail channel.
        return false;
    } catch (Throwable $e) { error_log('u5CMS native mail: '.$e->getMessage()); return false; }
}
