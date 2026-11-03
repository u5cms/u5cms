<?php
/* Execute the actual trusted-config detector blocks without opening a provider. */
$root=dirname(__DIR__);$checks=0;
$profiles=array(
    array('return true;',true),array('return false;',false),
    array('return 1;',true),array('return 0;',false),
    array('return "1";',true),array('return "0";',false),
    array('return "on";',true),array('return "";',false),
    array('$httpsisinuse=true;',true),array('$httpsisinuse=false;',false),
    array('$httpsisinuse=1;',true),array('$httpsisinuse="on";',true),
    array('$httpsisinuse=true;return false;',false),
    array('$httpsisinuse=true;return null;',true)
);
function httpsDetectionValue($block,$configuration) {
    $evaluateifhttpsisinuse=$configuration;
    eval($block);
    return (bool)$httpsisinuse;
}
$requiredRoutes=json_decode(getenv('U5CMS_HTTPS_ROUTES') ?: '["connect.inc.php","u5admin/connect.inc.php"]',true);
if (!is_array($requiredRoutes)) throw new RuntimeException('Invalid required HTTPS routes.');
foreach ($requiredRoutes as $route) if (!is_file($root.'/'.$route)) throw new RuntimeException('Required HTTPS route missing: '.$route);
foreach (array('connect.inc.php','u5admin/connect.inc.php','saml/login.php','oidc/bootstrap.inc.php') as $route) {
    $path=$root.'/'.$route;if (!is_file($path)) continue;
    $source=file_get_contents($path);
    $pattern=$route==='oidc/bootstrap.inc.php'
        ? '/\$httpsisinuse\s*=\s*false;.*?(?=\s*if\s*\(!\$httpsisinuse\))/s'
        : '/\$httpsisinuse\s*=\s*false;.*?\$httpsisinuse\s*=\s*\(bool\)\s*\$httpsisinuse;/s';
    if (!preg_match($pattern,$source,$match)) {
        if (in_array($route,$requiredRoutes,true) || $route==='oidc/bootstrap.inc.php' || strpos($source,'$evaluateifhttpsisinuse')!==false) throw new RuntimeException('HTTPS detector not found: '.$route);
        continue;
    }
    foreach ($profiles as $profile) {
        $checks++;
        if (httpsDetectionValue($match[0],$profile[0])!==$profile[1]) throw new RuntimeException('HTTPS configuration contract failed: '.$route.' / '.$profile[0]);
    }
}
echo 'HTTPS configuration contracts: '.$checks." passed.\n";
