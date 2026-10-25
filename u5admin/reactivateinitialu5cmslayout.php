<?php
die('TO RESET CSS TO THE ORIGINAL TEMPLATES, REMOVE THE die() IN youru5cmsinstallation/u5admin/reactivateinitialu5cmslayout.php');
if($_GET['iswear']!='iknowthatthisoverwritesthecurrentlayout')die('TO OVERWRITE THE CURRENT LAYOUT WITH THE INIITIAL U5CMS LAYOUT, ADD ON THE PAGE AT HAND THE GET PARAMETER iswear TO THE URL WITH THE VALUE iknowthatthisoverwritesthecurrentlayout');
require_once ('connect.inc.php');

$u5ResetReferences=array();
foreach (u5CodeAssetDefaults() as $legacy=>$typed) $u5ResetReferences[$typed]=u5CodeAssetName($legacy,dirname(__DIR__));
foreach (u5CodeAssetDefaults() as $legacy=>$typed) {
    if (is_file(__DIR__.'/code-templates/'.$typed.'.tpl')) orignow($typed,u5CodeAssetName($legacy,dirname(__DIR__)));
}
u5CodeAssetExport(dirname(__DIR__));

function orignow($n,$target) {
global $u5ResetReferences;
$d=u5CodeAssetReferences(file_get_contents(__DIR__.'/code-templates/'.$n.'.tpl'),$u5ResetReferences);
$sql_a="UPDATE resources SET content_1='".mysql_real_escape_string($d)."' WHERE name='".mysql_real_escape_string($target)."' AND typ='c'";
echo '<pre>'.htmlspecialchars($sql_a).'</pre><hr>';
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';
}
?>