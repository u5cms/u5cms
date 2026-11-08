<?php
/* Match the existing Laminas transport's refusal behaviour: a rejected RCPT
 * aborts before DATA instead of partially delivering to the remaining list. */
class U5MailSmtp extends U5MailVendor\PHPMailer\SMTP {
    public function recipient($address, $dsn = '') {
        if (!parent::recipient($address, $dsn)) {
            throw new U5MailVendor\PHPMailer\Exception('SMTP recipient rejected; no message body was transmitted.');
        }
        return true;
    }
}
