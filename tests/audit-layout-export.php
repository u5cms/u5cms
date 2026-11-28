<?php
// Exercise the installed exporter against the same disposable database at each stage.
$root = realpath($argv[1]);
$stage = $argv[2];
chdir($root);
require 'mysql.php';
require 'config.php';
mysql_connect($host, $username, $password);
mysql_select_db($db);
if (!mysql_query('SET NAMES latin1')) throw new RuntimeException(mysql_error());
$typed = is_file($root.'/u5admin/codeassets-migrate.inc.php');
if ($typed) {
    require_once $root.'/u5admin/codeassets-migrate.inc.php';
    u5CodeAssetsMigrate($root);
}
$name = $typed ? u5CodeAssetName('csslayout', $root) : 'csslayout';
$where = "typ='c' AND deleted!=1 AND name='".mysql_real_escape_string($name)."'";
$row = mysql_query("SELECT content_1 FROM resources WHERE $where");
if (!$row || mysql_num_rows($row)!==1) throw new RuntimeException('Expected a single installed layout');
$source = mysql_fetch_array($row)['content_1'];
$rule = <<<'CSS'

#u5auditcss { color: rgb(11, 22, 33); width: 193px; }
#u5auditcss::after { content: "Gen\e8 ve"; }
@media (max-width: 700px) { #u5auditcss { width: 87px; } }
CSS;
if ($stage==='original') {
    $source .= $rule;
} elseif (!str_contains($source, $rule)) {
    throw new RuntimeException('The existing rules did not survive the installation change');
}
if ($stage==='edit') $source = str_replace('rgb(11, 22, 33)', 'rgb(44, 55, 66)', $source);
if ($stage!=='upgrade' && !mysql_query("UPDATE resources SET content_1='".mysql_real_escape_string($source)."' WHERE $where")) throw new RuntimeException(mysql_error());
chdir($root.'/u5admin');
ob_start();
require 'putcss.inc.php';
$output = ob_get_clean();
if (str_contains($output, 'PROBLEM:') || str_contains($output,'Query failed')) throw new RuntimeException('Export failed');
$base = $typed ? u5CodeAssetPublicPath('cssbase','r/',$root) : 'r/cssbase.css';
$layout = $typed ? u5CodeAssetPublicPath('csslayout','r/',$root) : 'r/csslayout.css';
echo json_encode(['base'=>$base,'layout'=>$layout], JSON_THROW_ON_ERROR);
