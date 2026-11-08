<?php
/* Delivery addresses only. Historical CMS account keys use u5idn.inc.php. */
function u5mail_utf8($text, $encoding = 'cms') {
    if ($text === null) $text = '';
    if (is_scalar($text)) $text = (string) $text;
    if (!is_string($text)) throw new InvalidArgumentException('Email text must be a string.');
    if ($encoding === 'cms') $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
    elseif ($encoding !== 'utf8') throw new InvalidArgumentException('Unknown email input encoding.');
    if (!preg_match('//u', $text)) throw new InvalidArgumentException('Invalid UTF-8 in email text.');
    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function u5mail_domain($domain) {
    if ($domain === '') throw new InvalidArgumentException('Email domain is missing.');
    // Current UTS46 maps capital sharp S to sharp S, not ss. Older IDNA
    // data tables disagree even with nontransitional flags (Unicode U+1E9E).
    $domain = str_replace("\xE1\xBA\x9E", "\xC3\x9F", $domain);
    if ($domain[0] === '[') {
        $ip = substr($domain, 1, -1);
        if (stripos($ip, 'IPv6:') === 0) $ip = substr($ip, 5);
        if (substr($domain, -1) !== ']' || !filter_var($ip, FILTER_VALIDATE_IP)) throw new InvalidArgumentException('Invalid email domain literal.');
        return $domain;
    }
    $info = array();
    // Explicit values are the UTS46 flags, independent of ICU's changing defaults.
    $flags = 2 | 4 | 8 | 16;
    if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46')) {
        $ascii = idn_to_ascii($domain, $flags, INTL_IDNA_VARIANT_UTS46, $info);
    } else {
        // Private fallback: never install global IDN functions/constants or alter
        // the historical identity normalisation capability profile.
        require_once __DIR__.'/normalizer/Normalizer.php';
        require_once __DIR__.'/idn/Info.php';
        require_once __DIR__.'/idn/Resources/unidata/Regex.php';
        require_once __DIR__.'/idn/Resources/unidata/DisallowedRanges.php';
        require_once __DIR__.'/idn/Idn.php';
        $ascii = U5MailIntl\Idn\Idn::idn_to_ascii($domain, $flags, 1, $info);
    }
    if ($ascii === false || !empty($info['errors']) || strlen($ascii) > 253) throw new InvalidArgumentException('Invalid international email domain.');
    foreach (explode('.', $ascii) as $label) {
        if (!preg_match('/\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\z/iD', $label)) throw new InvalidArgumentException('Invalid email domain label.');
    }
    return strtolower($ascii);
}

function u5mail_address($input, $encoding = 'cms') {
    $address = u5mail_utf8($input, $encoding);
    if (preg_match('/[\x00-\x1f\x7f]/', $address)) throw new InvalidArgumentException('Control characters in email address.');
    $address = trim($address);
    $at = strrpos($address, '@');
    if ($at === false) throw new InvalidArgumentException('Email address must contain @.');
    $local = substr($address, 0, $at);
    $domain = substr($address, $at + 1);
    $atom = '[a-zA-Z0-9!#$%&\x27*+\/=?^_`{|}~\-\x{80}-\x{10FFFF}]+';
    $quoted = '/\A"(?:[^"\\\\\x00-\x1f\x7f]|\\\\[\x20-\x7e])*"\z/uD';
    if ($local === '' || strlen($local) > 64 || (!preg_match('/\A'.$atom.'(?:\.'.$atom.')*\z/uD', $local) && !preg_match($quoted, $local))) throw new InvalidArgumentException('Invalid email local-part.');
    $address = $local.'@'.u5mail_domain($domain);
    if (strlen($address) > 254) throw new InvalidArgumentException('Email address is too long.');
    return $address;
}

function u5mail_addresses($input, $encoding = 'cms', &$names = null) {
    if ($names === null) $names = array();
    if (is_array($input)) {
        $out = array();
        foreach ($input as $value) foreach (u5mail_addresses($value, $encoding, $names) as $address) if (!in_array($address, $out, true)) $out[] = $address;
        return $out;
    }
    $input = u5mail_utf8($input, $encoding);
    $parts = array(); $part = ''; $quoted = false; $escape = false;
    for ($i = 0, $n = strlen($input); $i < $n; $i++) {
        $c = $input[$i];
        if (!$quoted && ($c === ',' || $c === ';')) { $parts[] = $part; $part = ''; continue; }
        $part .= $c;
        if ($escape) { $escape = false; continue; }
        if ($quoted && $c === '\\') { $escape = true; continue; }
        if ($c === '"') $quoted = !$quoted;
    }
    $parts[] = $part;
    $out = array();
    foreach ($parts as $part) {
        if (trim($part) === '') continue;
        $name = '';
        if (preg_match('/\A\s*(.*?)\s*<([^<>]+)>\s*\z/usD', $part, $match)) {
            $name = trim($match[1]);
            if (strlen($name) >= 2 && $name[0] === '"' && substr($name, -1) === '"') {
                $name = preg_replace('/\\\\(.)/us', '$1', substr($name, 1, -1));
            }
            if (preg_match('/[\x00-\x1f\x7f]/', $name)) throw new InvalidArgumentException('Control characters in email display name.');
            $part = $match[2];
        }
        // Already decoded: do not decode a second time (literal ampersands).
        $address = u5mail_address_decoded($part);
        if (!in_array($address, $out, true)) { $out[] = $address; if ($name !== '') $names[$address] = $name; }
    }
    return $out;
}

function u5mail_address_decoded($address) {
    // Preserve literal ampersands through the one decoding boundary.
    return u5mail_address(str_replace('&', '&amp;', $address), 'utf8');
}

/* Legacy backend JavaScript uses escape(), including UTF-16 surrogate pairs. */
function u5mail_browser_escapes($input) {
    return preg_replace_callback('/%u([dD][89aAbB][0-9a-fA-F]{2})%u([dD][c-fC-F][0-9a-fA-F]{2})|%u([0-9a-fA-F]{4})/', function ($match) {
        $point = $match[1] !== '' ? 0x10000 + ((hexdec($match[1]) - 0xd800) << 10) + hexdec($match[2]) - 0xdc00 : hexdec($match[3]);
        if ($point >= 0xd800 && $point <= 0xdfff) throw new InvalidArgumentException('Unpaired UTF-16 surrogate in email input.');
        return '&#'.$point.';';
    }, $input);
}

function u5mail_extract($input) {
    $input = u5mail_utf8($input);
    $local = '[a-zA-Z0-9!#$%&\x27*+\/=?^_`{|}~.\-\x{80}-\x{10FFFF}]+';
    $domain = '[a-zA-Z0-9\-\x{80}-\x{10FFFF}]+(?:\.[a-zA-Z0-9\-\x{80}-\x{10FFFF}]+)+';
    preg_match_all('/'.$local.'@'.$domain.'/u', $input, $matches);
    $out = array();
    foreach ($matches[0] as $value) {
        try { $address = u5mail_address_decoded($value); }
        catch (InvalidArgumentException $e) { continue; }
        if (!in_array($address, $out, true)) $out[] = $address;
    }
    return implode(',', $out);
}

function u5mail_requires_utf8(array $addresses) {
    foreach ($addresses as $address) if (preg_match('/[^\x00-\x7f]/', $address)) return true;
    return false;
}

function u5mail_error($error) {
    error_log('u5CMS mail delivery failed: '.$error->getMessage());
    $message = 'Email could not be sent. Please contact the website administrator.';
    if (strpos($error->getMessage(), 'SMTPUTF8') !== false) $message = 'Email could not be sent. The configured mail server does not support the international characters before @ in this address. Please contact the website administrator.';
    echo '<div role="alert" style="color:#a00000;background:#fff0f0;padding:0.75em">'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</div>';
    // Form/PayPal completion can navigate away from an iframe; show the failure
    // before that navigation without interrupting an already saved transaction.
    if (PHP_SAPI !== 'cli') echo '<script>alert('.json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT).');</script>';
}
