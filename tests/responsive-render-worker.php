<?php
// CLI-only adapter, deployed only into disposable test copies.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir($argv[1]);
$_SERVER['HTTP_HOST']='fixture.invalid';
$_SERVER['REQUEST_URI']='/index.php?c=fixture&l=en';
$_SERVER['PHP_AUTH_USER']='reader';
$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_COOKIE=array('u'=>'fixture-user','p'=>'fixture-password');
require 'connect.inc.php';
require $argv[2];
$cases=json_decode(file_get_contents($argv[3]),true);
$defaults=array();
foreach ($cases as $case) foreach (($case['settings'] ?? array()) as $key=>$value) $defaults[$key]=$GLOBALS[$key] ?? null;
$out=array();
foreach ($cases as $case) {
    $_GET=array_merge(array('c'=>'fixture','l'=>'en'),$case['get'] ?? array());
    $_SERVER['QUERY_STRING']=http_build_query($_GET);
    foreach ($defaults as $key=>$value) $GLOBALS[$key]=$value;
    foreach (($case['settings'] ?? array()) as $key=>$value) $GLOBALS[$key]=$value;
    $result_b=mysql_query("SELECT * FROM inserts WHERE source1 NOT like '[' AND source1 NOT like '[h:]' ORDER BY length(source1) DESC");
    $num_b=mysql_num_rows($result_b);
    if (function_exists('u5RenderUseTemplate')) u5RenderUseTemplate($argv[4]==='responsive' ? '<body data-u5-layout="responsive">' : '<body>');
    ob_start();$html=render(base64_decode($case['source']));$noise=ob_get_clean();
    $out[]=array('name'=>$case['name'],'html'=>base64_encode($html),'side_output'=>base64_encode($noise));
}
echo json_encode($out,JSON_THROW_ON_ERROR);
