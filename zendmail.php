<?php
require_once __DIR__.'/u5admin/u5idn.inc.php';
require_once __DIR__.'/mail/delivery.php';
$u5mail_sent = false;
$mail = null;
try {
    if (($zendname ?? '') == ($zendfrom ?? '') && strpos($zendfrom ?? '', '@') !== false) {
        $parts = explode('@', $zendfrom, 2);
        $first = explode('.', $parts[0]);
        $second = explode('.', $parts[1]);
        $zendname = strtoupper($first[0]).' '.strtoupper($second[0]);
    }
    $u5mail_to_names = array();
    $u5mail_to = u5mail_addresses($zendto, 'cms', $u5mail_to_names);
    $u5mail_sent = u5mail_send(array(
        'from'=>u5mail_address($zendfrom),
        'to'=>$u5mail_to,
        'to_names'=>$u5mail_to_names,
        'name'=>u5mail_utf8($zendname ?? ''),
        'subject'=>u5mail_utf8($zendsubject ?? ''),
        'body'=>u5mail_utf8($zendmessage ?? '')
    ), false, array(), $mail);
} catch (Throwable $e) {
    if (u5mail_duplicate_delivered($zendto ?? '', $zendsubject ?? '', $zendmessage ?? '')) {
        $u5mail_sent = true;
        error_log('u5CMS primary mail failed; the matching native duplicate was accepted: '.$e->getMessage());
    } else u5mail_error($e);
}
