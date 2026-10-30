<?php
if (PHP_SAPI!=='cli' || !getenv('U5CMS_TEST_PORT')) {http_response_code(404);exit;}
require dirname(__DIR__).'/mysql.php';require dirname(__DIR__).'/u5admin/resource-rename.inc.php';
$settings=json_decode($argv[1],true,512,JSON_THROW_ON_ERROR);
if (!preg_match('/^u5rename_[a-f0-9]+$/D',$settings['database'])) throw new RuntimeException('Not a disposable rename database.');
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '');mysql_select_db($settings['database']);u5CodeMigrationQuery('SET NAMES latin1');
function u5CodeMigrationTestCheckpoint($phase) {
    global $settings;
    if ($phase!==$settings['phase']) return;
    echo "KILL CHECKPOINT\n";fflush(STDOUT);
    // The parent terminates this process with SIGKILL. No shutdown/finally handler can run.
    while (true) usleep(10000);
}
u5ResourceRename($settings['root'],'demo','newdemo',$settings['options']);
throw new RuntimeException('The requested kill checkpoint was not reached.');
