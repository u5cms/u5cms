<?php
/* Reproducible, hash-guarded patches for the bundled/locked upstream versions. */
if (PHP_SAPI!=='cli') { http_response_code(404);exit; }
$root=dirname(__DIR__);$check=in_array('--check',$argv,true);
try {
    $manifest=json_decode(file_get_contents($root.'/patches/php9-return-types.json'),true,512,JSON_THROW_ON_ERROR);
    $pending=array();
    foreach ($manifest['files'] as $file) {
        if (!preg_match('~^vendor/laminas/[A-Za-z0-9_./-]+\.php$~D',$file['path']) || strpos($file['path'],'..')!==false) throw new RuntimeException('Invalid dependency patch path.');
        $path=$root.'/'.$file['path'];$source=file_get_contents($path);
        if ($source===false) throw new RuntimeException('Missing locked dependency: '.$file['path']);
        $sha=hash('sha256',$source);
        if ($sha===$file['after_sha256']) continue;
        if ($sha!==$file['before_sha256']) throw new RuntimeException('Review required: dependency differs from the locked patch source: '.$file['path']);
        $after=$source;
        foreach ($file['replacements'] as $edit) {
            if (substr_count($after,$edit['old'])!==$edit['count']) throw new RuntimeException('Dependency patch does not match: '.$file['path']);
            $after=str_replace($edit['old'],$edit['new'],$after);
        }
        if (hash('sha256',$after)!==$file['after_sha256']) throw new RuntimeException('Dependency patch result mismatch: '.$file['path']);
        $pending[]=array('path'=>$path,'source'=>$after);
    }
    if ($check && $pending) throw new RuntimeException('Dependency return-type patches are not installed. Run this script without --check.');
    foreach ($pending as $file) {
        $temporary=tempnam(dirname($file['path']),'.u5-php-types-');
        if ($temporary===false || file_put_contents($temporary,$file['source'])!==strlen($file['source']) || !chmod($temporary,fileperms($file['path'])&0777) || !rename($temporary,$file['path'])) {
            if ($temporary && is_file($temporary)) unlink($temporary);
            throw new RuntimeException('Cannot publish dependency type patch. Retry after correcting file permissions.');
        }
    }
    echo $check ? "Dependency return contracts verified.\n" : count($pending)." dependency return-contract files updated.\n";
} catch (Throwable $error) { fwrite(STDERR,$error->getMessage()."\n");exit(1); }
