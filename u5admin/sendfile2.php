<?php require('iniset.inc.php');?>
<?php require_once('connect.inc.php');require_once('t2.php'); ?>
<?php
require('../config.php');
if($sticksessiontoip=='yes')$serverremoteaddr=$_SERVER['REMOTE_ADDR'];
else $serverremoteaddr='';
$filehash1=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$_GET['i'].date('Ymd'));
$filehash2=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$_GET['i'].date('Ymd',time()-24*60*60));
if ($filehash1!=$_GET['h'] && $filehash2!=$_GET['h']) die('<script>alert("ERROR: Rejected, referer wrong. Perhaps your IP address changed. Please try again!");history.go(-1);</script>');

function let_to_num($v){ //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
    return $ret;
}
$max_upload_size = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900" text="#FFFFFF"><script>parent.upend();</script>
<?php
require('archivecheck.inc.php');
$_POST['name']=basename($_POST['name']);
$_GET['l']=basename($_GET['l']);
$_FILES['userfile']['name']=str_replace(chr(0),'',$_FILES['userfile']['name']);
$ext=explode('.',$_FILES['userfile']['name']);
$ext=strtolower($ext[tnuoc($ext)-1]);
require('../configallowedfileextensions.php');
$okext=$extokBACKend;
if (!in_array($ext,$okext)) {
echo "<script>
document.getElementById('body').style.background='red';
alert('Your file has the extension $ext which is not whitelisted in conficallowedfileextensions.php. Therefore the extension .txt is added what makes the file useless. If you want to publish $ext-files you have to whitelist $ext.');
</script>";
$ext.='.txt';
}

if($_GET['typ']=='i' && $ext!='jpg' && $ext!='JPG' && $ext!='jpeg' && $ext!='JPEG') die('<script>document.getElementById("body").style.background="red";alert("Error: picture format must be jpg RGB (not CMYK) with file extension .jpg and not '.$ext.'");parent.location.href=parent.location.href</script>');

     $path='../r/'.$_POST['name'];
     if ($handle = @opendir($path))  {
     while (false !== ($file = readdir($handle)))  {

     $exta=explode('.',$file);
     $exta=$exta[tnuoc($exta)-1];

     if ($_FILES['userfile']['tmp_name']!='') if (str_replace('.','',$file)!='') @unlink('../r/'.$_POST['name'].'/'.$_POST['name'].$_GET['l'].'.'.$exta);
     }
     }

@mkdir('../r/'.$_POST['name'],0777);
if (!move_uploaded_file($_FILES['userfile']['tmp_name'], '../r/'.$_POST['name'].'/'.$_POST['name'].$_GET['l'].'.'.$ext)) {
echo '<script>document.getElementById("body").style.background="red";alert("Error, no file uploaded.\\n\\nIs the file too big? Max file size='.($max_upload_size/1024/1024).' MB");parent.location.href=parent.location.href</script>';
$ok='error';
}
else {

copy('../r/'.$_POST['name'].'/'.$_POST['name'].$_GET['l'].'.'.$ext,'../fileversions/'.(date('Ymd')).'-'.$_POST['name'].$_GET['l'].'.'.$ext);

echo '<script>parent.files.location.href=parent.files.location.href;if (parent && parent.opener && parent.opener.parent && parent.opener.parent.save) parent.opener.parent.save.location.href=\'done.php?n=uploaded '.$_POST['name'].$_GET['l'].'\'</script><b>ok</b><script>setTimeout("location.href=\'sendfile.php?name='.$_POST['name'].'&l='.$_GET['l'].'&typ='.$_GET['typ'].'\'",111)</script>';
$ok='ok';
}
trxlog('upload '.$_POST['name'].' '.$ok.' '.$_FILES['userfile']['name']);

$sql_a="UPDATE resources SET lastmut='".time()."' WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	   echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
?>
</body>
</html>