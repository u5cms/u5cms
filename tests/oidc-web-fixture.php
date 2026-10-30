<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Disposable test database only. Never place this file in a production document root.
require dirname(__DIR__).'/mysql.php';
require dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php';
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
if (!mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '')) throw new RuntimeException('Test database unavailable.');
if (in_array($argv[1] ?? '',array('cleanup','revoke-backend','restore-backend','revoke-member','restore-member','snapshot','members','bindings'),true)) {
    $name=$argv[2] ?? '';
    if (!preg_match('/^u5oidc_web_[a-f0-9]+$/D',$name)) throw new RuntimeException('Invalid test database.');
    if ($argv[1] === 'cleanup') u5CodeMigrationQuery('DROP DATABASE `'.$name.'`');
    else {
        mysql_select_db($name);
        if ($argv[1] === 'revoke-backend') u5CodeMigrationQuery("DELETE FROM accounts WHERE email='editor@example.org'");
        elseif ($argv[1] === 'restore-backend') u5CodeMigrationQuery("INSERT IGNORE INTO accounts (email,pw,accadmin,hash) VALUES ('editor@example.org','{SHA}".base64_encode(sha1('fixture-password',true))."',0,'fixture-editor')");
        elseif ($argv[1] === 'revoke-member' || $argv[1] === 'restore-member') {
            $logins=$argv[1] === 'revoke-member' ? '?other@example.org:other;' : '?member@example.org:'.(string)floor(crc32('member@example.org1234567')).';';
            u5CodeMigrationQuery('UPDATE resources SET logins='.u5CodeMigrationValue($logins)." WHERE name='!oidcprivate'");
        } elseif ($argv[1] === 'members') {
            echo mb_convert_encoding(mysql_fetch_assoc(mysql_query('SELECT members FROM intranetmembers LIMIT 1'))['members'],'UTF-8','WINDOWS-1252');
        } elseif ($argv[1] === 'bindings') {
            $rows=array();$r=mysql_query('SELECT username FROM u5oidc_bindings ORDER BY username');
            while ($row=mysql_fetch_assoc($r)) $rows[]=mb_convert_encoding($row['username'],'UTF-8','WINDOWS-1252');
            echo json_encode($rows);
        } else {
            $rows=array();$r=mysql_query('SELECT email,pw,accadmin FROM accounts ORDER BY email');
            while ($row=mysql_fetch_assoc($r)) $rows[]=$row;
            echo json_encode($rows);
        }
    }
    exit;
}
$root=$argv[1];$issuer=$argv[2];$base=$argv[3];$ca=$argv[4];
$name='u5oidc_web_'.bin2hex(random_bytes(6));
u5CodeMigrationQuery('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');mysql_select_db($name);
$link=mysql_ensure_link(null);
if (!mysqli_multi_query($link,file_get_contents(dirname(__DIR__).'/u5standard.sql'))) throw new RuntimeException(mysqli_error($link));
do { if ($r=mysqli_store_result($link)) mysqli_free_result($r); } while (mysqli_more_results($link) && mysqli_next_result($link));
if (mysqli_errno($link)) throw new RuntimeException(mysqli_error($link));
u5CodeMigrationQuery('SET NAMES latin1');
$hash='{SHA}'.base64_encode(sha1('fixture-password',true));
u5CodeMigrationQuery("UPDATE accounts SET email='admin@example.org',pw=".u5CodeMigrationValue($hash).',accadmin=1 WHERE id=1');
u5CodeMigrationQuery("INSERT INTO accounts (email,pw,accadmin,hash) VALUES ('editor@example.org',".u5CodeMigrationValue($hash).",0,'fixture-editor')");
u5CodeMigrationQuery('UPDATE intranetsalt SET salt=1234567');
u5CodeMigrationQuery("UPDATE intranetmembers SET members='member@example.org,'");
$memberPassword=(string)floor(crc32('member@example.org1234567'));
foreach (array('!oidcprivate'=>array('?member@example.org:'.$memberPassword.';','PRIVATE-INTRANET'),
    'oidcgroup'=>array('?page@example.org:page-password;','PRIVATE-GROUP'),
    'oidcpublic'=>array('','PUBLIC-OIDC-FIXTURE')) as $page=>$data) {
    $columns=array('name','typ','deleted','hidden','logins');$values=array(u5CodeMigrationValue($page),"'p'",'0','0',u5CodeMigrationValue($data[0]));
    foreach (range(1,5) as $language) {
        $columns[]='content_'.$language;$values[]=u5CodeMigrationValue($data[1]);
        $columns[]='title_'.$language;$values[]=u5CodeMigrationValue($data[1]);
    }
    u5CodeMigrationQuery('INSERT INTO resources (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',$values).')');
}
$config=file_get_contents($root.'/config.php.sample');
$settings=array('host'=>'127.0.0.1','username'=>getenv('U5CMS_TEST_USER') ?: 'root','password'=>getenv('U5CMS_TEST_PASSWORD') ?: '',
    'db'=>$name,'mymail'=>'fixture@example.invalid','usesessioninsteadofbasicauth'=>'yes','sessioncookiehashsalt'=>'fixture-salt');
foreach ($settings as $key=>$value) $config=preg_replace('/\$'.preg_quote($key,'/').'\s*=\s*[\'\"][^\'\"]*[\'\"]\s*;/', '\$'.$key.'='.var_export($value,true).';',$config,1);
$oidc=array('enabled'=>true,'issuer'=>$issuer,'client_id'=>'fixture-client','client_secret'=>'fixture-secret',
    'redirect_uri'=>$base.'/oidc/callback.php','ca_bundle'=>$ca,'provider_logout'=>true,
    'subjects'=>array('admin-sub'=>'admin@example.org','editor-sub'=>'editor@example.org','member-sub'=>'member@example.org',
        'page-sub'=>'page@example.org','no-rights-sub'=>'nobody@example.org'));
$config.="\n\$u5oidc=".var_export($oidc,true).";\n\$blockautoincluderefresherforhtaccessandindex='yes';\n\$backup_send_mail=false;\n";
file_put_contents($root.'/config.php',$config);
u5CodeAssetsMigrate($root);
@mkdir($root.'/r/oidc-files');
file_put_contents($root.'/r/oidc-files/.htpasswd','member@example.org:'.$hash."\n");
file_put_contents($root.'/r/oidc-files/private.txt','PRIVATE-DOWNLOAD');
// A known-good PNG is sufficient to exercise the real thumbnail authorisation gate.
file_put_contents($root.'/r/oidc-files/private.png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jX9sAAAAASUVORK5CYII='));
file_put_contents($root.'/oidc-form-probe.php',"<?php require __DIR__.'/loginformsave.inc.php';echo 'FORM-GATE-PASSED';");
file_put_contents($root.'/oidc-upload-probe.php',"<?php error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);require __DIR__.'/getauthuser.inc.php';echo 'UPLOAD-USER:'.\$_SERVER['PHP_AUTH_USER'];");
$fingerprint=$settings['mymail'].$settings['host'].$settings['username'].$settings['password'].$name;
echo json_encode(array('database'=>$name,'legacy_cookie'=>sha1('fixture-salt'.$fingerprint.$hash)));
