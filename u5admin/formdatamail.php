<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
setcookie('ffrm', $_SERVER['QUERY_STRING'], time()+3600*24*365*10,'/');
require_once('connect.inc.php');
include('../config.php');
$_GET['f']=htmlXspecialchars(trim(strip_tags($_GET['f'])));
$lnnr=1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo htmlXspecialchars($_GET['n']) ?></title>
<?php require('backendcss.php'); ?>
<script>
function fnmkreload() {
document.cookie='fd2y=-1';
location.href=location.href;
}
</script>
</head>
<style>
mark{background:yellow};
</style>
<body id="body" style="font-size:80%" onfocus="if (mkreload==1) fnmkreload()">
<script>
mkreload=0;

function cedit(ida,event) {
el=ida.getElementsByTagName('a');
for(i=0;i<el.length;i++) {
if (event.altKey) {
document.cookie='fd2y='+el[i].id.replace(/i/,'');
location.href='../formdataedit.php?<?php echo $_SERVER['QUERY_STRING'] ?>&a=1&id='+el[i].id.replace(/i/,'');
}
}
}
</script>
<?php
require('../config.php');
if ($formdataRqHIADRI!='no') require('accadmin.inc.php');
?>
<h3 style="display:inline"><?php echo '<a onclick="mkreload=1" target="_blank" href="../formdatainsert.php?a=1&c='.htmlXspecialchars($_GET['n']).'" title="open form (e. g. to enter new data)"><span style="background:blue;color:white">&nbsp;+&nbsp;</span>'.htmlXspecialchars($_GET['n']).'</a>' ?></h3>

Show
<select onchange="location.href='formdatamail.php?n=<?php echo $_GET['n']?>&s='+this.value+'&f=<?php echo $_GET['f']?>&o=<?php echo $_GET['o']?>'">
<option value="0">all with any status 1) &ndash; 4)</option>
<option <?php if ($_GET['s']==1) echo 'selected="selected"' ?> value="1">all with status 1) new</option>
<option <?php if ($_GET['s']==2) echo 'selected="selected"' ?> value="2">all with status 2) pending</option>
<option <?php if ($_GET['s']==3) echo 'selected="selected"' ?> value="3">all with status 3) problem</option>
<option <?php if ($_GET['s']==4) echo 'selected="selected"' ?> value="4">all with status 4) done</option>
<option <?php if ($_GET['s']==5) echo 'selected="selected"' ?> value="5">items deleted the last 30 days</option>
<option <?php if ($_GET['s']==6) echo 'selected="selected"' ?> value="6">former versions of edited items</option>
<option <?php if ($_GET['s']==7) echo 'selected="selected"' ?> value="7">copy&amp;paste csv/excel import</option>
</select>
<button onclick="location.href=location.href">refresh</button>

&nbsp;&nbsp;<span style="color:orange;font-weight:bold;font-size:120%">&#8898;</span>&nbsp;

<?php require('formdata.filterlabel.php') ?>

<input onkeypress="handleKeyPress(event)" id="filt" type="text">

<script type="text/javascript">
function hd(string){
    var text, p=document.createElement('p');
    p.innerHTML=string;
    text= p.innerText || p.textContent;
    p.innerHTML='';
    return text;
}


function replace(string,text,by) {
// Replaces text with by in string
var strLength = string.length, txtLength = text.length;
if ((strLength == 0) || (txtLength == 0)) return string;
var i = string.indexOf(text);
if ((!i) && (text != string.substring(0,txtLength))) return string;
if (i == -1) return string;
var newstr = string.substring(0,i) + by;
if (i+txtLength < strLength)
newstr += replace(string.substring(i+txtLength,strLength),text,by);
return newstr;
}

function handleKeyPress(e){
var key=e.keyCode || e.which;
if (key==13){
if (document.getElementById('filt').value=='') document.getElementById('filt').value='<?php echo ($_GET['o'])?>';location.href='formdatamail.php?n=<?php echo $_GET['n']?>&f='+escape(replace(replace(document.getElementById('filt').value,'"',''),'·',''))+'&s=<?php echo $_GET['s']?>';
}
}

function senden() {
if (document.getElementById('filt').value=='') document.getElementById('filt').value='<?php echo ($_GET['o'])?>';location.href='formdatamail.php?n=<?php echo $_GET['n']?>&f='+escape(replace(replace(replace(document.getElementById('filt').value,'"',''),'·',''),'÷',''))+'&s=<?php echo $_GET['s']?>';
}

function dbl(that) {
if (('x'+that).indexOf('<iframe')<1 && ('x'+that).indexOf('<IFRAME')<1 && ('x'+that).indexOf('<input')<1 && ('x'+that).indexOf('<INPUT')<1 && ('x'+that).indexOf('<textarea')<1 && ('x'+that).indexOf('<TEXTAREA')<1) {
document.getElementById('filt').value=that;
senden();
}
}
</script>

<button onclick="senden()">set</button>
<button onclick="unset()">unset</button>
<script>
function unset() {
if (document.getElementById('filt').value=='') document.getElementById('filt').value='<?php echo ($_GET['o'])?>';location.href='formdatamail.php?f=&n=<?php echo $_GET['n']?>&o='+escape(document.getElementById('filt').value)+'&s=<?php echo $_GET['s']?>';
}
document.getElementById('filt').value=unescape('<?php echo ($_GET['f']) ?>');
</script>
&nbsp;

<span style="color:orange;font-weight:bold;font-size:120%">&rArr;</span>&nbsp;

<span id="isn" style="color:orange;font-weight:bold"></span>

&nbsp;

<?php include('formdatacookies.inc.php');?>

&nbsp;

<a href="formdatamailcsv.php?<?php echo $_SERVER['QUERY_STRING'] ?>"> Excel (CSV)</a>

&nbsp;

<a href="../chart.php?<?php echo $_SERVER['QUERY_STRING'] ?>&fdb=<?php echo $_COOKIE['fdbool']?>&fdo=<?php echo $_COOKIE['fdorder']?>" target="_blank"> Chart</a>

<?php
if($_GET['s']==7) echo '<p><button onclick="location.href=(\'import.php?n='.$_GET['n'].'\')">import data from Excel by copying and pasting it into the textarea form field appearing when clicking this button</button> <small>You see/will see the imported records on the page at hand. To activate them, change their status. Bulk status changer see bottom of this page.</small></p>';
?>

&nbsp;<?php
require_once('connect.inc.php');
require_once('formdata.query.php');

if ($_GET['s']==5) echo '<br><hr><center><small>Items are automatically removed from this recycle bin list '.$toolate.' days after their deletion was selected.</small></center><hr>';

$num_a = mysql_num_rows($result_a);

echo "<script>document.getElementById('isn').innerHTML='n=".$num_a."'</script>";

$csv='';
$oldhead='';
$authusers='';
$emails='';
$rowaid='';
$trattribs='onclick="cedit(this.firstChild,event)" style="background:#e1e1e1" onmouseover="this.style.background=\'#eeeeee\'" onmouseout="this.style.background=\'#e1e1e1\'"';

$tennum=$num_a;
if($tennum>10)$tennum=10;

for ($i_a=0; $i_a<$tennum; $i_a++) {
$row_a = mysql_fetch_array($result_a);

if ($row_a['authuser']!='') if (strpos('xx'.$authusers,'.,!,.'.$row_a['authuser'].'.,!,.')>1) $row_a['authuser']='<span style="background:pink" title="duplicate?">'.$row_a['authuser'].' ÷<span>';

$authusers.='.,!,.'.$row_a['authuser'].'.,!,.';

$rowaid.=$row_a['id'].',';

$row_a['headcsv']=str_replace('_mandatory','*',$row_a['headcsv']);
if ($oldhead!=$row_a['headcsv']) $csv.='<small>N°</small><br>ID;Status;Notes;Authuser;'.$row_a['headcsv'].'Sent;IP'."\r\n";
if($i_a==0) $lasthead=$csv;
$oldhead=$row_a['headcsv'];

$datacsv=explode(';',$row_a['datacsv']);

for ($dwi=0;$dwi<tnuoc($datacsv);$dwi++) {
$datacsv[$dwi]=dowords($datacsv[$dwi]);

if (strpos($datacsv[$dwi],'@')>0) {
if ($datacsv[$dwi]!='') if (strpos('xx'.$emails,'.,!,.'.$datacsv[$dwi].'.,!,.')>1) $datacsv[$dwi]=':<:span style="background:pink" title="duplicate?":>:'.$datacsv[$dwi].' ÷:<:span>';
$emails.='.,!,.'.$datacsv[$dwi].'.,!,.';
}
}
$row_a['datacsv']=implode(';',$datacsv);
unset($datacsv);
$delete='delete now';
if ($_GET['s']==5) $delete='deleted on '.date('Y-m-d H:i',$row_a['lastmut']);
$noteslogic='onchange="document.nf'.$row_a['id'].'.submit()" onkeyup="document.nf'.$row_a['id'].'.submit()"';
$notesvalue=rawurlencode(str_replace(',.',';',str_replace('&amp;#','&#',htmlXspecialchars($row_a['notes']))));
$notesscript='<script><!!!u5dl_mtr!!! document.getElementById("no'.$row_a['id'].'").value=hd(unescape(document.getElementById("no'.$row_a['id'].'").value)) !!!u5dl_mtr!!!></script>';
$notesformstart='<form method="post" name="nf'.$row_a['id'].'" target="ifr'.$row_a['id'].'" action="notesave.php?id='.$row_a['id'].'">';
$notesformend='</form>';
if($noteslines==1)$notes=$notesformstart.'<input id="no'.$row_a['id'].'" name="note" style="width:'.$noteswidth.'px" type="text" '.$noteslogic.' value="'.$notesvalue.'">'.$notesformend;
else $notes=$notesformstart.'<textarea id="no'.$row_a['id'].'" name="note" rows="'.$noteslines.'" style="width:'.$noteswidth.'px" type="text" '.$noteslogic.'><!!!u5dl_mtr!!!'.$notesvalue.'!!!u5dl_mtr!!!></textarea>'.$notesformend;
$csv.='<a title="edit: Click here or Alt+Click anywhere" id="i'.$row_a['id'].'" style="text-decoration:none" onclick="document.cookie=\'fd2y='.$row_a['id'].'\'" href="../formdataedit.php?'.$_SERVER['QUERY_STRING'].'&a=1&id='.$row_a['id'].'"><small>'.$lnnr++.'</small><br>'.$row_a['id'].'</a>;<iframe scrolling="no" width="100%" height="3" frameborder="0" name="ifr'.$row_a['id'].'"></iframe><select onchange="ifr'.$row_a['id'].'.location.href=\'statussave.php?status=\'+this.value+\'&id='.$row_a['id'].'\'" id="sel'.$row_a['id'].'"><option value="1">1) new</option><option value="2">2) pending</option><option value="3">3) problem</option><option value="4">4) done</option><option value="5">'.$delete.'</option><option value="6">former version</option><option value="7">imported</option></select><script src=sel.php?id='.$row_a['id'].'&status='.$row_a['status'].'></script>;'.$notes.$notesscript.';'.str_replace(';',',.',($row_a['authuser'])).';'.str_replace("<br />"," | ",str_replace("\n","",str_replace("\r","",nl2br(str_replace(':&lt,.:','<',str_replace(':&gt,.:','>',str_replace('<','&lt,.',str_replace('>','&gt,.',($row_a['datacsv']))))))))).(date('Y.m.d H:i:s',$row_a['time'])).';'.$row_a['ip']."<br />";
}
$dnummer=date("YmdHis");
$echo = str_replace(',.',';',str_replace('<tr '.$trattribs.'><td ondblclick="dbl(this.innerHTML)"><b>','<tr '.$trattribs.' style="font-weight:bold"><td ondblclick="dbl(this.innerHTML)"><b>','<table><tr '.$trattribs.'><td ondblclick="dbl(this.innerHTML)">'.str_replace(';','</td><td ondblclick="dbl(this.innerHTML)">',str_replace('<br />','</td></tr><tr '.$trattribs.'><td ondblclick="dbl(this.innerHTML)">',nl2br($csv))).'</tr></table>'));

for ($k=0;$k<tnuoc($keywords);$k++) {
$echo =  highlight(str_replace('<','&lt;',$keywords[$k]), $echo);
}
$echo = str_replace('<!!!u5dl_mtr!!!','',$echo);
$echo = str_replace('!!!u5dl_mtr!!!>','',$echo);
echo str_replace('</span>;',';</span>',$echo);

function prepare_search_term($str,$delim='#') {
$search = preg_quote($str,$delim);

$search = preg_replace('/[aàáâãåäæAÀÁÂÃÄÅÆ]/i', '[aàáâãåäæAÀÁÂÃÄÅÆ]', $search);
$search = preg_replace('/[eèéêëEÈÉÊË]/i', '[eèéêëEÈÉÊË]', $search);
$search = preg_replace('/[iìíîïIÌÍÎÏ]/i', '[iìíîïIÌÍÎÏ]', $search);
$search = preg_replace('/[oòóôõöøoOÒÓÔÕÖØO]/i', '[oòóôõöøoOÒÓÔÕÖØO]', $search);
$search = preg_replace('/[uùúûüUÙÚÛÜ]/i', '[uùúûüUÙÚÛÜ]', $search);
$search = preg_replace('/[nñNÑ]/i', '[nñNÑ]', $search);
$search = preg_replace('/[cçCÇ]/i', '[cçCÇ]', $search);
    // add more characters...
    return $search;
}

function highlight($searchtext, $text) {
    $searchtext=str_replace(',.',';',$searchtext);
    $search = prepare_search_term($searchtext);
    $search;
	return preg_replace('#(?![^&;]*;)(?![^<>]*>)' . $search . '#i', '<mark>$0</mark>', $text);
}
?>
<script>
function replace(string,text,by) {
// Replaces text with by in string
var strLength = string.length, txtLength = text.length;
if ((strLength == 0) || (txtLength == 0)) return string;
var i = string.indexOf(text);
if ((!i) && (text != string.substring(0,txtLength))) return string;
if (i == -1) return string;
var newstr = string.substring(0,i) + by;
if (i+txtLength < strLength)
newstr += replace(string.substring(i+txtLength,strLength),text,by);
return newstr;
}

document.getElementById('filt').value=replace(document.getElementById('filt').value,'&lt;','<');
document.getElementById('filt').value=replace(document.getElementById('filt').value,'&gt;','>');
</script>

<?php

$rowaid=explode(',',$rowaid);
$rowaid=implode("','",$rowaid);
$rowaid="'".$rowaid."x'";
$rowaid=str_replace("''","'x'",$rowaid);

if ($num_a>0) {
?>
<br>
<form name="mass" method="post" action="mass.php?<?php echo $_SERVER['QUERY_STRING']?>" onSubmit="return mvalidate()">
<input type="hidden" name="rowaid" value="<?php echo $rowaid; ?>">
&nbsp;&nbsp;&nbsp;<select name="newstatus">
<option value="0">SET ALL ITEMS OF THE SELECTION ABOVE &hellip;</option>
<option value="1">&hellip; to status 1) new</option>
<option value="2">&hellip; to status 2) pending</option>
<option value="3">&hellip; to status 3) problem</option>
<option value="4">&hellip; to status 4) done</option>
<option value="5">&hellip; to status 5) DELETED</option>
<option value="6">&hellip; to former versions</option>
<option value="7">&hellip; to imported</option>
</select>
<input type="submit" value="go" />
<?php require('t1.php') ?></form>

<script>
function mvalidate(){
if (document.mass.newstatus.value=='0') return false;

else {
if (confirm('Are you sure?')) return true;
else return false;
}
}
</script>
<?php }
$rrr=1;
function dowords($phrase) {

if (strpos('x'.$phrase,'http://')==2 || strpos('x'.$phrase,'https://')==2 || strpos('x'.$phrase,'ftp://')==2) {
global $scripturi;
$scripturix=str_replace(basename($scripturi),'',$scripturi);
$scripturix=str_replace('/u5admin','',$scripturix);
$ext=explode('.',$phrase);
$ext=$ext[tnuoc($ext)-1];
$title=str_replace($scripturix,'',$phrase);
$title=str_replace('fileversions/useruploads/','',$title);
if (str_replace(' ','',trim($ext))=='' || $_COOKIE['fdtrunc']=='off') return '·'.str_replace('·','',':<:a href="'.$phrase.'" title="'.$title.'" target="_blank":>:'.$title.':<:/a:>:');
else return '·'.str_replace('·','',':<:a href="'.$phrase.'" title="'.$title.'" target="_blank":>:'.$ext.':<:/a:>:');
}
else if ($_COOKIE['fdtrunc']=='off') return $phrase;

else {

global $rrr;
$phrase=explode(' ',$phrase);

$newphrase='';
for ($i=0;$i<tnuoc($phrase);$i++) {
$newphrase.=$phrase[$i].' ';
if ($i==10 && tnuoc($phrase)>20) {
$rr='rr'.$rrr++;
$newphrase.=' :<:span title="show more" onclick="this.style.display=\'none\',.document.getElementById(\'id'.$rr.'\').style.display=\'inline\'":>::<:a href="javascript:void(0)":>:&hellip,.:<:/a:>::<:/span:>: :<:span id="id'.$rr.'" style="display:none":>: ';
}
}
if ($i>=10) $newphrase.=':<:/span:>: ';

return $newphrase;
}
}
?>
<br><br>
You are in the view "mail" which shows only the first 10 records. Serial mail will be sent to all <?php echo $num_a ?> records.
<br><br>
<?php if ($num_a>0) include('mailing.inc.php')?>
<br><br><br><br><br>
<iframe width="0" height="0" frameborder="0" src="cleanuseruploads.php"></iframe>
<?php
$sql_a="SELECT name FROM resources WHERE name='modify!formdata2!php' AND deleted!=1";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if ($num_a>0) {
$_GET['c']='modify!formdata2!php';
include('../u5sys.content.php');
}
?>

<script>
<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>X=0;
<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>Y=0;
if (document.cookie.indexOf('<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>X=')>-1){ 
<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>X=('; '+document.cookie).split('; <?php
 echo 'i'.str_replace('!','_',$_GET['n']) ?>X=')[1].split(';')[0];
}
if (document.cookie.indexOf('<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>Y=')>-1){ 
<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>Y=('; '+document.cookie).split('; <?php
echo 'i'.str_replace('!','_',$_GET['n']) ?>Y=')[1].split(';')[0];
}
if(<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>X!=''||<?php
 echo 'i'.str_replace('!','_',$_GET['n']) ?>Y!='')window.scroll(<?php
 echo 'i'.str_replace('!','_',$_GET['n']) ?>X-0,<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>Y-0);
document.getElementById('body').onscroll=function(){
document.cookie='<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>X='+escape(window.pageXOffset)+';'
document.cookie='<?php echo 'i'.str_replace('!','_',$_GET['n']) ?>Y='+escape(window.pageYOffset)+';'
};

if (document.cookie.indexOf('fd2y=')>-1){ 
fd2y='i'+('; '+document.cookie).split('; fd2y=')[1].split(';')[0];
if(document.getElementById(fd2y))if(document.getElementById(fd2y).parentNode)if(document.getElementById(fd2y).parentNode.parentNode){
el=document.getElementById(fd2y).parentNode.parentNode.getElementsByTagName('td');
for(i=0;i<el.length;i++) {
el[i].style.background='lightgreen';	
}
}
}
</script>
<?php if($_COOKIE['fd2y']==-1) {
$sql_a="SELECT id FROM formdata WHERE formname='".mysql_real_escape_string($_GET['n'])."' ORDER BY id DESC LIMIT 0,1";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);

echo "<script>
function greennew() {
fd2y='i".$row_a['id']."';
if(document.getElementById(fd2y))if(document.getElementById(fd2y).parentNode)if(document.getElementById(fd2y).parentNode.parentNode){
el=document.getElementById(fd2y).parentNode.parentNode.getElementsByTagName('td');
for(i=0;i<el.length;i++) {
el[i].style.background='lightgreen';	
}
}
}
setTimeout(\"greennew()\",777);
</script>";
}
?>
</body>
</html>
