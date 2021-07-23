<?php require('iniset.inc.php');?>
<?php require_once('connect.inc.php'); ?>
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
$post_max_size = let_to_num(ini_get('post_max_size'));
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
$moved=0;
$nojpg=0;
$yesjpg=0;
$file_ary = reArrayFiles($_FILES['userfile']);


if(count($file_ary)>ini_get('max_file_uploads')-1)die('<script>document.getElementById("body").style.background="red";alert("Error, no file uploaded. Reason: You tried to upload more than '.(ini_get('max_file_uploads')-1).' files at once.");parent.location.href=parent.location.href</script>');

foreach ($file_ary as $file) {

$file['name']=str_replace(chr(0),'',$file['name']);
$ext=explode('.',$file['name']);
$ext=$ext[count($ext)-1];
$ext=strtolower($ext);
$ext=str_replace('jpeg','jpg',$ext);


if($ext!='jpg') $nojpg++;
else $yesjpg++;

if($ext=='jpg') {
for ($i=1000;$i>99;$i--) {
if (!file_exists('../r/'.$_POST['name'].'/'.$_POST['name'].'_'.$i.'.'.$ext)) $filename=$_POST['name'].'_'.$i.'.'.$ext;
}

@mkdir('../r/'.$_POST['name'],0777);
if (!move_uploaded_file($file['tmp_name'], '../r/'.$_POST['name'].'/'.$filename)) {
$ok='error';
}
else {
copy('../r/'.$_POST['name'].'/'.$filename,'../fileversions/'.(date('Ymd')).'-'.$filename);
$moved++;
$ok='ok';
}
trxlog('upload '.$_POST['name'].' '.$ok.' '.$filenames);

$sql_a="UPDATE resources SET lastmut='".time()."' WHERE name='".mysql_real_escape_string($_POST['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
	   echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}
}
}

if(count($file_ary)>1) if ($nojpg>0) echo '<script>document.getElementById("body").style.background="lightblue";alert("Not all files were jpg-pictures!\n\nOnly the jpg-pictures have been saved ('.$yesjpg.' files).\n\nNon-jpg-files have been skipped ('.$nojpg.') files.")</script>';

if($moved>0) echo '<script>parent.files.location.href=parent.files.location.href;if (parent && parent.opener && parent.opener.parent && parent.opener.parent.save) parent.opener.parent.save.location.href=\'done.php?n=uploaded '.$_POST['name'].$_GET['l'].'\'</script><b>ok</b><script>setTimeout("location.href=\'sendfilea.php?name='.$_POST['name'].'&l='.$_GET['l'].'&typ='.$_GET['typ'].'\'",111)</script>';
else echo '<script>document.getElementById("body").style.background="red";alert("Error, no file uploaded.\\n\\nIs the file too big? Max file size='.($max_upload_size/1024/1024).' MB\\n\\nIf multiple files, is the total too big? Post max size='.($post_max_size/1024/1024).' MB\\n\\nIf one file, was it a jpg-file?");parent.location.href=parent.location.href</script>';

function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}
?>
</body>
</html>