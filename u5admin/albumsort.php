<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>sortp</title>
<?php

require_once('../myfunctions.inc.php');
require('backendcss.php');

?></head>

<body style="background:#eeeeee">
<?php
$_GET['name']=$_GET['n'];
require('archivecheckget.inc.php');
$d=$_GET['d'];
$f=$_GET['f'];
$n=$_GET['n'];

if($_GET['t']<1)$t=1;
else $t=$_GET['t'];

$old=explode('_',$f);
$old=$old[tnuoc($old)-1];
$old=explode('.',$old);
$old=$old[0];

$tdiff=1;
if($t>1) {
if($t>$old){
	$d='u';
    $tdiff=$t-$old;
}
else {
	$d='d';
	$tdiff=$old-$t;
}
}

for($i=0;$i<$tdiff;$i++) {

if ($d=='d') $new=$old-1;
if ($d=='u') $new=$old+1;

//echo "<script>alert('$old $new $tdiff')</script>";

if ($new>999) die('<script>alert("highest position allowed is 999");</script>');
if ($new<100) die('<script>alert("lowest position allowed is 100");</script>');

rename("../r/".$n."/".$n."_".$new  .".jpg" , "../r/".$n."/".$n."_".(9999).".jpg");
rename("../r/".$n."/".$n."_".$old  .".jpg" , "../r/".$n."/".$n."_".$new  .".jpg");
rename("../r/".$n."/".$n."_".(9999).".jpg" , "../r/".$n."/".$n."_".$old  .".jpg");


$sql_a="UPDATE resources SET content_1=(replace(content_1,'$new>>>','9999>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_1=(replace(content_1,'$old>>>','$new>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_1=(replace(content_1,'9999>>>','$old>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';



$sql_a="UPDATE resources SET content_2=(replace(content_2,'$new>>>','9999>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_2=(replace(content_2,'$old>>>','$new>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_2=(replace(content_2,'9999>>>','$old>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';


$sql_a="UPDATE resources SET content_3=(replace(content_3,'$new>>>','9999>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_3=(replace(content_3,'$old>>>','$new>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_3=(replace(content_3,'9999>>>','$old>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_4=(replace(content_4,'$new>>>','9999>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_4=(replace(content_4,'$old>>>','$new>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_4=(replace(content_4,'9999>>>','$old>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_5=(replace(content_5,'$new>>>','9999>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_5=(replace(content_5,'$old>>>','$new>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$sql_a="UPDATE resources SET content_5=(replace(content_5,'9999>>>','$old>>>')) WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

    $sql_a="UPDATE resources SET lastmut=".time()." WHERE name='".mysql_real_escape_string($n)."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!...!<p>'; 

if ($d=='d') $old--;
if ($d=='u') $old++;
}
?>

<script>
setTimeout("parent.files.location.reload()",1);
setTimeout("location.href='blank.php'",1111); 
if(parent.opener) {
setTimeout("if(parent.opener.parent)if(parent.opener.parent.i3)parent.opener.parent.i3.location.reload()",999);
setTimeout("if(parent.opener.parent)if(parent.opener.parent.i1)parent.opener.parent.i1.pframe.pviewit()",999);
setTimeout("if(parent.opener.parent)if(parent.opener.parent.i2)parent.opener.parent.i2.pframe.pviewit()",999);
}
</script>

</body>
</html>
