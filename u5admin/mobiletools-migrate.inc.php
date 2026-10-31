<?php
require_once __DIR__.'/mobiletools-source.inc.php';

function u5MobileToolsHistoryRow($row,$sourceColumns) {
    $columns=u5CodeMigrationColumns('resources_log');
    foreach ($row as $field=>&$value) {
        if (!is_string($value) || !isset($columns[$field],$sourceColumns[$field])) continue;
        $from=u5CodeMigrationCharset($sourceColumns[$field]);$to=u5CodeMigrationCharset($columns[$field]);
        if ($from===$to || $from==='binary' || $to==='binary') continue;
        foreach (array($from,$to) as $charset) if (!preg_match('/^[a-z0-9_]+$/iD',$charset)) throw new RuntimeException('Invalid mobile history charset.');
        $expression='CONVERT(CONVERT('.u5CodeMigrationValue($value).' USING '.$from.') USING '.$to.')';
        $converted=u5CodeMigrationRows('SELECT CAST('.$expression.' AS BINARY) AS content, CAST(CONVERT('.$expression.' USING '.$from.') AS BINARY) AS original')[0];
        if ($converted['original']!==$value) throw new RuntimeException('The mobile source cannot be preserved in history without loss. Adapt the history charset and retry.');
        $value=$converted['content'];
    }
    unset($value);return $row;
}

/* Runs under the existing CMS mutation lock, after any old immutable plan finishes. */
function u5MobileToolsMigrate($root) {
    $base=$root.'/fileversions/mobiletools-2026-10-31';
    $pending=$base.'.pending.php';$done=$base.'.done.php';
    if (is_file($done) && !is_file($pending)) return;
    if (is_file($pending)) {
        $bytes=file_get_contents($pending);$prefix="<?php exit; __halt_compiler();\n";
        if (substr($bytes,0,strlen($prefix))!==$prefix) throw new RuntimeException('Invalid mobile controls checkpoint.');
        $plan=unserialize(substr($bytes,strlen($prefix)),array('allowed_classes'=>false));
        if (!is_array($plan) || ($plan['version'] ?? '')!=='2026-10-31') throw new RuntimeException('Invalid mobile controls checkpoint.');
    } else {
        $name=u5CodeAssetName('jsmobilespecific',$root);$columns=u5CodeMigrationColumns('resources');$select=array();
        foreach ($columns as $field=>$column) {
            if (preg_match('/(?:VIRTUAL|STORED|PERSISTENT) GENERATED|on update/i',$column['Extra'] ?? '')) continue;
            $select[]='CAST('.u5CodeMigrationIdentifier($field).' AS BINARY) AS '.u5CodeMigrationIdentifier($field);
        }
        $rows=u5CodeMigrationRows('SELECT '.implode(',',$select).' FROM resources WHERE name='.u5CodeMigrationValue($name)." AND typ='c' AND deleted!=1");
        $plan=array('version'=>'2026-10-31','rows'=>array(),'templates'=>array());
        foreach ($rows as $before) {
            $after=$before;
            foreach (range(1,5) as $language) {
                $field='content_'.$language;
                if (!isset($before[$field]) || trim($before[$field])==='') continue;
                $after[$field]=u5MobileToolsAppend($before[$field]);
                if (function_exists('u5CodeMigrationValidateRow')) u5CodeMigrationValidateRow('resources',$before,$after,$columns);
            }
            if ($after!==$before) $plan['rows'][]=array('table'=>'resources','before'=>$before,'after'=>$after);
        }
        $relative='r/'.$name.'.tpl';
        if (is_file($root.'/'.$relative)) {
            $before=file_get_contents($root.'/'.$relative);$after=u5MobileToolsAppend($before);
            if ($after!==$before) $plan['templates'][]=array('path'=>$relative,'before'=>$before,'after'=>$after);
        }
        u5CodeAssetAtomicWrite($pending,"<?php exit; __halt_compiler();\n".serialize($plan));
    }
    u5CodeMigrationCheckpoint('mobiletools:planned');
    foreach ($plan['templates'] as $file) {
        if (!preg_match('~^r/[a-zA-Z0-9_!.-]+\.tpl$~D',$file['path'])) throw new RuntimeException('Invalid mobile controls template path.');
        $current=@file_get_contents($root.'/'.$file['path']);
        if ($current!==$file['before'] && $current!==$file['after']) throw new RuntimeException('The mobile reset template changed during the update. Its contents and checkpoint were kept.');
    }
    $columns=u5CodeMigrationColumns('resources');
    foreach ($plan['rows'] as $change) {
        u5CodeMigrationInsert('resources_log',u5MobileToolsHistoryRow($change['before'],$columns));
        u5CodeMigrationApplyRow($change);
        u5CodeMigrationInsert('resources_log',u5MobileToolsHistoryRow($change['after'],$columns));
    }
    u5CodeMigrationCheckpoint('mobiletools:database');
    foreach ($plan['templates'] as $file) {
        u5CodeMigrationBackupFile($root,$file['path']);
        u5CodeAssetAtomicWrite($root.'/'.$file['path'],$file['after']);
    }
    u5CodeMigrationCheckpoint('mobiletools:templates');
    foreach ($plan['rows'] as $change) u5CodeMigrationBackupFile($root,'r/'.u5CodeAssetFilename($change['after']['name']));
    u5CodeAssetExport($root);
    u5CodeMigrationCheckpoint('mobiletools:exported');
    // The completed guarded plan retains exact before-images for manual rollback.
    u5CodeAssetAtomicWrite($done,"<?php exit; __halt_compiler();\n".serialize($plan));
    if (!@unlink($pending)) throw new RuntimeException('Cannot finalize the mobile controls checkpoint.');
}
