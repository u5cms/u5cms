<?php
/* Execute the actual redirect guards, without database/authentication side
 * effects. Expected pairs follow DNS/URL host identity, not IDNA migration. */
$root=getenv('U5CMS_SOURCE_ROOT') ?: dirname(__DIR__);
require $root.'/vendor/autoload.php';
if (!function_exists('idn_to_ascii')) throw new RuntimeException('Run with PHP intl or the distribution IDN API.');
$cases=array(
    array('cms.example','https://cms.example/path',true),
    array('CMS.EXAMPLE','https://cms.example/path',true),
    array('cms.example','https://other.example/path',false),
    array('fass.example',"https://fa\xC3\x9F.example/path",false),
    array("fa\xC3\x9F.example",'https://fass.example/path',false),
    array('xn--fa-hia.example',"https://fa\xC3\x9F.example/path",true),
    array("fa\xC3\x9F.example",'https://xn--fa-hia.example/path',true),
    array('cms.example','relative/path',true),
    array('cms.example','//other.example/path',false),
    array('cms.example','https://cms.example:444/path',false)
);
$checks=0;$failed=0;
foreach (array('loginsave.php','u5admin/loginsave.php','saml/login.php','u5sys._login.php') as $path) {
    $source=file_get_contents($root.'/'.$path);
    $start=strpos($source, "if (isset(\$_GET['u'])) {");
    if ($start===false) throw new RuntimeException('Missing redirect guard in '.$path);
    $block='';$depth=0;$started=false;
    foreach (token_get_all('<?php '.substr($source,$start)) as $token) {
        if (is_array($token) && $token[0]===T_OPEN_TAG) continue;
        $block.=is_array($token) ? $token[1] : $token;
        if ($token==='{') {$depth++;$started=true;}
        elseif ($token==='}' && --$depth===0 && $started) break;
    }
    if (!$started || $depth!==0) throw new RuntimeException('Incomplete redirect guard in '.$path);
    foreach ($cases as list($host,$url,$expected)) {
        $code='require '.var_export($root.'/vendor/autoload.php',true).';$_GET=array("u"=>'.var_export($url,true).');$_SERVER=array("HTTP_HOST"=>'.var_export($host,true).',"SERVER_PORT"=>443);'.$block.'echo "ALLOWED";';
        $args=array(PHP_BINARY);
        $args=array_merge($args,array('-r',$code));
        $process=proc_open($args,array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
        if (!is_resource($process)) throw new RuntimeException('Cannot run redirect fixture.');
        $out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
        $status=proc_close($process);$checks++;
        if ($status!==0 || $error!=='' || ($out==='ALLOWED')!==$expected) {
            $failed++;fwrite(STDERR,$path.': '.$host.' -> '.$url.' expected '.($expected?'allow':'deny').', got '.$out.$error."\n");
        }
    }
}
if ($failed) throw new RuntimeException($failed.' of '.$checks.' login-host checks failed.');
echo 'Login host policy: '.$checks.' checks passed on '.PHP_VERSION.".\n";
