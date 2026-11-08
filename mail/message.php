<?php
/* Delivery local-parts are case-sensitive. Do not collapse distinct recipients
 * just because their ASCII letters differ in case. Domains are already ASCII
 * lower-case when this internal adapter is entered. */
class U5MailMessage extends U5MailVendor\PHPMailer\PHPMailer {
    protected function addAnAddress($kind, $address, $name = '') {
        if ($kind === 'Reply-To') return parent::addAnAddress($kind, $address, $name);
        if (!in_array($kind, array('to','cc','bcc'), true) || !static::validateAddress($address)) {
            throw new U5MailVendor\PHPMailer\Exception('Invalid email recipient.');
        }
        if (array_key_exists($address, $this->all_recipients)) return false;
        $this->{$kind}[] = array($address, $name);
        $this->all_recipients[$address] = true;
        return true;
    }
}
