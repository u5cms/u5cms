<?php
// Disposable regression worker: the parent kills this process at a checkpoint.
require dirname(__DIR__).'/mysql.php';
require dirname(__DIR__).'/u5admin/codeassets-migrate.inc.php';
list(,$database,$root,$killPhase,$signal)=$argv;
if (!preg_match('/^u5asset_[a-f0-9]+$/D',$database)) throw new RuntimeException('Invalid test database.');
ini_set('mysqli.default_port',(string)(int)getenv('U5CMS_TEST_PORT'));
mysql_connect('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '');
mysql_select_db($database);
u5CodeMigrationQuery('SET NAMES latin1');
function u5CodeMigrationTestCheckpoint($phase) {
    global $killPhase,$signal;
    if ($phase!==$killPhase) return;
    file_put_contents($signal,$phase);
    while (true) usleep(50000);
}
u5CodeAssetsMigrate($root);
