<?php
if($_GET['iswear']!='iknowthatthisoverwritesthecurrentlayout')die('TO OVERWRITE THE CURRENT LAYOUT WITH THE INIITIAL U5CMS LAYOUT, ADD ON THE PAGE AT HAND THE GET PARAMETER iswear TO THE URL WITH THE VALUE iknowthatthisoverwritesthecurrentlayout');
require_once ('connect.inc.php');

orignow('cssauthuser');
orignow('cssbase');
orignow('cssform');
orignow('csshot');
orignow('csslayout');
orignow('cssnavleft');
orignow('cssnavleftsubtop');
orignow('cssnavtop');
orignow('cssstyle');
orignow('csstable');
orignow('htmltemplate');
orignow('jsmobilespecific');

function orignow($n) {
$d=file_get_contents('../r/'.$n.'.tpl');
$sql_a="UPDATE resources SET content_1='".mysql_real_escape_string($d)."' WHERE name='$n'";
echo '<pre>'.htmlspecialchars($sql_a).'</pre><hr>';
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
?>