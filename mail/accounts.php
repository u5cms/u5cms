<?php
/* Delivery metadata never changes the historical account/login key. */
require_once __DIR__.'/addresses.php';

function u5mail_account_address(array $account) {
    return isset($account['email_delivery']) && $account['email_delivery'] !== ''
        ? $account['email_delivery'] : $account['email'];
}

function u5mail_account_storage($address) {
    // ASCII-only, including astral characters and literal entity-like text.
    // Exactly one later CMS decoding boundary restores the transport address.
    return mb_encode_numericentity($address, array(0, 0x10ffff, 0, 0x1fffff), 'UTF-8');
}

function u5mail_account_needs_delivery($address, $key) {
    try { return $address !== u5mail_address($key); }
    catch (InvalidArgumentException $error) { return true; }
}

function u5mail_account_delivery_column($required) {
    $columns = mysql_query("SHOW COLUMNS FROM accounts LIKE 'email_delivery'");
    if ($columns !== false && mysql_num_rows($columns) > 0) return true;
    if (!$required) return false;
    // Only an invitation needing a separate address upgrades an old schema.
    // Another request may have added it in the meantime: recheck after failure.
    if (@mysql_query('ALTER TABLE accounts ADD COLUMN email_delivery TEXT NULL')) return true;
    $columns = mysql_query("SHOW COLUMNS FROM accounts LIKE 'email_delivery'");
    if ($columns !== false && mysql_num_rows($columns) > 0) return true;
    throw new RuntimeException('Cannot preserve the account delivery address. The accounts.email_delivery TEXT column could not be added.');
}

function u5mail_account_recovery_address(array $account, $requested) {
    if (!empty($account['email_delivery'])) return $account['email_delivery'];
    $stored = u5mail_address($account['email']);
    $request = u5mail_address($requested);
    // Old transitional IDNA can alias two distinct domains. Never guess the
    // original owner or send credentials to an address derived from that alias.
    if (substr($stored, strrpos($stored, '@')) !== substr($request, strrpos($request, '@'))) {
        throw new RuntimeException('The requested domain differs from the stored legacy account address. An administrator must verify the original delivery address.');
    }
    return $account['email'];
}

/* Only call in an account context, never for arbitrary form recipients. */
function u5mail_account_notification_address($key) {
    $result = mysql_query("SELECT * FROM accounts WHERE email='".mysql_real_escape_string($key)."'");
    if ($result !== false && mysql_num_rows($result) === 1) return u5mail_account_address(mysql_fetch_array($result));
    return $key;
}
