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
    $httpsisinuse=false;
    $evaluateifhttpsisinuse=$configuration;
    eval($block);
    return (bool)$httpsisinuse;
}
$requiredRoutes=json_decode(getenv('U5CMS_HTTPS_ROUTES') ?: '["connect.inc.php","u5admin/connect.inc.php"]',true);
$legacyRoutes=json_decode(getenv('U5CMS_HTTPS_LEGACY_ROUTES') ?: '[]',true);
if (!is_array($requiredRoutes)) throw new RuntimeException('Invalid required HTTPS routes.');
if (!is_array($legacyRoutes)) throw new RuntimeException('Invalid legacy HTTPS routes.');
foreach (array_merge($requiredRoutes,$legacyRoutes) as $route) if (!is_file($root.'/'.$route)) throw new RuntimeException('Required HTTPS route missing: '.$route);
foreach (array('connect.inc.php','u5admin/connect.inc.php','saml/login.php','oidc/bootstrap.inc.php') as $route) {
    $path=$root.'/'.$route;if (!is_file($path)) continue;
    $source=file_get_contents($path);
    $legacy=in_array($route,$legacyRoutes,true);
    $pattern=$legacy ? ($route==='connect.inc.php' ? '/\$httpsisinuse\s*=\s*eval\(\$evaluateifhttpsisinuse\);/' : '/eval\(\$evaluateifhttpsisinuse\);\s*\$httpsisinuse\s*=\s*\$httpsisinuse\s*\?\s*true\s*:\s*false;/s') : ($route==='oidc/bootstrap.inc.php'
        ? '/\$httpsisinuse\s*=\s*false;.*?(?=\s*if\s*\(!\$httpsisinuse\))/s'
        : '/\$httpsisinuse\s*=\s*false;.*?\$httpsisinuse\s*=\s*\(bool\)\s*\$httpsisinuse;/s');
    if (!preg_match($pattern,$source,$match)) {
        if ($legacy || in_array($route,$requiredRoutes,true) || $route==='oidc/bootstrap.inc.php' || strpos($source,'$evaluateifhttpsisinuse')!==false) throw new RuntimeException('HTTPS detector not found: '.$route);
        continue;
    }
    // Historical frontend/backend routes used separate return/assignment contracts.
    $routeProfiles=$legacy ? ($route==='connect.inc.php' ? array_slice($profiles,0,8) : array_slice($profiles,8,4)) : $profiles;
    foreach ($routeProfiles as $profile) {
        $checks++;
        if (httpsDetectionValue($match[0],$profile[0])!==$profile[1]) throw new RuntimeException('HTTPS configuration contract failed: '.$route.' / '.$profile[0]);
    }
}
echo 'HTTPS configuration contracts: '.$checks." passed.\n";
