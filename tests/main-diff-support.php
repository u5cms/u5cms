<?php
/* Test-only setup for disposable installations created by codeassets-web-regression.py. */
$root=$argv[1];$action=$argv[2];
$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';$_SERVER['REMOTE_ADDR']='127.0.0.1';
require $root.'/config.php';require_once $root.'/mysql.php';
if (!preg_match('/^u5asset_web_[a-f0-9]+$/D',$db)) throw new RuntimeException('This helper only operates on its disposable test database.');
ini_set('mysqli.default_port',getenv('U5CMS_TEST_PORT'));
if (!mysql_connect($host,$username,$password) || !mysql_select_db($db)) throw new RuntimeException('Test fixture connection failed.');
function mainFixtureQuery($sql) { $result=mysql_query($sql);if ($result===false) throw new RuntimeException(mysql_error());return $result; }
if ($action==='view') mainFixtureQuery('CREATE VIEW audit_backup_view AS SELECT 1 AS n');
elseif ($action==='nonadmin' || $action==='admin') mainFixtureQuery("UPDATE accounts SET accadmin=".($action==='admin' ? 1 : 0)." WHERE email='auditadmin'");
elseif ($action==='drop-view') mainFixtureQuery('DROP VIEW IF EXISTS audit_backup_view');
elseif ($action==='drop-large') mainFixtureQuery('DROP TABLE IF EXISTS audit_backup_large');
elseif ($action==='large') {
    mainFixtureQuery('CREATE TABLE audit_backup_large(a MEDIUMTEXT,b MEDIUMTEXT,c MEDIUMTEXT)');
    mainFixtureQuery("INSERT INTO audit_backup_large VALUES(REPEAT('a',8*1024*1024),REPEAT('b',8*1024*1024),REPEAT('c',8*1024*1024))");
}
elseif ($action==='scroll') {
    $names=array();
    for ($number=1;$number<=60;$number++) {
        $name='auditscroll'.sprintf('%03d',$number);$names[]=$name;
        $body='<p>Scroll fixture '.$number.'</p><img loading="lazy" height="350" src="r/auditprivate/portrait.jpg">';
        mainFixtureQuery("INSERT IGNORE INTO resources(name,typ,deleted,content_1,title_1,lastmut) VALUES('".$name."','p',0,'".mysql_real_escape_string($body)."','preview',1)");
    }
    mainFixtureQuery("INSERT IGNORE INTO resources(name,typ,deleted,content_1) VALUES('auditscrollparent','p',0,'".mysql_real_escape_string('['.implode('] [',$names).']')."')");
    echo json_encode($names);
}
elseif ($action==='cache') {
    $hash='{SHA}'.base64_encode(sha1('audit-password',true));
    foreach (array('r/auditprivate','fileversions','fileversions/_dbbackup','fileversions/useruploads') as $directory) {
        if (!is_dir($root.'/'.$directory)) mkdir($root.'/'.$directory,0700,true);
        if ($directory==='r/auditprivate' || $directory==='fileversions') file_put_contents($root.'/'.$directory.'/.htpasswd','auditadmin:'.$hash."\n");
        file_put_contents($root.'/'.$directory.'/auditsecret.txt','private fixture bytes');touch($root.'/'.$directory.'/auditsecret.txt',1700000000);
    }
    foreach (array('portrait'=>array(40,80),'landscape'=>array(80,40),'square'=>array(40,40)) as $name=>$size) {
        $image=imagecreatetruecolor($size[0],$size[1]);imagefill($image,0,0,imagecolorallocate($image,20,180,60));
        imagejpeg($image,$root.'/r/auditprivate/'.$name.'.jpg');imagedestroy($image);
    }
    $cookies=json_decode($argv[3] ?? '{}',true);$identity=($cookies['u'] ?? '').($cookies['p'] ?? '');
    if ($sticksessiontoip==='yes') $identity='127.0.0.1'.$identity;
    echo sha1($mymail.$host.$username.$password.$db.$identity.'auditsecret.txt'.date('Ymd')).'_URL_NON_TRANSFERABLE_AND_TIME_LIMITED';
} else throw new RuntimeException('Unknown test fixture action.');
