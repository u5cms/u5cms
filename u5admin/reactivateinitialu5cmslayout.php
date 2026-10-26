<?php
die('TO RESET CSS TO THE ORIGINAL TEMPLATES, REMOVE THE die() IN youru5cmsinstallation/u5admin/reactivateinitialu5cmslayout.php');
if($_GET['iswear']!='iknowthatthisoverwritesthecurrentlayout')die('TO OVERWRITE THE CURRENT LAYOUT WITH THE INIITIAL U5CMS LAYOUT, ADD ON THE PAGE AT HAND THE GET PARAMETER iswear TO THE URL WITH THE VALUE iknowthatthisoverwritesthecurrentlayout');
require_once ('connect.inc.php');

require_once('codeassets-migrate.inc.php');
if (($cssRqHIADRI ?? 'no')!='no') require('accadmin.inc.php');
$u5ResetRelease=u5CodeMigrationLock(dirname(__DIR__));
try {
    u5CodeMigrationHistorySchema();
    $u5ResetRoot=dirname(__DIR__);$u5ResetColumns=u5CodeMigrationColumns('resources');
    $u5ResetCharset=u5CodeMigrationCharset($u5ResetColumns['content_1']);
    $u5ResetReferences=array();$u5ResetChanges=u5CodeMigrationStartChanges();
    foreach (u5CodeAssetDefaults() as $legacy=>$typed) {
        $target=u5CodeAssetName($legacy,$u5ResetRoot);
        $u5ResetReferences[$legacy]=$target;$u5ResetReferences[$typed]=$target;
    }
    try {
        foreach (u5CodeAssetDefaults() as $legacy=>$typed) {
            $target=u5CodeAssetName($legacy,$u5ResetRoot);$path=$u5ResetRoot.'/r/'.$target.'.tpl';
            if (!is_file($path)) continue;
            $body=u5CodeAssetReferences(file_get_contents($path),$u5ResetReferences,0,u5CodeAssetFormat($target));
            $content=u5CodeMigrationFileText($body,$u5ResetCharset);
            $query=u5CodeMigrationSelect($u5ResetColumns);
            $rows=u5CodeMigrationRows('SELECT '.$query['select'].' FROM resources WHERE name='.u5CodeMigrationValue($target)." AND typ='c' AND deleted!=1");
            foreach ($rows as $before) {
                $after=$before;$after['content_1']=$content;u5CodeMigrationRevision($before,$after);
                u5CodeMigrationValidateRow('resources',$before,$after,$u5ResetColumns);
                if ($before!==$after) u5CodeMigrationQueueChange($u5ResetRoot,$u5ResetChanges,array('table'=>'resources','before'=>$before,'after'=>$after));
            }
        }
        $u5ResetChanges=u5CodeMigrationFinishChanges($u5ResetRoot,$u5ResetChanges);
    } catch (Throwable $error) { u5CodeMigrationAbortChanges($u5ResetRoot,$u5ResetChanges);throw $error; }
    u5CodeMigrationInitializeHistory();
    u5CodeMigrationApplyChanges($u5ResetRoot,$u5ResetChanges);
    u5CodeMigrationInitializeHistory();
    u5CodeAssetExport($u5ResetRoot);
    echo '<p>Layout reset completed. Existing reset templates were used; earlier contents are preserved in text versions and guarded row backups.</p>';
} finally { $u5ResetRelease(); }
?>
