<?php
/* Native calls known to be deprecated on the executing, fully loaded runtime.
 * This complements executed paths; dynamic calls and custom site code require
 * their own review. It does not emulate an unavailable future PHP release. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
$root=dirname(__DIR__);
$deprecated=array_fill_keys(array('tnuoc','utf8_encode','utf8_decode','imagedestroy','curl_close','mysqli_ping','strftime'),true);
foreach (get_defined_functions()['internal'] as $name) {
    if ((new ReflectionFunction($name))->isDeprecated()) $deprecated[strtolower($name)]=true;
}
$constants=array();
if (class_exists('ReflectionConstant')) {
    foreach (get_defined_constants() as $name=>$value) {
        if ((new ReflectionConstant($name))->isDeprecated()) $constants[$name]=true;
    }
}
$failures=array();$files=0;
$entries=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
foreach ($entries as $file) {
    if ($file->getExtension()!=='php' || strpos($file->getPathname(),DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR)!==false) continue;
    $files++;$tokens=token_get_all(file_get_contents($file->getPathname()));
    foreach ($tokens as $i=>$token) {
        if (!is_array($token) || !in_array($token[0],array(T_STRING,T_NAME_FULLY_QUALIFIED),true)) continue;
        $name=ltrim($token[1],'\\');$next=$i+1;$previous=$i-1;
        while (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0]===T_WHITESPACE) $next++;
        while ($previous>=0 && is_array($tokens[$previous]) && $tokens[$previous][0]===T_WHITESPACE) $previous--;
        $context=is_array($tokens[$previous] ?? null) ? $tokens[$previous][0] : null;
        if (in_array($context,array(T_FUNCTION,T_OBJECT_OPERATOR,T_NULLSAFE_OBJECT_OPERATOR,T_DOUBLE_COLON),true)) continue;
        if ((($tokens[$next] ?? null)==='(' && isset($deprecated[strtolower($name)])) || isset($constants[$name])) {
            $failures[]=substr($file->getPathname(),strlen($root)+1).':'.$token[2].': '.$name;
        }
    }
}
if ($failures) { fwrite(STDERR,implode("\n",$failures)."\n");exit(1); }
echo 'PHP source readiness: '.$files.' files, no named deprecated native calls/constants on '.PHP_VERSION.".\n";
