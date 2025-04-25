<?php 
ignore_user_abort(true);set_time_limit(3600); 
require_once('connect.inc.php');

if(file_get_contents('../fileversions/CLrunning.txt')<time()-60*60*24*10) {
file_put_contents('../fileversions/CLrunning.txt',time())
?>
<iframe src="cleanbackups.php"></iframe>
<audio id="doneaudio" src="'.rand(1,6).'.mp3" autoplay /><script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>
<?php
$sql_c="SELECT name FROM resources WHERE deleted!=1";
$result_c=mysql_query($sql_c);

if ($result_c==false) {
echo 'SQL_c-Query failed!...!<p>';
}

$num_c = mysql_num_rows($result_c);

for ($i_c=0; $i_c<$num_c; $i_c++) {
$row_c = mysql_fetch_array($result_c);
      
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$sql_a="SELECT lastmut FROM resources_log WHERE name='".$row_c['name']."' AND operator NOT LIKE '%(!)%' ORDER BY lastmut DESC";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

/////////////////////////////////////////////////

$sql_b="DELETE FROM resources_log WHERE name='".$row_c['name']."' AND operator NOT LIKE '%(!)%' AND lastmut<".$row_a['lastmut']." AND lastmut>".($row_a['lastmut']-1*60*60)." AND lastmut<".(time()-3*24*60*60);    

$result_b=mysql_query($sql_b);

if ($result_b==false) {
echo 'SQL_b-Query failed!...!<p>';
}
/////////////////////////////////////////////////    
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$sql_a="SELECT lastmut FROM resources_log WHERE name='".$row_c['name']."' AND operator LIKE '%(!)%' ORDER BY lastmut DESC";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

/////////////////////////////////////////////////

$sql_b="DELETE FROM resources_log WHERE name='".$row_c['name']."' AND operator LIKE '%(!)%' AND lastmut<".$row_a['lastmut']." AND lastmut>".($row_a['lastmut']-1*60*60)." AND lastmut<".(time()-3*24*60*60);    

$result_b=mysql_query($sql_b);

if ($result_b==false) {
echo 'SQL_b-Query failed!...!<p>';
}
/////////////////////////////////////////////////    
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


}
echo "<script>if(parent)if(parent.document.getElementById('cleanlog'))parent.document.getElementById('cleanlog').style.display='inline'</script>";
}
?>