<?php
/* Schema checks run before any source or reference is changed. */
function u5CodeMigrationDdlClauses($sql) {
    $clauses=array();$start=0;$depth=0;$quote=null;
    for ($index=0,$length=strlen($sql);$index<$length;$index++) {
        $char=$sql[$index];
        if ($quote!==null) {
            if ($char==='\\') $index++;
            elseif ($char===$quote) { if (($sql[$index+1] ?? null)===$quote) $index++;else $quote=null; }
            continue;
        }
        if ($char==='"' || $char==="'" || $char==='`') $quote=$char;
        elseif ($char==='(') $depth++;
        elseif ($char===')') $depth--;
        elseif ($char===',' && $depth===0) { $clauses[]=trim(substr($sql,$start,$index-$start));$start=$index+1; }
    }
    if ($quote!==null || $depth!==0) throw new RuntimeException('Cannot parse the legacy schema change safely.');
    $clauses[]=trim(substr($sql,$start));return $clauses;
}

function u5CodeMigrationLegacyQuery($sql) {
    if (preg_match('/^\s*SET\s+storage_engine\s*=/i',$sql)) $sql=preg_replace('/storage_engine/i','default_storage_engine',$sql,1);
    // MariaDB's conditional ALTER clauses are not accepted by MySQL. Check metadata first.
    $identifier='(`?[a-zA-Z0-9_]+`?)';
    if (stripos($sql,'IF ')!==false && preg_match('/^\s*ALTER\s+TABLE\s+'.$identifier.'\s+(.+?)\s*;?\s*$/isD',$sql,$alter)) {
        $table=trim($alter[1],'`');
        foreach (u5CodeMigrationDdlClauses(rtrim($alter[2],"; \t\r\n")) as $clause) {
            if (preg_match('/^ADD\s+(?:UNIQUE\s+)?(?:KEY|INDEX)\s+IF\s+NOT\s+EXISTS\s+'.$identifier.'/i',$clause,$match)) {
                $found=false;foreach (u5CodeMigrationRows('SHOW INDEX FROM '.u5CodeMigrationIdentifier($table)) as $index) if (strcasecmp($index['Key_name'],trim($match[1],'`'))===0) $found=true;
                if ($found) continue;
            } elseif (preg_match('/^ADD\s+PRIMARY\s+KEY\s+IF\s+NOT\s+EXISTS\b/i',$clause)) {
                $found=false;foreach (u5CodeMigrationRows('SHOW INDEX FROM '.u5CodeMigrationIdentifier($table)) as $index) if ($index['Key_name']==='PRIMARY') $found=true;
                if ($found) continue;
            } elseif (preg_match('/^ADD\s+(?:COLUMN\s+)?IF\s+NOT\s+EXISTS\s+'.$identifier.'/i',$clause,$match)) {
                $columns=array_change_key_case(u5CodeMigrationColumns($table),CASE_LOWER);
                if (isset($columns[strtolower(trim($match[1],'`'))])) continue;
            } elseif (preg_match('/^CHANGE\s+(?:COLUMN\s+)?IF\s+EXISTS\s+'.$identifier.'\s+'.$identifier.'/i',$clause,$match)) {
                $columns=array_change_key_case(u5CodeMigrationColumns($table),CASE_LOWER);$old=strtolower(trim($match[1],'`'));$new=strtolower(trim($match[2],'`'));
                if (!isset($columns[$old])) continue;
                if ($old!==$new && isset($columns[$new])) throw new RuntimeException('Both old and new language columns exist in '.$table.'. Reconcile them before retrying the update.');
            }
            $portable=preg_replace('/\bIF\s+(?:NOT\s+)?EXISTS\s*/i','',$clause,1);
            u5CodeMigrationQuery('ALTER TABLE '.u5CodeMigrationIdentifier($table).' '.$portable);
        }
        return true;
    }
    return mysql_query($sql);
}

function u5CodeMigrationValidateRow($table,$before,$after,$columns) {
    foreach ($after as $field=>$value) {
        if (!isset($columns[$field]) || $value===($before[$field] ?? null)) continue;
        $column=$columns[$field];$type=strtolower($column['Type']);
        if ($value!==null && preg_match('/^(tinyint|smallint|mediumint|int)\b/',$type,$integer)) {
            $bits=array('tinyint'=>8,'smallint'=>16,'mediumint'=>24,'int'=>32);$unsigned=strpos($type,'unsigned')!==false;
            $maximum=pow(2,$bits[$integer[1]]-($unsigned ? 0 : 1))-1;$minimum=$unsigned ? 0 : -$maximum-1;
            if (!preg_match('/^-?[0-9]+$/D',(string)$value) || $value<$minimum || $value>$maximum) throw new RuntimeException('Updated revision/value exceeds the integer capacity of '.$table.'.'.$field.'. Enlarge that column and retry; no source rows have been changed.');
        }
        if (!is_string($value)) continue;
        if (preg_match('/^(enum|set)\(/',$type)) throw new RuntimeException('Cannot safely rewrite '.$table.'.'.$field.' inside its ENUM/SET definition. Adapt that definition first; no rows have been changed.');
        $bytes=strlen($value);$limit=null;
        if (preg_match('/^(char|varchar)\((\d+)\)/',$type,$match)) {
            $length=$bytes;$charset=u5CodeMigrationCharset($column);
            if (strpos($charset,'utf8')===0) {
                $length=preg_match_all('/./us',$value,$unused);
                if ($length===false) throw new RuntimeException('Invalid UTF-8 in '.$table.'.'.$field.'. No rows have been changed.');
            }
            if ($length>(int)$match[2]) throw new RuntimeException('Rewritten reference exceeds the capacity of '.$table.'.'.$field.'. Enlarge that column and retry; no rows have been changed.');
        } elseif (preg_match('/^(tinytext|text|mediumtext|longtext)\b/',$type,$match)) {
            $limits=array('tinytext'=>255,'text'=>65535,'mediumtext'=>16777215,'longtext'=>4294967295);$limit=$limits[$match[1]];
        }
        if ($limit!==null && $bytes>$limit) throw new RuntimeException('Rewritten reference exceeds the byte capacity of '.$table.'.'.$field.'. No rows have been changed.');
    }
}

function u5CodeMigrationHistorySchema() {
    $source=u5CodeMigrationColumns('resources');$history=u5CodeMigrationColumns('resources_log');$changes=array();
    foreach ($history as $name=>$column) {
        if (!isset($source[$name])) continue;
        $type=$column['Type'];$charset=u5CodeMigrationCharset($column);$newCharset=$charset;
        if (preg_match('/^varchar\((\d+)\)$/i',$type,$old) && preg_match('/^varchar\((\d+)\)$/i',$source[$name]['Type'],$new) && $new[1]>$old[1]) $type=$source[$name]['Type'];
        $textSizes=array('tinytext'=>1,'text'=>2,'mediumtext'=>3,'longtext'=>4);
        if (isset($textSizes[strtolower($type)],$textSizes[strtolower($source[$name]['Type'])]) && $textSizes[strtolower($source[$name]['Type'])]>$textSizes[strtolower($type)]) $type=$source[$name]['Type'];
        $sourceCharset=u5CodeMigrationCharset($source[$name]);
        if ($sourceCharset!==$charset && !in_array($sourceCharset,array('binary','ascii','latin1'),true) && $charset!=='utf8mb4') $newCharset='utf8mb4';
        if ($type!==$column['Type'] || $newCharset!==$charset) $changes[$name]=array('type'=>$type,'charset'=>$newCharset,'column'=>$column);
    }
    if ($changes) {
        // Read the original definitions as UTF-8 so defaults and comments survive verbatim.
        $session=u5CodeMigrationRows('SELECT @@character_set_client AS client,@@character_set_results AS results,@@collation_connection AS connection')[0];
        try {
            u5CodeMigrationQuery('SET NAMES utf8mb4');
            $create=array_values(u5CodeMigrationRows('SHOW CREATE TABLE resources_log')[0])[1];$alter=array();
            foreach ($changes as $name=>$change) {
                if (!preg_match('/^\s*'.preg_quote(u5CodeMigrationIdentifier($name),'/').' (.+?)(?:,)?$/m',$create,$match)) throw new RuntimeException('Cannot preserve the history column definition for '.$name.'.');
                $definition=rtrim($match[1],',');$oldType=$change['column']['Type'];
                if (strncasecmp($definition,$oldType,strlen($oldType))!==0) throw new RuntimeException('Unexpected history column definition for '.$name.'.');
                $definition=$change['type'].substr($definition,strlen($oldType));
                if ($change['charset']!==u5CodeMigrationCharset($change['column'])) {
                    $definition=preg_replace('/\s+CHARACTER SET\s+\w+(?:\s+COLLATE\s+\w+)?/i','',$definition);
                    $definition=substr_replace($definition,' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',strlen($change['type']),0);
                }
                $alter[]='MODIFY COLUMN '.u5CodeMigrationIdentifier($name).' '.$definition;
            }
            u5CodeMigrationQuery('ALTER TABLE resources_log '.implode(',',$alter));
        } finally {
            u5CodeMigrationQuery('SET character_set_client='.u5CodeMigrationValue($session['client']).', character_set_results='.u5CodeMigrationValue($session['results']).', collation_connection='.u5CodeMigrationValue($session['connection']));
        }
    }
    $indexes=u5CodeMigrationRows('SHOW INDEX FROM resources_log');$hasNameIndex=false;
    foreach ($indexes as $index) if ((int)$index['Seq_in_index']===1 && $index['Column_name']==='name') $hasNameIndex=true;
    if (!$hasNameIndex) u5CodeMigrationQuery('ALTER TABLE resources_log ADD INDEX u5_code_history_lookup (name(150),lastmut,deleted)');
}

function u5CodeMigrationInitializeHistory() {
    $source=u5CodeMigrationColumns('resources');$history=u5CodeMigrationColumns('resources_log');$fields=array();$values=array();$identity=array();
    foreach ($history as $name=>$column) if (isset($source[$name])) {
        $field=u5CodeMigrationIdentifier($name);$fields[]=$field;$values[]='r.'.$field;
    }
    foreach (array('name','lastmut','deleted') as $name) if (isset($source[$name],$history[$name])) {
        $field=u5CodeMigrationIdentifier($name);$identity[]='h.'.$field.' <=> r.'.$field;
        if ($name==='name') $identity[]='BINARY h.'.$field.' <=> BINARY r.'.$field;
    }
    if (count($identity)!==4) throw new RuntimeException('The resource history identity is incomplete.');
    u5CodeMigrationQuery('INSERT INTO resources_log ('.implode(',',$fields).') SELECT '.implode(',',$values).' FROM resources r WHERE NOT EXISTS (SELECT 1 FROM resources_log h WHERE '.implode(' AND ',$identity).')');
}

function u5CodeMigrationRevision($before,&$after) {
    if ($before===$after) return;
    if (array_key_exists('lastmut',$before)) $after['lastmut']=(string)max(time(),(int)$before['lastmut']+1);
    if (array_key_exists('operator',$before)) $after['operator']='u5CMS 12.11.1';
    foreach (range(1,5) as $language) if (array_key_exists('search_'.$language,$before)) $after['search_'.$language]=null;
}
