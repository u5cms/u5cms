<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

// Render the real signed-out SAML prompt with no credentials or database.
// The database stub permits reads only, so the fixture cannot hide a write.
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
$root = getenv('U5CMS_SOURCE_ROOT') ?: dirname(__DIR__);
$source = getenv('U5CMS_BACKEND_SOURCE') ?: $root . '/u5admin/usercheck.inc.php';
function mysql_real_escape_string($value) { return addslashes($value); }
function mysql_query($sql) {
    if (stripos(ltrim($sql), 'SELECT ') !== 0) throw new RuntimeException('Unexpected database mutation in login prompt.');
    return true;
}
function mysql_num_rows($result) { return 0; }
function ehtml($value) { return htmlspecialchars($value ?? '', ENT_QUOTES, 'WINDOWS-1252'); }
function pwdhsh($value) { return base64_encode(sha1($value, true)); }
function u5OidcEnabled() { return false; }
function u5ValidateTextRequest($values, $keys) {}

$_COOKIE = array();
$_POST = array();
$_SERVER['PHP_AUTH_USER'] = '';
$_SERVER['PHP_AUTH_PW'] = '';
$_SERVER['SCRIPT_NAME'] = '/u5admin/metai.php';
$_SERVER['REQUEST_URI'] = '/u5admin/metai.php?typ=i&name=example5';
$donotloadu5adminconfig = 1;
$usesessioninsteadofbasicauth = 'yes';
$autoresolvefastcgiproblems = 'no';
$automaticallyskipbackendlogin = 'no';
$u5trusteddeviceenabled = 'no';
$httpsisinuse = false;
$u5samlsalt = 'isolated-fixture';
$u5samllogininfo = 'Isolated SAML fixture';
chdir($root . '/u5admin');
require $source;
throw new RuntimeException('Signed-out SAML prompt did not stop before protected content.');
