<?php
require_once ('connect.inc.php');
require_once('login.inc.php');
require_once ('render.inc.php');

if ($_GET['c']=='') {
$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
$result_a = mysql_query($sql_a);

if ($result_a == false) {

  echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}


$row_a = mysql_fetch_array($result_a);

$_GET['c']=$row_a['name'];

}



$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND name='" . (mysql_real_escape_string($_GET['c'])) . "r'";
$result_a = mysql_query($sql_a);

if ($result_a == false) {

  echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}

$num_a = mysql_num_rows($result_a);

$row_a = mysql_fetch_array($result_a);

/////////////////////////////////////////////////////////////////////////////////

if ($num_a==0) {

$sql_a = "SELECT * FROM resources WHERE deleted!=1 AND name='news'";
$result_a = mysql_query($sql_a);

if ($result_a == false) {

  echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}

$num_a = mysql_num_rows($result_a);
$row_a = mysql_fetch_array($result_a);

}

/////////////////////////////////////////////////////////////////////////////////

$sql_b = "SELECT * FROM inserts WHERE source1 NOT like '[' AND source1 NOT like '[h:]' ORDER BY length(source1) DESC";
$result_b = mysql_query($sql_b);

if ($result_b == false) {

  echo 'SQL_b-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_b . '</font><p>';
}
$num_b = mysql_num_rows($result_b);

$notp='';
if ($row_a['typ']!='p') $notp='['.$row_a['name'].']';
if ($row_a['hidden']<1) $echo=render($notp.' '.def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']));
else $echo=def($notpub_1,$notpub_2,$notpub_3,$notpub_4,$notpub_5);

$echo=str_replace('[]','',$echo);
if ($executephp=='onallpages' || ($executephp=='inarchiveonly' && $row_a['deleted']==2)) echo eval('?>'.$echo);
else echo $echo;
?>
