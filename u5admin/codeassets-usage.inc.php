<?php
require_once dirname(__DIR__).'/codeassets.inc.php';
function u5CodeAssetUsageSql($name) {
    if (!u5CodeAssetIsTyped($name)) return '0';
    $pattern=mysql_real_escape_string(strtolower(u5CodeAssetUsagePattern($name)));
    $escapedSlash=mysql_real_escape_string('\\/');$terms=array();
    foreach (array('content','title','desc','key') as $field) foreach (range(1,5) as $language) {
        $terms[]="LOWER(REPLACE(COALESCE(".$field.'_'.$language.",''),'".$escapedSlash."','/')) REGEXP '".$pattern."'";
    }
    return '('.implode(' OR ',$terms).')';
}
