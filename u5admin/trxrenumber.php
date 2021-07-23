<?php 
require_once('connect.inc.php');

$sql_a="SELECT id FROM trxlog ORDER BY id";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
   $row_a = mysql_fetch_array($result_a);
      
      
      
      
$sql_b="UPDATE trxlog SET id=".$row_a['id']." WHERE id=".$row_a['id'];
$result_b=mysql_query($sql_b);

if ($result_b==false) {
echo 'SQL_b-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';
}
      
      
      
      
      
      
      
         }
?>