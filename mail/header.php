<?php
/* Encode display names before quoting delimiters. The stock header quotes a
 * comma first and consequently embeds those quotes in its encoded-word. */
class U5MailToHeader extends Laminas\Mail\Header\To {
    public function getFieldValue($format = Laminas\Mail\Header\HeaderInterface::FORMAT_RAW) {
        $out = array();
        foreach ($this->getAddressList() as $address) {
            $email = $address->getEmail();
            $name = $address->getName();
            if ($name !== null && $name !== '') {
                if ($format === Laminas\Mail\Header\HeaderInterface::FORMAT_ENCODED) $name = Laminas\Mail\Header\HeaderWrap::mimeEncodeValue($name, 'UTF-8', 76);
                $email = $name.' <'.$email.'>';
            }
            if ($format !== Laminas\Mail\Header\HeaderInterface::FORMAT_RAW) Laminas\Mail\Header\HeaderValue::assertValid($email);
            $out[] = $email;
        }
        return implode(','.Laminas\Mail\Headers::FOLDING, $out);
    }
}
