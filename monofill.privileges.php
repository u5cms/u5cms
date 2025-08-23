<?php
$_GET['l']=$_COOKIE['aclan'];
$sql_a="SELECT * FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_GET['n'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p><script>alert("'.htmlXspecialchars(mysql_error()).'")</script>';
}
$num_a = mysql_num_rows($result_a);
if ($disableformsourcenamecheck!='yes'&&$num_a==0) die('ERROR: Source form does not exist<script>alert("ERROR: Source form does not exist")</script>');

$row_a = mysql_fetch_array($result_a);

include('ob.php');
$pexcheck = $ob;

if($pexcheck==false) $pexcheck=render(def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']));

if(strpos($pexcheck,'ifrmonofill')<1)die('<script>if(top.location.href.indexOf("/u5admin")<0)alert("ERROR: [mf] not set in source form!")</script>');
?>
