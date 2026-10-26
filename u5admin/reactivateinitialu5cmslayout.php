<?php
die('TO RESET CSS TO THE ORIGINAL TEMPLATES, REMOVE THE die() IN youru5cmsinstallation/u5admin/reactivateinitialu5cmslayout.php');
if($_GET['iswear']!='iknowthatthisoverwritesthecurrentlayout')die('TO OVERWRITE THE CURRENT LAYOUT WITH THE INIITIAL U5CMS LAYOUT, ADD ON THE PAGE AT HAND THE GET PARAMETER iswear TO THE URL WITH THE VALUE iknowthatthisoverwritesthecurrentlayout');
require_once ('connect.inc.php');

$u5ResetReferences=array();
foreach (u5CodeAssetDefaults() as $legacy=>$typed) {
    $target=u5CodeAssetName($legacy,dirname(__DIR__));
    $u5ResetReferences[$legacy]=$target;
    $u5ResetReferences[$typed]=$target;
}
foreach (u5CodeAssetDefaults() as $legacy=>$typed) {
    $target=u5CodeAssetName($legacy,dirname(__DIR__));
    $path=dirname(__DIR__).'/r/'.$target.'.tpl';
    if (!is_file($path)) continue;
    $d=u5CodeAssetReferences(file_get_contents($path),$u5ResetReferences);
    $sql_a="UPDATE resources SET content_1='".mysql_real_escape_string($d)."' WHERE name='".mysql_real_escape_string($target)."' AND typ='c'";
    echo '<pre>'.htmlspecialchars($sql_a).'</pre><hr>';
    $result_a=mysql_query($sql_a);
    if ($result_a==false) echo 'SQL_a-Query did not work!...!<p>';
}
u5CodeAssetExport(dirname(__DIR__));
?>
