<?php
require('connect.inc.php');
$delimiter=';';
$path='./../fileversions/useruploads';
$files='';
if ($handle = @opendir($path))  { 
   while (false !== ($dir = readdir($handle)))  { 
      $files.=$dir.$delimiter;
      }
   }
$files=explode($delimiter,$files);

for ($i=0; $i<count($files);$i++) {

$sql_a="SELECT * FROM formdata WHERE datacsv LIKE '%".mysql_real_escape_string($files[$i])."%' AND status!=5";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$num_a = mysql_num_rows($result_a);
if ($num_a<1) {
////
$sql_a="SELECT * FROM formdata WHERE datacsv LIKE '%".mysql_real_escape_string($files[$i])."%' AND status=5 AND lastmut<".(time()-31*24*60*60);
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$num_a = mysql_num_rows($result_a);
if ($num_a>0) if (filemtime($path.'/'.$files[$i])<time()-30*24*60*60) unlink($path.'/'.$files[$i]);
////
}

$sql_a="SELECT * FROM formdata WHERE datacsv LIKE '%".mysql_real_escape_string($files[$i])."%'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$num_a = mysql_num_rows($result_a);
if ($num_a==0) if (filemtime($path.'/'.$files[$i])<time()-30*24*60*60) unlink($path.'/'.$files[$i]);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$delimiter=';';
$path='./../r/P';
$files='';
if ($handle = @opendir($path))  { 
   while (false !== ($dir = readdir($handle)))  { 
      $files.=$dir.$delimiter;
      }
   }
$files=explode($delimiter,$files);

for ($i=0; $i<count($files);$i++) {

$sql_a="SELECT * FROM formdata WHERE datacsv LIKE '%".mysql_real_escape_string($files[$i])."%' AND status!=5";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$num_a = mysql_num_rows($result_a);
if ($num_a<1) {
////
$sql_a="SELECT * FROM formdata WHERE datacsv LIKE '%".mysql_real_escape_string($files[$i])."%' AND status=5 AND lastmut<".(time()-31*24*60*60);
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$num_a = mysql_num_rows($result_a);
if ($num_a>0) if (filemtime($path.'/'.$files[$i])<time()-30*24*60*60) unlink($path.'/'.$files[$i]);
////
}

$sql_a="SELECT * FROM formdata WHERE datacsv LIKE '%".mysql_real_escape_string($files[$i])."%'";
$result_a=mysql_query($sql_a);
if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
$num_a = mysql_num_rows($result_a);
if ($num_a==0) if (filemtime($path.'/'.$files[$i])<time()-30*24*60*60) unlink($path.'/'.$files[$i]);
}
?>