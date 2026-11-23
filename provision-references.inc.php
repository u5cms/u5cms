<?php
function u5ProvisionQuery($sql) {
    $result=mysql_query($sql);
    if ($result===false) throw new RuntimeException('Provisioning database operation failed.');
    return $result;
}

function u5ProvisionQuote($value) {
    return "'".mysql_real_escape_string((string)$value)."'";
}

function u5ProvisionFields() {
    static $fields=array();
    if (!$fields) foreach (array('content','title','desc','key') as $prefix) {
        for ($i=1;$i<=5;$i++) $fields[]=$prefix.'_'.$i;
    }
    return $fields;
}

function u5ProvisionTokens($row) {
    $names=array();
    $fallback=false;
    $unusual=array();
    foreach (u5ProvisionFields() as $field) {
        $text=str_replace(array('[[[[','[[[','[[',']]]]',']]]',']]'), ' ', (string)($row[$field] ?? ''));
        // Literal bracket escapes are removed in exactly the same order as in SQL.
        if (preg_match('~(?:[\[:][^\]\[?:\s]{0,255}[\x80-\xff]|[\x80-\xff]/|r/[^/\s]{0,255}[\x80-\xff])~i',$text)) $fallback=true;
        // Legacy names may contain punctuation or SQL LIKE wildcards. A
        // change to one of those spellings must survive the fast-path comparison.
        if (preg_match_all('~(?:[\[:]([^\]\[?:]*?)(?=[?\]])|r/([^/\s]+)(?=/))~i',$text,$raw,PREG_SET_ORDER)) {
            foreach ($raw as $piece) {
                $word=$piece[1]!=='' ? $piece[1] : ($piece[2] ?? '');
                if (!preg_match('/^[A-Za-z0-9!]+$/D',$word)) $unusual[$word]=true;
            }
        }
        if (preg_match_all('~(?:[\[:]([a-z0-9!]+)(?=[?\]])|r/([a-z0-9!]+)(?=/))~i',$text,$found,PREG_SET_ORDER)===false) {
            throw new RuntimeException('Cannot read CMS references.');
        }
        foreach ($found as $match) $names[strtolower($match[1]!=='' ? $match[1] : $match[2])]=true;
    }
    ksort($names);
    ksort($unusual);
    return array('names'=>array_keys($names),'fallback'=>$fallback,'legacy'=>hash('sha256',serialize(array_keys($unusual))));
}

function u5ProvisionSummary($row) {
    $summary=u5ProvisionTokens($row);
    $summary['type']=$row['typ'];
    $summary['policy']=hash('sha256',serialize(array((string)$row['logins'],(string)$row['hidden'])));
    return $summary;
}

function u5ProvisionFetchSources($sources=null,$deletedOnly=false) {
    $fields=array();
    // Preserve characters that cannot be represented by the CMS connection's
    // Latin-1 client encoding. SQL rechecks ambiguous tokens using site collation.
    foreach (u5ProvisionFields() as $f) $fields[]='CAST(CONVERT('.$f.' USING utf8mb4) AS BINARY) AS '.$f;
    $where=$deletedOnly ? 'deleted=1' : 'deleted!=1';
    if ($sources!==null) {
        if (!$sources) return array();
        $where.=' AND name IN ('.implode(',',array_map('u5ProvisionQuote',$sources)).')';
    }
    $sql='SELECT name,typ,logins,hidden,'.implode(',',$fields).' FROM resources WHERE '.$where;
    $result=mysql_unbuffered_query($sql);
    if (!$result) throw new RuntimeException('Cannot read resources for provisioning.');
    $rows=array();
    try {
        while ($row=mysql_fetch_assoc($result)) {
            $name=$row['name'];$item=u5ProvisionSummary($row);
            // An active and an archived row can share a name in the composite primary key.
            if (isset($rows[$name])) {
                $item['names']=array_unique(array_merge($item['names'],$rows[$name]['names']));
                $item['fallback']=$item['fallback'] || $rows[$name]['fallback'];
                $item['multiple']=true;
            }
            $rows[$name]=$item;
        }
    } finally {mysql_free_result($result);}
    return $rows;
}

function u5ProvisionMedia() {
    $result=u5ProvisionQuery("SELECT name,typ FROM resources WHERE deleted!=1 AND typ NOT IN ('p','c','s') ORDER BY lastmut DESC,name");
    $media=array();
    while ($row=mysql_fetch_assoc($result)) if ($row['name']!=='') $media[$row['name']]=$row['typ'];
    return $media;
}

function u5ProvisionCandidates($summaries,$media) {
    $targets=array();
    $ambiguous=false;
    foreach ($summaries as $summary) {
        foreach ($summary['names'] as $key) $targets[$key]=true;
        $ambiguous=$ambiguous || $summary['fallback'];
    }
    $selected=array();
    foreach ($media as $name=>$type) {
        if ($ambiguous || isset($targets[strtolower((string)$name)]) || !preg_match('/^[a-z0-9!]+$/iD',(string)$name)) {
            $selected[]=(string)$name;
        }
    }
    return $selected;
}

// SQL is still the authority for collation, wildcard names, escaped brackets
// and five-language legacy data. The reverse map removes unrelated rows.
function u5ProvisionUses($name,$sources=null) {
    if ($sources===array()) return array();
    $conditions=array();
    foreach (u5ProvisionFields() as $f) {
        $value=$f;
        foreach (array('[[[[','[[[','[[',']]]]',']]]',']]') as $escape) {
            $value='REPLACE('.$value.','.u5ProvisionQuote($escape).",' ')";
        }
        foreach (array('%['.$name.']%','%:'.$name.']%','%['.$name.'?%','%:'.$name.'?%','%r/'.$name.'/%') as $pattern) {
            $conditions[]=$value.' LIKE '.u5ProvisionQuote($pattern);
        }
    }
    $rows=array();
    foreach ($sources===null ? array(null) : array_chunk(array_values($sources),128) as $batch) {
        $filter=$batch===null ? '' : ' AND name IN ('.implode(',',array_map('u5ProvisionQuote',$batch)).')';
        $result=u5ProvisionQuery('SELECT name,typ,hidden,logins,lastmut FROM resources WHERE deleted!=1'.$filter.' AND ('.implode(' OR ',$conditions).') ORDER BY lastmut DESC,name');
        while ($row=mysql_fetch_assoc($result)) if ($row['name']!==$name) $rows[]=$row;
    }
    if ($sources!==null && count($sources)>128) usort($rows,function($a,$b) {
        return ($b['lastmut']<=>$a['lastmut']) ?: strcmp($a['name'],$b['name']);
    });
    return $rows;
}
