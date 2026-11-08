<?php
/* Independent delivery expectations. No transport or database is invoked. */
$_SERVER['REQUEST_URI'] = '/international-email-test';
require dirname(__DIR__).'/myfunctions.inc.php';
require dirname(__DIR__).'/u5admin/u5idn.inc.php';
ini_set('default_charset', 'Windows-1252');
$checks = 0;
function emailCheck($actual, $expected, $label) {
    global $checks;
    $checks++;
    if ($actual !== $expected) throw new RuntimeException($label.': '.var_export($actual, true).' != '.var_export($expected, true));
}
$before = array('idn'=>function_exists('idn_to_ascii'), 'mb'=>mb_internal_encoding());
$cases = array(
    'Anna+Privat@example.ch'=>'Anna+Privat@example.ch',
    'info@m&uuml;ller.ch'=>'info@xn--mller-kva.ch',
    'info@mu&#776;ller.ch'=>'info@xn--mller-kva.ch',
    'info@M&Uuml;LLER.CH'=>'info@xn--mller-kva.ch',
    'info@stra&szlig;e.ch'=>'info@xn--strae-oqa.ch',
    'info@stra&#223;e.ch'=>'info@xn--strae-oqa.ch',
    'info@stra&#xDF;e.ch'=>'info@xn--strae-oqa.ch',
    'info@STRA&#7838;E.CH'=>'info@xn--strae-oqa.ch',
    'info@stra&#x1E9E;e.ch'=>'info@xn--strae-oqa.ch',
    'info@xn--strae-oqa.ch'=>'info@xn--strae-oqa.ch',
    'info@&#26085;&#26412;.jp'=>'info@xn--wgv71a.jp',
    'info@&#x65e5;&#x672c;.jp'=>'info@xn--wgv71a.jp',
    'info@&#20363;&#23376;.&#20013;&#22269;'=>'info@xn--fsqu00a.xn--fiqs8s',
    'info@&#134071;.jp'=>'info@xn--7l3i.jp',
    'M&uuml;ller@stra&#223;e.ch'=>"M\xC3\xBCller@xn--strae-oqa.ch",
    '&Auml;nne@example.ch'=>"\xC3\x84nne@example.ch",
    '&#7838;@example.ch'=>"\xE1\xBA\x9E@example.ch",
    '&#28450;&#23383;@example.ch'=>"\xE6\xBC\xA2\xE5\xAD\x97@example.ch",
    '&#134071;@example.ch'=>"\xF0\xA0\xAE\xB7@example.ch",
    'a%tag+X@example.ch'=>'a%tag+X@example.ch',
    'a&amp;b@example.ch'=>'a&b@example.ch',
    'O&apos;Neil@example.ch'=>"O'Neil@example.ch",
    '"space name"@example.ch'=>'"space name"@example.ch',
    'info@[127.0.0.1]'=>'info@[127.0.0.1]'
);
foreach ($cases as $input=>$expected) {
    emailCheck(u5mail_address($input), $expected, $input);
    emailCheck(u5mail_addresses($input), array($expected), 'list '.$input);
    emailCheck(u5mail_address_decoded($expected), $expected, 'idempotent '.$input);
}
$invalid = array('info@stra&Szlig;e.ch', 'info@stra&SZLIG;e.ch', 'not-email', '@example.ch',
    'x@@example.ch', 'x@', 'a..b@example.ch', '.a@example.ch', 'a.@example.ch',
    'x@a..ch', 'x@-bad.ch', 'x@bad-.ch', 'x@bad_domain.ch',
    "x@example.ch\r\nBcc: other@example.ch", 'x@example.ch&#10;Bcc:other@example.ch',
    'x@example.ch&#0;', 'x@&lt;example.ch&gt;', str_repeat('a',65).'@example.ch');
foreach ($invalid as $input) {
    $failed = false;
    try { u5mail_address($input); } catch (InvalidArgumentException $e) { $failed = true; }
    emailCheck($failed, true, 'reject '.$input);
}
emailCheck(u5mail_addresses('one@example.ch;two@stra&szlig;e.ch'), array('one@example.ch','two@xn--strae-oqa.ch'), 'separators after entity decoding');
emailCheck(u5mail_addresses('"space;name"@example.ch,one@example.ch'), array('"space;name"@example.ch','one@example.ch'), 'quoted separator');
emailCheck(u5mail_extract('People: Anna+Privat@example.ch; &Auml;nne@stra&#223;e.ch'), "Anna+Privat@example.ch,\xC3\x84nne@xn--strae-oqa.ch", 'template extraction');
emailCheck(array('idn'=>function_exists('idn_to_ascii'), 'mb'=>mb_internal_encoding()), $before, 'no global identity/profile changes');
emailCheck(u5mail_requires_utf8(array('info@xn--strae-oqa.ch')), false, 'IDN needs no SMTPUTF8');
emailCheck(u5mail_requires_utf8(array("M\xC3\xBCller@example.ch")), true, 'Unicode local-part needs SMTPUTF8');
echo json_encode(array('php'=>PHP_VERSION,'checks'=>$checks,'native_intl'=>extension_loaded('intl'))), "\n";
