<?php
ignore_user_abort(true);

if(file_exists('../fileversions/htarunning.txt') && file_get_contents('../fileversions/htarunning.txt')!=0 && file_get_contents('../fileversions/htarunning.txt')>time()-60*15)die('<script>top.document.title="."+top.document.title</script>');
file_put_contents('../fileversions/htarunning.txt',time());

require_once ('connect.inc.php');
require_once ('updateintranet.php');
require_once ('getadmins.inc.php');
require_once('u5idn.inc.php');
$sql_a="SELECT name,typ FROM resources WHERE deleted!=1 AND typ!='p' AND typ!='c' AND typ!='s' ORDER BY lastmut DESC";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$num_a = mysql_num_rows($result_a);

$replacestart="replace(replace(replace(replace(replace(replace(";
$replaceend=",'[[[[',' '),'[[[',' '),'[[',' '),']]]]',' '),']]]',' '),']]',' ')";


for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

//////////////////////////////////////////////////////////////////////////////////////////////////////////
$existent="(".$replacestart."content_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."content_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."content_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."content_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."content_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."content_5".$replaceend." LIKE '%:".$row_a['name']."?%')

 OR

(".$replacestart."title_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."title_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."title_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."title_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."title_5".$replaceend." LIKE '%:".$row_a['name']."?%')

 OR
 
(".$replacestart."desc_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."desc_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."desc_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."desc_5".$replaceend." LIKE '%:".$row_a['name']."?%')

 OR

(".$replacestart."key_1".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_2".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_3".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_4".$replaceend." LIKE '%[".$row_a['name']."]%' OR ".$replacestart."key_5".$replaceend." LIKE '%[".$row_a['name']."]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_2".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_3".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_4".$replaceend." LIKE '%:".$row_a['name']."]%' OR ".$replacestart."key_5".$replaceend." LIKE '%:".$row_a['name']."]%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_2".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_3".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_4".$replaceend." LIKE '%[".$row_a['name']."?%' OR ".$replacestart."key_5".$replaceend." LIKE '%[".$row_a['name']."?%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_2".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_3".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_4".$replaceend." LIKE '%:".$row_a['name']."?%' OR ".$replacestart."key_5".$replaceend." LIKE '%:".$row_a['name']."?%')

 OR

(".$replacestart."content_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."content_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
OR
(".$replacestart."title_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."title_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
OR
(".$replacestart."desc_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."desc_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
OR
(".$replacestart."key_1".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_2".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_3".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_4".$replaceend." LIKE '%r/".$row_a['name']."/%' OR ".$replacestart."key_5".$replaceend." LIKE '%r/".$row_a['name']."/%')
";

$sql_b="SELECT name,logins,hidden FROM resources WHERE deleted!=1 AND ($existent) ORDER BY lastmut DESC";
$result_b=mysql_query($sql_b);

if ($result_b==false) echo 'SQL_b-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';

$num_b = mysql_num_rows($result_b);


$onindexedpages=0;
$onofflinepages=0;
$onnonindexedpages=0;
$onforcerpages=0;

$onfreepages=0;
$collectedlogins='';


for ($i_b=0; $i_b<$num_b; $i_b++) {
$row_b = mysql_fetch_array($result_b);

////////////////
if ($row_a['name']!=$row_b['name']) {

if($row_b['hidden']==0 || $row_b['hidden']=='')  $onindexedpages++;
if($row_b['hidden']==1 )  $onofflinepages++;
if($row_b['hidden']==-1)  $onnonindexedpages++;
if($row_b['hidden']==2)  $onforcerpages++;

if(trim($row_b['logins'])!='') $collectedlogins.=$row_b['logins'];
else $onfreepages++;

}
////////////////
}
if(trim($row_a['name'])!='')mkhta($row_a['name'],$onindexedpages,$onofflinepages,$onnonindexedpages,$onforcerpages,$onfreepages,$collectedlogins,$row_a['typ']);      
//////////////////////////////////////////////////////////////////////////////////////////////////////////

}


function mkhta($name,$onindexedpages,$onofflinepages,$onnonindexedpages,$onforcerpages,$onfreepages,$collectedlogins,$typ) {
global $alladmins;
global $u5cmsrealm;

//echo $name.': '.$onindexedpages.' '.$onofflinepages.' '.$onnonindexedpages.' '.$onforcerpages.' '.$onfreepages.' '.$collectedlogins.'<hr>';

if($onforcerpages==0 && $onfreepages>0) {

$sql_a="UPDATE resources SET logins='' WHERE name='$name' AND typ!='p'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';  

@unlink("../r/$name/.htaccess");
@unlink("../r/$name/.htpasswd");

if($typ=='v') {
$sql_a="UPDATE resources SET logins='' WHERE name='v".$name."' AND typ!='p'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';  

@unlink("../r/v".$name."/.htaccess");
@unlink("../r/v".$name."/.htpasswd");	
}

}

else {
$sql_a="UPDATE resources SET logins='".mysql_real_escape_string($collectedlogins)."' WHERE name='$name' AND typ!='p'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';  

if($typ=='v') {
$sql_a="UPDATE resources SET logins='".mysql_real_escape_string($collectedlogins)."' WHERE name='v".$name."' AND (typ='i' OR typ='f')";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';  
}

$hts='';
//$collectedlogins=html_entity_decode(utf8_encode(trim($collectedlogins)), ENT_COMPAT, 'UTF-8');
$collectedlogins=u5toutf8($collectedlogins);
$collectedlogins=explode(';',str_replace('?','',$collectedlogins));
for ($i=0;$i<tnuoc($collectedlogins);$i++) {

//$collectedlogins[$i]=u5allnument(trim($collectedlogins[$i]));
//$collectedlogins[$i]=html_entity_decode(html_entity_decode(($collectedlogins[$i]), ENT_COMPAT,'ISO-8859-1'), ENT_COMPAT,'ISO-8859-1');


$items=explode(':',trim($collectedlogins[$i]));
//echo '<hr>'.$media.'x'.$items[0].'x'.$items[1];
if (str_replace('&#0;','',u5flatidn($items[0]))) $hts.=u5flatidn($items[0]).':'.pwdhsh($items[1])."\r\n";
}
//echo $media.' '.$hts.'<hr>';
//var_dump($collectedlogins);
if (str_replace(' ','',$hts)!='') {
file_put_contents("../r/$name/.htpasswd",$hts.$alladmins);
if($typ=='v') file_put_contents("../r/v".$name."/.htpasswd",$hts.$alladmins);
if(!isset($u5cmsrealm))$u5cmsrealm='LOGIN';

if($u5allowbasicauthtoprotectedfilesindirr=='yes') {
$htpasswd="AuthName \"".$u5cmsrealm."\"
AuthType Basic
AuthUserFile \"".str_replace('/u5admin/htaccess.php','',str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']))."/r/$name/.htpasswd\"
Require valid-user
";
}
else {
$htpasswd="Deny from all";
}

file_put_contents("../r/$name/.htaccess",$htpasswd);
if($typ=='v') file_put_contents("../r/v".$name."/.htaccess",$htpasswd);

}
}

if($onindexedpages>0) {
$sql_a="UPDATE resources SET hidden=0 WHERE name='$name' AND typ!='p' AND typ!='c'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';    
}
else if($onnonindexedpages>0) {
$sql_a="UPDATE resources SET hidden=-1 WHERE name='$name' AND typ!='p' AND typ!='c'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';    
}
else if($onofflinepages>0) {
$sql_a="UPDATE resources SET hidden=1 WHERE name='$name' AND typ!='p' AND typ!='c'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';    
}

if($typ=='v') {
if($onindexedpages>0) {
$sql_a="UPDATE resources SET hidden=0 WHERE name='v".$name."' AND typ!='p' AND typ!='c'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';    
}
else if($onnonindexedpages>0) {
$sql_a="UPDATE resources SET hidden=-1 WHERE name='v".$name."' AND typ!='p' AND typ!='c'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';    
}
else if($onofflinepages>0) {
$sql_a="UPDATE resources SET hidden=1 WHERE name='v".$name."' AND typ!='p' AND typ!='c'";
$result_a=mysql_query($sql_a);
if ($result_a==false) 	echo 'SQL_a-Query schlug failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';    
}
}
}
?>
<script>
if(parent)if(parent.parent)if(parent.parent.parent)if(parent.parent.parent.i3)if(parent.parent.parent.i3.location.href.indexOf('i1.php')>1 || parent.parent.parent.i3.location.href.indexOf('f.php')>1 || parent.parent.parent.i3.location.href.indexOf('d.php')>1 || parent.parent.parent.i3.location.href.indexOf('v1.php')>1) {
parent.parent.parent.i3.location.reload();
}
//if(location.href.indexOf('o=intranet')<0&&window.name!='htaccess0')location.href='../indexer.php?l=<?php echo $lan1na?>';
</script>
<?php
echo"<script>if(parent)if(parent.parent)if(parent.parent.document.getElementById('htaccess'))parent.parent.document.getElementById('htaccess').style.display='block'</script>";
echo '<audio id="doneaudio" src="'.rand(1,6).'.mp3" autoplay />';
file_put_contents('../fileversions/htarunning.txt',0);
?>
<script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>