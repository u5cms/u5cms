<?php
require_once('connect.inc.php');
if ($_COOKIE['i1_l']=='') eikooctes('i1_l', 'P', time()+3600*24*365*10,'/');
if ($_COOKIE['i2_l']=='') eikooctes('i2_l', '1', time()+3600*24*365*10,'/');
if($_SERVER['QUERY_STRING']=='i') eikooctes('i1_l', '1', time()+3600*24*365*10,'/');
if($_SERVER['QUERY_STRING']=='i') eikooctes('i2_1', '1', time()+3600*24*365*10,'/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /><title>u5CMS - Welcome <?php echo ehtml($_SERVER['PHP_AUTH_USER'])?></title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	i1.document.form1.submit();
    i1.ctrls();
	i2.document.form1.submit();
    i2.ctrls();
})
</script>
<?php require('backendcss.php'); ?>
<style>
.ifrscw {
-webkit-overflow-scrolling: touch;
overflow-y: scroll;
overflow-x: scroll;
height:1000px;
width:555px;
}
.ifrscw2 {
-webkit-overflow-scrolling: touch;
overflow-y: scroll;
overflow-x: scroll;
height:980px;
width:260px;
}
</style>
</head>
<body style="background:#7695AD;margin:0;padding:0;overflow:hidden" id="idbody" onload="loader()">
<table width="100%" border="0" cellspacing="1" cellpadding="0">
  <tr>
    <td width="555" valign="top" id="td1" height="1000"><div class="ifrscw"><iframe name="i1" frameborder="0" src="editor.php" width="555" height="1000" style="margin-top:-5px" scrolling="yes"></iframe></div></td>
    <td width="555" valign="top" id="td2" height="1000"><div class="ifrscw"><iframe name="i2" frameborder="0" src="editor.php" width="555" height="1000" style="margin-top:-5px" scrolling="yes"></iframe></div></td>
    <td width="260" valign="top" id="td3" height="1000"><iframe name="save" frameborder="0" src="iblank.php" width="260" scrolling="no" height="20" style="margin-top:-3px"></iframe>
      <div class="ifrscw2"><iframe id="i3iframe" name="i3" frameborder="0" src="pidvesa.php" width="260" height="1000" style="margin-top:-3px"></iframe></div>
      <iframe name="i4a" frameborder="0" src="blank.php" width="1" height="1" style="display:none"></iframe>
      <iframe name="i4b" frameborder="0" src="blank.php" width="1" height="1" style="display:none"></iframe>
      <iframe name="i4c" frameborder="0" src="blank.php" width="1" height="1" style="display:none"></iframe>
      <iframe name="i4d" frameborder="0" src="blank.php" width="1" height="1" style="display:none"></iframe>
      <iframe name="i4e" frameborder="0" src="blank.php" width="1" height="1" style="display:none"></iframe>
      </td>
  </tr>
</table><div style="display:none" id="documenttitle"></div>
<script>
res=-20;
function loader() {
//sizer();
}

function sizer() {
//document.getElementById('td1').height=document.documentElement.clientHeight-res;
//document.getElementById('td2').height=document.documentElement.clientHeight-res;
//document.getElementById('td3').height=document.documentElement.clientHeight-res;
//document.getElementById('i3iframe').height=document.documentElement.clientHeight-20;
setTimeout("resizer()",1111);
}

function resizer() {
//if(window.innerWidth<1600)document.getElementById('td3').style.width='16%'
//else document.getElementById('td3').style.width='260px'
leftname='';
if(i1) if(i1.document) if(i1.document.form1) if(i1.document.form1.page) leftname=i1.document.form1.page.value;
leftstar='';
if(i1) if(i1.document) if(i1.document.getElementById('savebutton')) if(i1.document.getElementById('savebutton').innerHTML.indexOf('*')>0) leftstar='*';
rightname='';
if(i2) if(i2.document) if(i2.document.form1) if(i2.document.form1.page) rightname=i2.document.form1.page.value;
rightstar='';
if(i2) if(i2.document) if(i2.document.getElementById('savebutton')) if(i2.document.getElementById('savebutton').innerHTML.indexOf('*')>0) rightstar='*';
lrstar=''
if(leftstar=='*' || rightstar=='*') lrstar='*';	
if(leftname==rightname) documenttitle=escape(lrstar+rightname+' | <?php echo $_SERVER['PHP_AUTH_USER'] ?>');
else documenttitle=escape(leftstar+leftname+' | '+rightstar+rightname+' | <?php echo $_SERVER['PHP_AUTH_USER'] ?>');

document.getElementById('documenttitle').innerHTML=unescape(documenttitle);
document.title=document.getElementById('documenttitle').innerHTML;

//if (document.getElementById('td3').height!=document.documentElement.clientHeight-res) sizer();
//else 
setTimeout("resizer()",1111);
}
sizer();
</script>
<?php
include('zr.php');
if (file_put_contents('../r/x','x')) {
echo '<!--w ../r/ ok -->';
unlink('../r/x');
}
else echo '<script>alert("PROBLEM: The server does not have the right to write into the folder named \'r\'.\n\nEFFECTS: You cannot create or upload files in the backend nor change the htmltemplate or css\'s, and your customers cannot upload files in your forms where the script \'Pupload\' is used.\n\nSOLUTION: CHMOD the folder \'r\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>';

if (file_put_contents('../fileversions/x','x')) {
echo '<!--w ../r/ ok -->';
unlink('../fileversions/x');
}
else echo '<script>alert("PROBLEM: The server does not have the right to write into the folder named \'fileversions\'.\n\nEFFECTS: Your customers cannot upload files in your forms where the script \'upload\' is used, and there is no versioning of your files uploaded in the backend.\n\nSOLUTION: CHMOD the folder \'fileversions\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>';
?>
</body>
</html>