<script>
if(parent) {
if(parent.document.getElementById('body')) {
parent.document.getElementById('body').style.visibility='hidden';	
}
}
</script>
<?php 
ignore_user_abort(true); 
require_once('connect.inc.php');
require_once('u5admin/u5idn.inc.php');

$sql_a="SELECT * FROM formdata WHERE formname='getaloginstep1' AND status<5 ORDER BY time DESC";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$num_a = mysql_num_rows($result_a);

if ($num_a>0) {

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

////////////////////////////////////////////////////////
$sql_b="UPDATE formdata SET status=5 WHERE formname='getaloginstep1' AND status<5 AND datacsv ='".mysql_real_escape_string($row_a['datacsv'])."' AND time<".$row_a['time'];
$result_b=mysql_query($sql_b);
if ($result_b==false) echo 'SQL_b-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';
////////////////////////////////////////////////////////

$data=$row_a['datacsv'];
$data=explode(';',$data);
$email.=trim(str_replace($data[0][0],'',$data[0])).",";
}

$email=u5flatidn($email);
$sql_a="UPDATE intranetmembers SET members='".mysql_real_escape_string(str_replace(',&#0;@&#0;','',$email))."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

}
?>
<iframe src="u5admin/htaccess.php?o=intranet" frameborder="0" width="0" height="0"></iframe>
<script>
if(parent) {
if(parent.document.getElementById('body')) {
parent.document.getElementById('body').style.visibility='visible';	
}
}
</script>