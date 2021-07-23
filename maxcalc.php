<?php 
$a=0;
$u=0;
$uuu+=$value;
$uu=$uuu;
$m=mysql_real_escape_string($key);

$m=explode('_MAX_',$m);
$field=$m[0].'_MAX';
$m=$m[1];
$m=explode('_',$m);

$status=$m[0];
$allmax=$m[1];
$userthismax=$m[2];
$userallmax=$m[3];

$where=' AND (status='.$status[0];
if($status[1]>0)$where.=' OR status='.$status[1];
if($status[2]>0)$where.=' OR status='.$status[2];
if($status[3]>0)$where.=' OR status='.$status[3];
if($status[4]>0)$where.=' OR status='.$status[4];
if($status[5]>0)$where.=' OR status='.$status[5];
if($status[6]>0)$where.=' OR status='.$status[6];

$where.=')';

$c=$_GET['c'];
if(trim($c)=='')$c=$_GET['n'];

$c=explode('&',$c);
$c=$c[0];

$c=mysql_real_escape_string($c);

////////////////////
$sql_a="SELECT * FROM formdata WHERE formname='$c' $where";
$result_a=mysql_query($sql_a);
//echo $sql_a;
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

$headcsv=explode(';',$row_a['headcsv']);
$datacsv=explode(';',$row_a['datacsv']);
for ($i=0;$i<count($headcsv);$i++) {

if (strpos($headcsv[$i],$field)==1) $a+=substr($datacsv[$i],1);
}
}
////////////////////
$sql_a="SELECT * FROM formdata WHERE formname='$c' $where AND authuser='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."'";
$result_a=mysql_query($sql_a);
//echo $sql_a;
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

$headcsv=explode(';',$row_a['headcsv']);
$datacsv=explode(';',$row_a['datacsv']);
for ($i=0;$i<count($headcsv);$i++) {

if (strpos($headcsv[$i],$field)==1) $u+=substr($datacsv[$i],1);
if (strpos($headcsv[$i],'_MAX_')>1) $uu+=substr($datacsv[$i],1);
}
}


if($value>0 && $allmax-$a-$value<0) die('<script>
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
parent.document.u5form.'. $key .'.onchange();
</script>');
else if($value>0)echo'<script>
parent.document.u5form.'. $key .'.style.borderColor=\'green\';
parent.document.u5form.'. $key .'.style.background=\'white\';
</script>';
else echo'<script>
parent.document.u5form.'. $key .'.style.borderColor=\'gray\';
parent.document.u5form.'. $key .'.style.background=\'white\';
</script>';


$restforthisuser=$userthismax-$a;
if($uu>=$userallmax && $userallmax>0 && $restforthisuser>0)$restforthisuser=0;

if($userthismax>0) {
if($value>0 && $restforthisuser-$value<0) die('<script>
parent.document.u5form.'. $key .'.style.borderColor=\'red\';
parent.document.u5form.'. $key .'.style.background=\'yellow\';
parent.document.u5form.'. $key .'.onchange();
</script>');
else if($value>0) echo'<script>
parent.document.u5form.'. $key .'.style.borderColor=\'green\';
parent.document.u5form.'. $key .'.style.background=\'white\';
</script>';
}
?>