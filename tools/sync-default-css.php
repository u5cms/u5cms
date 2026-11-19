<?php
/* Keep the two installation seeds, CSS exports and reset templates in agreement. */
require_once dirname(__DIR__).'/codeassets.inc.php';
$root=dirname(__DIR__);$check=in_array('--check',$argv,true);$failed=false;
$names=array('base','layout','style','navleft','navtop','navleftsubtop','form','table');
$seeds=array();foreach(array('u5standard.sql','u5conference.sql') as $file)$seeds[$file]=file_get_contents($root.'/'.$file);
foreach($names as $name) {
    $object=$name.'!css';$source=file_get_contents($root.'/r/'.$object.'.tpl');
    $utf8=substr($source,0,3)==="\xEF\xBB\xBF" ? substr($source,3) : u5CodeAssetUtf8($source);
    $files=u5CodeAssetRowsToFiles(array(array('name'=>$object,'typ'=>'c','deleted'=>0,'content_1'=>$source,'u5_code_utf8'=>$utf8)),121500);
    $path=$root.'/r/'.$name.'.css';
    if(file_get_contents($path)!==$files[$name.'.css']) {
        if($check){fwrite(STDERR,"CSS export differs: $name\n");$failed=true;}
        else file_put_contents($path,$files[$name.'.css']);
    }
    $encoded=str_replace(array('\\',"\0","'","\r","\n","\x1a"),array('\\\\','\\0',"\\'",'\\r','\\n','\\Z'),$utf8);
    foreach($seeds as $file=>&$sql) {
        $needle="('".$object."','";
        if(substr_count($sql,$needle)!==1)throw new RuntimeException('Cannot identify the unique default CSS object in '.$file.': '.$object);
        $start=strpos($sql,$needle)+strlen($needle);$end=$start;$length=strlen($sql);
        while($end<$length) {if($sql[$end]==='\\'){$end+=2;continue;}if($sql[$end]==="'")break;$end++;}
        if($end===$length)throw new RuntimeException('Unterminated CSS source in '.$file);
        if(substr($sql,$start,$end-$start)!==$encoded) {
            if($check){fwrite(STDERR,"SQL source differs: $file $object\n");$failed=true;}
            else $sql=substr($sql,0,$start).$encoded.substr($sql,$end);
        }
    }
    unset($sql);
}
if(!$check)foreach($seeds as $file=>$sql)file_put_contents($root.'/'.$file,$sql);
if($failed)exit(1);
echo 'Default CSS: two SQL seeds, eight exports and eight reset templates agree (four distribution representations).'."\n";
