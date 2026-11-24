<?php
/* Loaded only by sites which explicitly enable the archived-SAML form mode. */

/* Call from the private configuration to also detect a downgrade or mixed tree. */
function u5EvidenceGuardEntry() {
    $entry = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
    $protected = array('formsave.php','editformsave.php','delformsave.php','paypal.php','formdata2.php',
        'formdatamail.php','formdata2csv.php','cleanuseruploads.php');
    if (!in_array($entry,$protected,true)) return;
    static $verified = array();
    $file = $_SERVER['SCRIPT_FILENAME'];
    if (isset($verified[$file])) return;
    $source = file_get_contents($file);
    if ($source === false || strpos($source,'u5EvidenceRequireMode(1);') === false) {
        http_response_code(503);
        exit('The installed form handler does not support the configured evidence format.');
    }
    $verified[$file] = true;
}

function u5EvidenceRequireMode($apiVersion) {
    if ($apiVersion !== 1 || ($GLOBALS['formSenderEvidenceRequired'] ?? null) !== 'saml-archive-v1') {
        http_response_code(503);
        exit('Form evidence configuration needs an explicit upgrade.');
    }
}

function u5EvidenceEmail($email, $encoding = 'utf8') {
    require_once __DIR__.'/mail/addresses.php';
    if (!is_string($email) || preg_match('/[\r\n\x00]/', $email)) throw new RuntimeException('Invalid email value.');
    return u5mail_address(trim($email), $encoding);
}

function u5EvidenceEmailKey($email, $encoding = 'utf8') {
    return strtolower(u5EvidenceEmail($email, $encoding));
}

function u5EvidenceAttributeEmail(array $attributes) {
    $emails = array();
    foreach ($attributes as $name => $values) {
        if (preg_match('~(?:^|[/#:])emailaddress$~i', (string) $name) || $name === 'mail') {
            foreach ((array) $values as $value) {
                $email = u5EvidenceEmail($value);
                $emails[strtolower($email)] = $email;
            }
        }
    }
    if (count($emails) !== 1) throw new RuntimeException('The SAML email is missing or ambiguous.');
    return reset($emails);
}

function u5EvidenceFormEmail(array $post) {
    $values = array();
    foreach (array('youremail', 'youremail_mandatory') as $key) {
        if (isset($post[$key]) && $post[$key] !== '') {
            $email = u5EvidenceEmail($post[$key], 'cms');
            $values[strtolower($email)] = $email;
        }
    }
    if (count($values) > 1) throw new RuntimeException('Conflicting form email fields.');
    return $values ? reset($values) : null;
}

function u5EvidenceValidateArchive(array $row, array $previous, $source, array $attributes) {
    if (empty($previous['id']) || empty($previous['issuer']) ||
        (string) $row['response_id'] !== (string) $previous['id'] ||
        (string) $row['idp_entity_id'] !== (string) $previous['issuer'] ||
        (string) $row['authsource'] !== (string) $source) {
        throw new RuntimeException('The archive does not match the authenticated SAML response.');
    }
    $raw = $row['saml_response_b64'];
    if (!is_string($raw) || $raw === '' || strlen($raw) > 2000000 ||
        !hash_equals((string) $row['saml_response_sha256'], hash('sha256', $raw))) {
        throw new RuntimeException('The archived original token failed its integrity check.');
    }
    $xml = base64_decode($raw, true);
    if ($xml === false || stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
        throw new RuntimeException('Invalid archived SAML XML.');
    }
    $document = new DOMDocument();
    $oldErrors = libxml_use_internal_errors(true);
    try { $loaded = $document->loadXML($xml, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($oldErrors); }
    $root = $document->documentElement;
    if (!$loaded || !$root || $root->localName !== 'Response' ||
        $root->namespaceURI !== 'urn:oasis:names:tc:SAML:2.0:protocol' ||
        $root->getAttribute('ID') !== (string) $previous['id']) {
        throw new RuntimeException('Original SAML response identity mismatch.');
    }
    $archivedAttributes = json_decode($row['attributes_json'], true, 512, JSON_THROW_ON_ERROR);
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
    $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');
    if ($xpath->query('/samlp:Response/saml:Assertion')->length !== 1) throw new RuntimeException('Expected one assertion.');
    $xmlAttributes = array();
    foreach ($xpath->query('/samlp:Response/saml:Assertion/saml:AttributeStatement/saml:Attribute') as $attribute) {
        foreach ($xpath->query('saml:AttributeValue', $attribute) as $value) $xmlAttributes[$attribute->getAttribute('Name')][] = $value->textContent;
    }
    $email = u5EvidenceAttributeEmail($xmlAttributes);
    if (u5EvidenceEmailKey($email) !== u5EvidenceEmailKey(u5EvidenceAttributeEmail($archivedAttributes))) {
        throw new RuntimeException('Original XML and archived SAML email differ.');
    }
    if (u5EvidenceEmailKey($email) !== u5EvidenceEmailKey(u5EvidenceAttributeEmail($attributes))) {
        throw new RuntimeException('Archived and active SAML email differ.');
    }
    return array('email' => $email, 'xml_sha256' => hash('sha256', $xml));
}


/* Resolve precisely one archive entry for the currently authenticated SP session. */
function u5EvidenceIdentity($account, array $post) {
    global $u5samlsspconfigdir, $u5samlsspauthsourcekey, $u5samlmockauth;
    if ($u5samlmockauth === 'yes' || !$u5samlsspauthsourcekey || !$u5samlsspconfigdir) {
        throw new RuntimeException('A configured SAML service provider is required.');
    }
    putenv('SIMPLESAMLPHP_CONFIG_DIR='.$u5samlsspconfigdir);
    require_once __DIR__.'/simplesaml/_include.php';
    $auth = new \SimpleSAML\Auth\Simple($u5samlsspauthsourcekey);
    try {
        if (!$auth->isAuthenticated()) throw new RuntimeException('A current login is required.');
        $previous = $auth->getAuthData('saml:sp:prevAuth');
        $attributes = $auth->getAttributes();
    } finally {
        \SimpleSAML\Session::getSessionFromRequest()->cleanup();
    }
    if (empty($previous['id']) || empty($previous['issuer'])) throw new RuntimeException('Missing response identity.');
    $sql = "SELECT id, response_id, authsource, idp_entity_id, CAST(CONVERT(attributes_json USING utf8mb4) AS BINARY) AS attributes_json, saml_response_b64, saml_response_sha256
            FROM saml_response_archive WHERE response_id=".u5EvidenceQuote($previous['id'])."
            AND BINARY response_id=".u5EvidenceQuote($previous['id'])."
            AND BINARY idp_entity_id=".u5EvidenceQuote($previous['issuer'])."
            AND BINARY authsource=".u5EvidenceQuote($u5samlsspauthsourcekey)." LIMIT 2";
    $result = mysql_query($sql);
    if (!$result || mysql_num_rows($result) !== 1) throw new RuntimeException('No unique archived response.');
    $row = mysql_fetch_assoc($result);
    $source = u5EvidenceValidateArchive($row, $previous, $u5samlsspauthsourcekey, $attributes);
    $login = u5EvidenceEmail($account, 'cms');
    if (u5EvidenceEmailKey($login) !== u5EvidenceEmailKey($source['email'])) {
        throw new RuntimeException('Login and verified sender differ.');
    }
    $contact = u5EvidenceFormEmail($post);
    return array('archive_id'=>(string)$row['id'], 'xml_sha256'=>$source['xml_sha256'],
        'saml_email'=>$source['email'], 'login_email'=>$login, 'form_email'=>$contact,
        'comparison'=>$contact === null ? 'missing' : (u5EvidenceEmailKey($contact) === u5EvidenceEmailKey($login) ? 'match' : 'changed'));
}

/* Extend only the protected version INSERT. Both boundaries belong to the shipped
   template. Escaped SQL values cannot introduce an unescaped newline. */
function u5EvidenceVersionInsert($sql, $columns, $values) {
    $boundary = "\n) VALUES";
    $offset = strpos($sql,$boundary);
    if ($offset === false || substr($sql,-2) !== "\n)") throw new RuntimeException('Unsupported version INSERT.');
    return substr($sql,0,$offset).$columns.substr($sql,$offset,-2).$values."\n)";
}

function u5EvidenceQuote($value) {
    return "'".mysql_real_escape_string((string)$value)."'";
}

function u5EvidenceKey() {
    $key = $GLOBALS['formSenderEvidenceSigningKey'] ?? null;
    if (!is_string($key) || strlen($key) < 32) throw new RuntimeException('A private signing key is required.');
    return $key;
}

function u5EvidenceEnvelope(array $record, $key) {
    $json = json_encode($record, JSON_THROW_ON_ERROR);
    return json_encode(array('format'=>'u5-form-1', 'record'=>$record,
        'mac'=>hash_hmac('sha256', "u5-form-1\0".$json, $key)), JSON_THROW_ON_ERROR);
}

function u5EvidenceRead($envelope) {
    if ($envelope === null || $envelope === '') return array('status'=>'unrecorded');
    try {
        $data = json_decode($envelope, true, 32, JSON_THROW_ON_ERROR);
        if (($data['format'] ?? null) !== 'u5-form-1') return array('status'=>'unverified');
        $record = $data['record'];
        $input = "u5-form-1\0".json_encode($record, JSON_THROW_ON_ERROR);
        $keys = array_merge(array(u5EvidenceKey()), (array)($GLOBALS['formSenderEvidenceVerificationKeys'] ?? array()));
        foreach ($keys as $key) {
            if (is_string($key) && strlen($key)>=32 && hash_equals(hash_hmac('sha256', $input, $key), $data['mac'])) {
                return array('status'=>'verified', 'record'=>$record);
            }
        }
    } catch (Throwable $e) {}
    return array('status'=>'unverified');
}

/* Resolve transport and display controls on the server before the CMS builds its CSV. */
function u5EvidencePrepare(array &$post, $html) {
    $doc = new DOMDocument();
    $old = libxml_use_internal_errors(true);
    try { $doc->loadHTML('<meta charset="windows-1252">'.$html, LIBXML_NONET); }
    finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
    $xpath = new DOMXPath($doc);
    $forms = $xpath->query('//form[@name="u5form"]');
    if ($forms->length !== 1) throw new RuntimeException('Expected one CMS form.');
    $controls = array_fill_keys(array('ouremail','thankssubject','thankstext','thanksgreetings','thanks',
        'em2me','em2cu','in2my','nt2me','nt2cu','qv2bo','ed2cu'), '');
    $found = array();
    foreach ($forms->item(0)->getElementsByTagName('input') as $input) {
        $key = $input->getAttribute('name');
        if (strtolower($input->getAttribute('type')) !== 'hidden' || !array_key_exists($key,$controls)) continue;
        if (isset($found[$key])) throw new RuntimeException('Ambiguous form delivery controls.');
        $found[$key] = true;
        $controls[$key] = mb_convert_encoding($input->getAttribute('value'), 'Windows-1252','UTF-8');
    }
    if ($controls['ed2cu']==='on') throw new RuntimeException('Evidence submissions require an append-only form.');
    $owner = $controls['ouremail'] === '' ? $GLOBALS['mymail'] : $controls['ouremail'];
    $owner = u5EvidenceEmail($owner, 'cms');
    $allowed = (array)($GLOBALS['formEvidenceInternalRecipients'] ?? array($GLOBALS['mymail']));
    $allowed = array_map(function($address) { return u5EvidenceEmailKey($address,'cms'); }, $allowed);
    if (!in_array(u5EvidenceEmailKey($owner), $allowed, true)) throw new RuntimeException('Internal destination is not configured.');
    foreach ($controls as $key=>$value) $post[$key]=$value;
    return $owner;
}

function u5EvidenceEvent($id, $attempt, $role, $state, $key) {
    $date = gmdate('Y-m-d H:i:s');
    $values = array($id, $attempt, $role, $state, $date);
    $values[] = hash_hmac('sha256', "u5-form-mail-1\0".json_encode($values, JSON_THROW_ON_ERROR), $key);
    $sql = 'INSERT INTO form_evidence_mail (evidence_id,attempt_id,role_name,event_state,created_utc,mac) VALUES ('
         .implode(',', array_map('u5EvidenceQuote',$values)).')';
    if (!mysql_query($sql)) throw new RuntimeException('Could not record mail event.');
}

function u5EvidenceSend($record, $key, $internalBody) {
    require_once __DIR__.'/mail/delivery.php';
    $sender = u5EvidenceEmail($GLOBALS['mymail'],'cms');
    $id = $record['id'];
    $source = $record['identity'];
    $confirmed = true;
    foreach ($record['destinations'] as $role=>$to) {
        $attempt = bin2hex(random_bytes(16));
        u5EvidenceEvent($id, $attempt, $role, 'started', $key);
        $message = "Submission: ".$id."\r\nDelivery: ".$attempt;
        if ($role !== 'receipt') {
            $message .= "\r\nForm: ".$record['form']."\r\nAuthenticated sender: ".$source['login_email']
                ."\r\nContact: ".($source['form_email'] ?? '')."\r\nComparison: ".$source['comparison']
                ."\r\nArchive: ".$source['archive_id']."\r\nSHA-256 (XML): ".$source['xml_sha256'];
            if ($role === 'owner') $message .= "\r\n\r\n".u5mail_utf8($internalBody);
        }
        $accepted = false;
        try {
            $accepted = u5mail_send(array('from'=>$sender, 'to'=>array($to), 'subject'=>'Form submission '.$id, 'body'=>$message)) === true;
        } catch (Throwable $e) {
            // An interrupted hand-off may have reached the transport. Never replay it here.
        }
        u5EvidenceEvent($id, $attempt, $role, $accepted ? 'accepted' : 'unconfirmed', $key);
        if (!$accepted) $confirmed = false;
    }
    return $confirmed;
}

function u5EvidenceSubmit($name, $owner, array $identity, $head, $data, $internalBody) {
    $key = u5EvidenceKey();
    $admin = u5EvidenceEmail($GLOBALS['mymail'],'cms');
    $destinations = array();
    if ($_POST['in2my'] !== 'off') $destinations['admin'] = $admin;
    if ($_POST['em2me'] !== 'off') $destinations['owner'] = $owner;
    if (!$destinations) throw new RuntimeException('No internal delivery route.');
    if ($_POST['em2cu'] !== 'off' && $identity['form_email'] !== null) $destinations['receipt'] = $identity['form_email'];
    if (!mysql_query('SELECT evidence_id,attempt_id,role_name,event_state,created_utc,mac FROM form_evidence_mail LIMIT 0')) {
        throw new RuntimeException('Mail journal unavailable.');
    }
    $id = bin2hex(random_bytes(16));
    $record = array('id'=>$id, 'utc'=>gmdate('Y-m-d\TH:i:s\Z'), 'form'=>u5mail_utf8($name),
        'account_key'=>u5mail_utf8(u5flatidnlower($_SERVER['PHP_AUTH_USER'])), 'identity'=>$identity, 'head_b64'=>base64_encode($head), 'data_b64'=>base64_encode($data), 'destinations'=>$destinations);
    $values = array($name, $head, $data, time(), $_SERVER['REMOTE_ADDR'], u5flatidnlower($_SERVER['PHP_AUTH_USER']),
        date('Y.m.d H:i:s'), time(), u5EvidenceEnvelope($record,$key));
    $sql = 'INSERT INTO formdata (formname,headcsv,datacsv,time,ip,authuser,humantime,lastmut,sender_evidence_json) VALUES ('
         .implode(',',array_map('u5EvidenceQuote',$values)).')';
    if (!mysql_query($sql)) throw new RuntimeException('Could not store submission.');
    try { $confirmed = u5EvidenceSend($record,$key,$internalBody); }
    catch (Throwable $error) { $confirmed = false; }
    if (!$confirmed) {
        $notice = 'Submission saved. Mail delivery is unconfirmed. Reference: '.$id;
        exit($notice.'<script>alert('.json_encode($notice).');</script>');
    }
}

function u5EvidenceSummary(array $row, $csv) {
    require_once __DIR__.'/mail/addresses.php';
    $result = u5EvidenceRead($row['sender_evidence_json'] ?? null);
    $fields = array($result['status'], '', '', '', '', '', '');
    if ($result['status'] === 'verified') {
        $r = $result['record'];
        $i = $r['identity'];
        $encoding = $csv ? 'utf8' : 'cms';
        if (u5mail_utf8($row['formname'], $encoding) === $r['form'] &&
            u5mail_utf8($row['authuser'], $encoding) === $r['account_key']) {
            $fields = array('verified', $r['id'], $i['saml_email'], $i['form_email'] ?? '', $i['comparison'], $i['archive_id'], $i['xml_sha256']);
        } else {
            $fields[0] = 'unverified';
        }
    }
    foreach ($fields as &$v) {
        if ($csv) {
            if (preg_match('/^[=+@-]/', $v)) $v = "'".$v;
            $v = '"'.str_replace('"','""',$v).'"';
        } else {
            $v = mb_encode_numericentity(htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), array(128,0x10ffff,0,0xffffff),'UTF-8');
            $v = str_replace(';',',.',$v);
        }
    }
    return implode(';',$fields).';';
}

function u5EvidenceHeader() {
    return 'Sender evidence;Submission ID;Verified SAML email;Submitted email;Comparison;Archive ID;SHA-256 (XML);';
}
