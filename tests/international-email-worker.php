<?php
/* Called by the loopback SMTP regression. All credentials and domains synthetic. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$_SERVER['REQUEST_URI'] = '/mail-fixture';
require dirname(__DIR__).'/myfunctions.inc.php';
require dirname(__DIR__).'/u5admin/u5idn.inc.php';
ini_set('default_charset', 'Windows-1252');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
$case = json_decode(stream_get_contents(STDIN), true);
$usesmtp = isset($case['usesmtp']) ? $case['usesmtp'] : 'yes';
$config = array();
if (!empty($case['auth'])) { $config['username']='fixture'; $config['password']='fixture-only'; }
if (!empty($case['tls'])) { $config['ssl']='tls'; $config['stream_context_options']=array('ssl'=>array('verify_peer'=>false,'verify_peer_name'=>false)); }
$mysmtpoptions = new Laminas\Mail\Transport\SmtpOptions(array('name'=>'cms.example','host'=>'127.0.0.1',
    'port'=>$case['port'], 'connection_class'=>!empty($case['auth'])?$case['auth']:'smtp','connection_config'=>$config));
function cmsBytes($value) { return $value === '' ? '' : html_entity_decode(u5allnument($value), ENT_QUOTES, 'Windows-1252'); }
ob_start();
try {
    if ($case['kind']==='backup') {
        $source = file_get_contents(dirname(__DIR__).'/u5admin/backupdb.php');
        $start = strpos($source, 'function mail_att(');
        $end = strpos($source, "\n}\n", $start) + 3;
        eval(substr($source, $start, $end - $start));
        $file = tempnam(sys_get_temp_dir(), 'u5-mail-attachment-');
        file_put_contents($file, "Backup fixture\x00\xFF\r\n");
        try { $ok=mail_att(cmsBytes($case['to']), cmsBytes('sender@straße.ch'), 'Backup fixture', 'Backup body', array($file)); }
        finally { unlink($file); }
    } elseif ($case['kind']==='duplicate') {
        $ok=u5mail_duplicate(cmsBytes($case['to']), 'Duplicate fixture', 'Duplicate body');
    } elseif ($case['kind']==='native') {
        $ok=u5mail_native(cmsBytes($case['to']), 'Native fixture', 'Native body');
    } elseif ($case['kind']==='plain' || $case['kind']==='plain-with-duplicate') {
        $zendfrom=cmsBytes(isset($case['from'])?$case['from']:'sender@example.ch');
        $zendto=cmsBytes($case['to']);
        $zendname=cmsBytes('Jörg Müller – €');
        $zendsubject=cmsBytes('Grüsse & Nachrichten 2026');
        $zendmessage=cmsBytes('Plain body: Grüsse – été, &amp; entity.');
        if ($case['kind']==='plain-with-duplicate') u5mail_duplicate($zendto,$zendsubject,$zendmessage);
        include dirname(__DIR__).'/zendmail.php';
        $ok=$u5mail_sent;
    } else {
        $fields=array('from'=>u5mail_address('sender@example.ch'),'to'=>u5mail_addresses(cmsBytes($case['to'])),
            'cc'=>u5mail_addresses(cmsBytes(isset($case['cc'])?$case['cc']:'')),
            'bcc'=>u5mail_addresses(cmsBytes(isset($case['bcc'])?$case['bcc']:'')),
            'name'=>'Mail fixture', 'subject'=>'Mail fixture 2026', 'body'=>'<p>Grüsse, été and 日本語.</p>');
        $ok=u5mail_send($fields, true);
    }
    $result=array('ok'=>$ok);
} catch (Throwable $e) { $result=array('ok'=>false,'error'=>$e->getMessage()); }
$result['output']=ob_get_clean();
echo json_encode($result, JSON_INVALID_UTF8_SUBSTITUTE), "\n";
