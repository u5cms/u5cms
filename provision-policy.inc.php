<?php
// Condense cycles so inherited permissions depend on external references,
// not on the order in which two mutually referring resources were last saved.
function u5ProvisionComponents($edges) {
    $order=array();$seen=array();$incoming=array();
    foreach ($edges as $node=>$parents) foreach ($parents as $parent) $incoming[$parent][]=$node;
    foreach (array_keys($edges) as $start) {
        if (isset($seen[$start])) continue;
        $stack=array(array($start,false));
        while ($stack) {
            list($node,$done)=array_pop($stack);
            if ($done) {$order[]=$node;continue;}
            if (isset($seen[$node])) continue;
            $seen[$node]=true;$stack[]=array($node,true);
            foreach ($edges[$node] as $parent) if (!isset($seen[$parent])) $stack[]=array($parent,false);
        }
    }
    $components=array();$membership=array();
    foreach (array_reverse($order) as $start) {
        if (isset($membership[$start])) continue;
        $id=count($components);$stack=array($start);$group=array();
        while ($stack) {
            $node=array_pop($stack);
            if (isset($membership[$node])) continue;
            $membership[$node]=$id;$group[]=$node;
            foreach ($incoming[$node] ?? array() as $child) $stack[]=$child;
        }
        $components[$id]=$group;
    }
    return array($components,$membership);
}

function u5ProvisionWasPrivate($root,$name) {
    $dir=u5ProvisionMediaDir($root,(string)$name);
    $guard=$dir.'/.u5-provision.php';
    if (is_file($guard)) return u5ProvisionRead($guard)['private'];
    return is_file($dir.'/.htpasswd') || is_file($dir.'/.htaccess');
}

function u5ProvisionPolicies($root,$selected,$reverse,$ambiguous,$partial) {
    $refs=array();$edges=array();
    foreach ($selected as $name=>$type) {
        $candidates=!$partial && preg_match('/^[a-z0-9!]+$/iD',(string)$name)
            ? array_unique(array_merge($reverse[strtolower((string)$name)] ?? array(),$ambiguous)) : null;
        $refs[$name]=u5ProvisionUses((string)$name,$candidates);
        $edges[$name]=array();
        foreach ($refs[$name] as $row) if (!in_array($row['typ'],array('p','c','s'),true) && isset($selected[$row['name']])) $edges[$name][]=$row['name'];
    }
    list($components,$membership)=u5ProvisionComponents($edges);
    $policies=array();
    foreach (array_reverse($components,true) as $id=>$group) {
        $uses=array();
        foreach ($group as $name) foreach ($refs[$name] as $row) {
            $parent=$row['name'];
            if (!in_array($row['typ'],array('p','c','s'),true) && isset($membership[$parent])) {
                if ($membership[$parent]===$id) continue;
                $inherited=$policies[$parent];
                $row['restricted']=!$inherited['public'];
                $row['logins']=$inherited['logins'];
                $row['hidden']=$inherited['hidden'] ?? $row['hidden'];
            }
            $uses[]=$row;
        }
        $policy=u5ProvisionPolicy($uses);
        if (!$uses) {
            $policy['public']=true;
            foreach ($group as $name) if (u5ProvisionWasPrivate($root,$name)) $policy['public']=false;
            if (count($group)>1) $policy['used']=true;
        }
        foreach ($group as $name) $policies[$name]=$policy;
    }
    return $policies;
}

// The poster may also be referenced in its own right. Neither relationship
// should expose it to an audience excluded by the other.
function u5ProvisionPosterPolicy($video,$poster) {
    if (!$poster['used']) return $video;
    if ($video['public']) return $poster;
    if ($poster['public']) return $video;
    $entries=function($logins) {
        $pairs=array();
        foreach (explode(';',$logins) as $pair) if (trim($pair)!=='') $pairs[trim($pair)]=true;
        return $pairs;
    };
    $permitted=array_intersect_key($entries($video['logins']),$entries($poster['logins']));
    $video['used']=true;
    $video['logins']=$permitted ? implode(";\r\n",array_keys($permitted)).";\r\n" : '';
    return $video;
}
