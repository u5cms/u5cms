<?php
/* Escaped CMS examples are authored literal text, not object dependencies. */
$root=$argv[1] ?? dirname(__DIR__);require $root.'/codeassets.inc.php';$checks=0;
$cases=array(
    '[[cssbase]] [cssbase]'=>'[[cssbase]] [base!css]',
    '[[[cssbase]]] [cssbase]'=>'[[[cssbase]]] [base!css]',
    '[[[[cssbase]]]] [$$$:cssbase]'=>'[[[[cssbase]]]] [$$$:base!css]',
    '<h1>Example [[cssbase]]</h1>[cssbase]'=>'<h1>Example [[cssbase]]</h1>[base!css]',
    '[[cssbase?l=de]] [cssbase?l=de]'=>'[[cssbase?l=de]] [base!css?l=de]',
    '[[hello]] [cssbase]'=>'[[hello]] [base!css]',
    '[[[$$$:cssbase]]] [$$$:cssbase]'=>'[[[$$$:cssbase]]] [$$$:base!css]',
    "\xDCber \x80 [[cssbase]] [custom]"=>"\xDCber \x80 [[cssbase]] [custom!js]",
);
foreach($cases as$before=>$expected){
    $actual=u5CodeAssetReferences($before,array('cssbase'=>'base!css','custom'=>'custom!js'));
    if($actual!==$expected)throw new RuntimeException('Code asset rewrite changed an escaped literal example.');$checks++;
    if(u5CodeAssetReferences($actual,array('cssbase'=>'base!css','custom'=>'custom!js'))!==$actual)throw new RuntimeException('Literal-safe rewrite is not idempotent.');$checks++;
}
echo 'Literal code-reference markup contracts: '.$checks." checks passed (escaped examples, actual includes, headings, query syntax, Windows-1252 and idempotence).\n";
