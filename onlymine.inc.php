<?php
// Ownership applies to SELF form records after the site's normal authentication.
function u5onlymineUsesDomain() {
    return ($GLOBALS['u5onlyminescope'] ?? 'user') === 'domain';
}

function u5onlymineRemotepart($identity) {
    if (!is_string($identity) || $identity === '') return '';
    $identity = str_replace(',.', ';', $identity);
    // u5CMS supplies Windows-1252 plus character references, including values
    // read over its SET NAMES latin1 connection. Do not guess UTF-8 from bytes:
    // that could merge two distinct, valid legacy domain spellings.
    $identity = function_exists('mb_convert_encoding')
        ? mb_convert_encoding($identity, 'UTF-8', 'Windows-1252') : '';
    $identity = html_entity_decode($identity, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (substr_count($identity, '@') !== 1 || preg_match('/[\x00-\x20\x7f]/', $identity)) return '';
    list($local, $domain) = explode('@', $identity, 2);
    if ($local === '' || $domain === '' || preg_match('/[,;<>]/', $identity)) return '';
    if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46')) {
        $info = array();
        $flags = IDNA_NONTRANSITIONAL_TO_ASCII | IDNA_USE_STD3_RULES | IDNA_CHECK_BIDI | IDNA_CHECK_CONTEXTJ;
        $domain = idn_to_ascii($domain, $flags, INTL_IDNA_VARIANT_UTS46, $info);
        if ($domain === false || !isset($info['errors']) || $info['errors'] !== 0) return '';
    } elseif (preg_match('/[^\x00-\x7f]/', $domain) || stripos($domain, 'xn--') !== false) {
        // Fail closed for IDN if intl is later removed from the installation.
        return '';
    }
    $domain = strtolower($domain);
    if ($domain === '' || strlen($domain) > 253) return '';
    foreach (explode('.', $domain) as $label) {
        if (!preg_match('/\A[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\z/D', $label)) return '';
    }
    return $domain;
}

function u5onlymineDomainHash($identity) {
    $domain = u5onlymineRemotepart($identity);
    return $domain === '' ? '' : hash('sha256', $domain);
}

function u5onlymineCurrentRemotepart() {
    // login.inc.php must actually have validated the credentials. Merely
    // presenting a cookie/Basic-Auth username does not establish ownership.
    if (!u5onlymineUsesDomain() || ($GLOBALS['unknown'] ?? '') !== 'ok') return '';
    return u5onlymineRemotepart(isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '');
}

// Administrator domains are explicitly configured in DNS ASCII (A-label) form.
function u5onlymineCanWriteAll() {
    $domain = u5onlymineCurrentRemotepart();
    $domains = $GLOBALS['u5onlymineadmindomains'] ?? array();
    return $domain !== '' && is_array($domains) && in_array($domain, $domains, true);
}

function u5onlymineCanEditAuthor($identity) {
    $domain = u5onlymineCurrentRemotepart();
    return $domain !== '' && ($domain === u5onlymineRemotepart($identity) || u5onlymineCanWriteAll());
}

function u5onlymineHasMarker($commands, $marker) {
    $position = strpos($commands, $marker);
    return u5onlymineUsesDomain() ? $position !== false : $position > 1;
}

function u5onlymineFormRules($allowPageAsTarget = false) {
    $domainMode = u5onlymineUsesDomain();
    if ($domainMode) {
        if ($allowPageAsTarget && !isset($_GET['n'])) $_GET['n'] = $_GET['c'] ?? '';
        if (!is_string($_GET['n'] ?? null) || $_GET['n'] === '') {
            http_response_code(400);
            exit('Invalid target form.');
        }
        $_GET['c'] = $_GET['n'];
    } elseif (($_GET['c'] ?? '') === '' && ($_GET['n'] ?? '') !== '') {
        $_GET['c'] = $_GET['n'];
    }
    $active = $domainMode ? " AND deleted!=1" : "";
    $page = mysql_real_escape_string($_GET['c'] ?? '');
    $result = mysql_query("SELECT content_1,content_2,content_3,content_4,content_5 FROM resources WHERE name='$page'$active");
    if (!$result || ($domainMode && mysql_num_rows($result) !== 1)) {
        http_response_code(403);
        exit('Target form unavailable.');
    }
    $row = mysql_fetch_row($result);
    mysql_free_result($result);
    return $row ? implode('', $row) : '';
}

function u5onlymineOwnsRequestedRecord() {
    if (!u5onlymineUsesDomain()) {
        $owner = mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']));
        $id = mysql_real_escape_string($_GET['id']);
        $form = mysql_real_escape_string($_GET['n']);
        $result = mysql_query("SELECT id FROM formdata WHERE authuser='$owner' AND id='$id' AND formname='$form'");
        return $result && mysql_num_rows($result) > 0;
    }
    if (u5onlymineCurrentRemotepart() === '' || !is_string($_GET['id'] ?? null)
        || !is_string($_GET['n'] ?? null) || $_GET['n'] === ''
        || !preg_match('/\A[1-9][0-9]{0,9}\z/D', $_GET['id'])) return false;
    $result = mysql_query("SELECT authuser FROM formdata WHERE id='" . mysql_real_escape_string($_GET['id'])
        . "' AND formname='" . mysql_real_escape_string($_GET['n']) . "' AND status=1");
    if ($result === false || mysql_num_rows($result) !== 1) return false;
    $record = mysql_fetch_array($result);
    mysql_free_result($result);
    return u5onlymineCanEditAuthor($record['authuser']);
}

function u5onlymineSelfSql($formname) {
    if (!u5onlymineUsesDomain()) {
        return " AND authuser='" . mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER'])) . "'";
    }
    $domain = u5onlymineCurrentRemotepart();
    if ($domain === '' || !is_string($formname) || $formname === '') return ' AND 1=0';
    if (u5onlymineCanWriteAll()) return '';
    // Normalize using exactly the same PHP function as enforcement and UI.
    // HEX retains exact storage bytes without changing the charset of authuser:
    // BINARY authuser would bypass SET NAMES latin1 conversion on retrieval.
    $result = mysql_query("SELECT DISTINCT authuser, HEX(authuser) AS author_hex FROM formdata WHERE formname='"
        . mysql_real_escape_string($formname) . "'");
    if ($result === false) return ' AND 1=0';
    $authors = array();
    while ($row = mysql_fetch_array($result)) {
        if ($domain === u5onlymineRemotepart($row['authuser'])
            && preg_match('/\A[0-9A-F]+\z/D', $row['author_hex'])) {
            $authors[] = "'" . $row['author_hex'] . "'";
        }
    }
    mysql_free_result($result);
    return count($authors) ? ' AND HEX(authuser) IN (' . implode(',', $authors) . ')' : ' AND 1=0';
}

// An author can select the site's storage routine using this literal marker
// in the stored form. Request values cannot select a function or a form writer.
function u5FormUsesSiteWriter($resource) {
    for ($i = 1; $i <= 5; $i++) {
        if (strpos($resource['content_'.$i] ?? '', '<!-- u5cms-form-writer -->') !== false) return true;
    }
    return false;
}

function u5FormRunSiteWriter($form, $enabled) {
    if (!$enabled) return false;
    if (!function_exists('u5FormWriteRecord') || u5FormWriteRecord($form) !== true) {
        http_response_code(500);
        exit('Site form writer did not complete.');
    }
    return true;
}
