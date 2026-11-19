<?php
/* Synthetic byte-conservation oracle for large authored HTML, no private data. */
$root=$argv[1] ?? dirname(__DIR__);require $root.'/codeassets.inc.php';
$checks=0;$map=array('cssbase'=>'base!css');
function largeMarkupCheck($actual,$expected,$label){global $checks;if($actual!==$expected)throw new RuntimeException('Large markup changed bytes or reference context: '.$label);$checks++;}
$cases=array();
foreach(array(4096,16384,65536,1048576)as$length){
    $tag='<section data-note='.str_repeat('x',$length).'>';
    $cases['unquoted-'.$length]=array($tag.'[cssbase]</section>',$tag.'[base!css]</section>');
}
$value=str_repeat('x>[cssbase]<',8000);
$tag='<div title="'.$value.'" data-include="[$$$:cssbase]">';
$cases['quoted-delimiters']=array($tag.'[cssbase]</div>',str_replace('[$$$:cssbase]','[$$$:base!css]',$tag).'[base!css]</div>');
$tag='<div'.str_repeat(' data-note="[cssbase]>x"',12000).'>';
$cases['many-attributes']=array($tag.'[cssbase]</div>',$tag.'[base!css]</div>');
$tag='<svg><path d='.str_repeat('M0,0L1,1 ',20000).' title="[cssbase]"></path></svg>';
$cases['svg-path']=array($tag.'[cssbase]',$tag.'[base!css]');
// Retain the established treatment of an unmatched opening tag as tag context.
$unclosed='<div data-note="'.str_repeat('x',65536).' [cssbase]';$cases['unterminated-tag']=array($unclosed,$unclosed);
$cases['literal-brackets-and-bytes']=array("<div data-note=".str_repeat('x',65536).">[[cssbase]] \xDCber \x80 [cssbase]</div>","<div data-note=".str_repeat('x',65536).">[[cssbase]] \xDCber \x80 [base!css]</div>");
$script='<script>'.str_repeat('var cssbase=1;',6000).'var x=[cssbase]; var y="r/cssbase.css";</script>';
$cases['script-context']=array($script.'[cssbase]',str_replace('r/cssbase.css','r/base.css',$script).'[base!css]');
$style='<style>'.str_repeat('[cssbase]{color:red}',6000).'</style>';
$cases['style-context']=array($style.'[cssbase]',$style.'[base!css]');
$html='<div data-note='.str_repeat('x',65536).'>[cssbase]</div>';$expected=str_replace('>[cssbase]<','>[base!css]<',$html);
$cases['serialized']=array(serialize(array('html'=>$html)),serialize(array('html'=>$expected)));
$cases['json']=array(json_encode(array('html'=>$html)),json_encode(array('html'=>$expected)));
$settings=array();foreach(array('pcre.jit','pcre.backtrack_limit','pcre.recursion_limit')as$name)$settings[$name]=ini_get($name);
try{
    ini_set('pcre.backtrack_limit','1000000');ini_set('pcre.recursion_limit','100000');
    foreach(array('1','0')as$jit){ini_set('pcre.jit',$jit);
        foreach($cases as$label=>$case){
            try{$actual=u5CodeAssetReferences($case[0],$map);}catch(Throwable$error){throw new RuntimeException('Large markup '.$label.' (JIT '.$jit.'): '.$error->getMessage().' '.preg_last_error_msg(),0,$error);}
            largeMarkupCheck($actual,$case[1],$label.' JIT '.$jit);
            largeMarkupCheck(u5CodeAssetReferences($actual,$map),$actual,$label.' must remain idempotent JIT '.$jit);
        }
    }
    echo 'Large code-reference markup contracts: '.$checks." checks passed (normal PCRE limits, JIT on/off, long and repeated attributes, SVG, malformed markup, literal bytes, script/style, JSON and serialized data).\n";
}finally{foreach($settings as$name=>$value)ini_set($name,$value);}
