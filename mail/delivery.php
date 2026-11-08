<?php
require_once __DIR__.'/addresses.php';
require_once __DIR__.'/accounts.php';

function u5mail_phpmailer($options, $useSmtp, $requiresUtf8 = true) {
    require_once __DIR__.'/phpmailer/src/Exception.php';
    require_once __DIR__.'/phpmailer/src/OAuthTokenProvider.php';
    require_once __DIR__.'/phpmailer/src/PHPMailer.php';
    require_once __DIR__.'/phpmailer/src/SMTP.php';
    require_once __DIR__.'/smtp.php';
    require_once __DIR__.'/message.php';
    U5MailVendor\PHPMailer\PHPMailer::$validator = function ($address) {
        try { return u5mail_address_decoded($address) === $address; }
        catch (InvalidArgumentException $e) { return false; }
    };
    $mail = new U5MailMessage(true);
    $mail->setSMTPInstance(new U5MailSmtp());
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
    $mail->Helo = (string) $options->getName();
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
    if (!empty($config['novalidatecert'])) $mail->SMTPOptions = array('ssl'=>array('verify_peer'=>false, 'verify_peer_name'=>false));
    if (!empty($config['stream_context_options'])) $mail->SMTPOptions = $config['stream_context_options'];
    return $mail;
}

/* All address fields entering here are UTF-8 transport addresses, not CMS keys. */
function u5mail_send(array $fields, $html = false, array $attachments = array(), &$message = null) {
    $message = null;
    global $usesmtp, $mysmtpoptions;
    $from = u5mail_address_decoded($fields['from']);
    $to = isset($fields['to']) ? $fields['to'] : array();
    $cc = isset($fields['cc']) ? $fields['cc'] : array();
    $bcc = isset($fields['bcc']) ? $fields['bcc'] : array();
    $toNames = isset($fields['to_names']) ? $fields['to_names'] : array();
    foreach (array_merge($to, $cc, $bcc) as $address) if (u5mail_address_decoded($address) !== $address) throw new InvalidArgumentException('Unnormalised delivery address.');
    if (!$to && !$cc && !$bcc) throw new InvalidArgumentException('Email has no recipients.');
    $name = isset($fields['name']) ? $fields['name'] : '';
    foreach (array_merge(array($name, $fields['subject']), array_values($toNames)) as $header) if (preg_match('/[\r\n\x00]/', $header)) throw new InvalidArgumentException('Control characters in email header.');
    $utf8 = u5mail_requires_utf8(array_merge(array($from), $to, $cc, $bcc));
    // The fallback also makes mail work on old distributions lacking intl, without
    // introducing global constants that could change legacy CMS identity keys.
    if ($utf8 || !defined('IDNA_ERROR_EMPTY_LABEL')) {
        $mail = u5mail_phpmailer($mysmtpoptions, $usesmtp, $utf8);
        $message = $mail;
        $mail->setFrom($from, $name);
        $mail->addReplyTo($from, $name);
        foreach ($to as $address) $mail->addAddress($address, isset($toNames[$address]) ? $toNames[$address] : '');
        foreach ($cc as $address) $mail->addCC($address);
        foreach ($bcc as $address) $mail->addBCC($address);
        $mail->Subject = $fields['subject'];
        $mail->Body = $fields['body'];
        if ($html) { $mail->isHTML(true); $mail->AltBody = strip_tags($fields['body']); }
        foreach ($attachments as $file) $mail->addAttachment($file);
        return $mail->send();
    }
    $mail = new Laminas\Mail\Message();
    $message = $mail;
    $mail->setEncoding('UTF-8');
    $mail->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
    $mail->addFrom($from, $name);
    $mail->addReplyTo($from, $name);
    if ($toNames) {
        require_once __DIR__.'/header.php';
        $mail->getHeaders()->addHeader(new U5MailToHeader());
    }
    foreach ($to as $address) $mail->addTo($address, isset($toNames[$address]) ? $toNames[$address] : null);
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

/* Native callers retain their original route for ordinary ASCII addresses. */
function u5mail_native($to, $subject, $body, $headers = '') {
    global $mymail;
    try {
        $addresses = u5mail_addresses($to);
        if (!u5mail_requires_utf8($addresses)) return mail(implode(',', $addresses), $subject, $body, $headers);
        if ($headers !== '') throw new RuntimeException('International native email with custom headers needs an explicit MIME sender.');
        return u5mail_send(array('from'=>u5mail_address($mymail), 'to'=>$addresses,
            'name'=>'', 'subject'=>u5mail_utf8($subject), 'body'=>u5mail_utf8($body)));
    } catch (Throwable $e) { error_log('u5CMS native mail: '.$e->getMessage()); return false; }
}

function u5mail_duplicate($to, $subject, $body) {
    try {
        // zendmail sends the primary notification with negotiated SMTPUTF8.
        // A second unverified PHP-mail route cannot safely duplicate EAI mail.
        if (u5mail_requires_utf8(u5mail_addresses($to))) return false;
        $sent = u5mail_native($to, $subject, $body);
        if ($sent) $GLOBALS['u5mail_duplicate_receipts'][u5mail_duplicate_key($to, $subject, $body)] = true;
        return $sent;
    } catch (Throwable $e) { error_log('u5CMS duplicate mail: '.$e->getMessage()); return false; }
}

function u5mail_duplicate_key($to, $subject, $body) {
    return hash('sha256', serialize(array(u5mail_addresses($to), (string) $subject, (string) $body)));
}

function u5mail_duplicate_delivered($to, $subject, $body) {
    try { return !empty($GLOBALS['u5mail_duplicate_receipts'][u5mail_duplicate_key($to, $subject, $body)]); }
    catch (InvalidArgumentException $e) { return false; }
}
