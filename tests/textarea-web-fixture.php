<?php
/* Only disposable fixtures created by codeassets-web-regression.py. */
require dirname(__DIR__).'/mysql.php';
$_SERVER['REQUEST_URI']='/fixture';$_SERVER['HTTP_HOST']='127.0.0.1';
$root=$argv[1];include $root.'/config.php';
if (!preg_match('/^u5asset_web_[a-f0-9]+$/D',$db)) throw new RuntimeException('A disposable fixture database is required.');
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
mysql_connect($host,$username,$password);mysql_select_db($db);mysql_query('SET NAMES latin1');
foreach (array('textareatest'=>0,'textareatarget'=>0,'textareaarchive'=>2) as $name=>$deleted) {
    $columns=array('name','typ','deleted','hidden','lastmut');$values=array("'$name'","'p'",$deleted,0,1);
    foreach (range(1,5) as $l) {
        $columns[]='title_'.$l;$values[]="'Textarea fixture $l'";
        $columns[]='content_'.$l;$values[]="'Lead sample text [textareatarget] [\$\$\$:textareatarget] [label:textareatarget] [:label:textareatarget] [accent!js] tail'";
    }
    if (!mysql_query('INSERT INTO resources (`'.implode('`,`',$columns).'`) VALUES ('.implode(',',$values).')')) throw new RuntimeException(mysql_error());
}
if (!mysql_query("INSERT INTO resources(name,typ,deleted,hidden) VALUES('textareadoc','d',0,0)")) throw new RuntimeException(mysql_error());
mkdir($root.'/r/textareadoc',0700,true);file_put_contents($root.'/r/textareadoc/textareadoc_de.txt','original document');
if (!mysql_query("INSERT INTO accounts(email,pw,accadmin) VALUES('textareauser','{SHA}".base64_encode(sha1('textarea-password',true))."',0)")) throw new RuntimeException(mysql_error());
echo "Textarea fixtures created.\n";
