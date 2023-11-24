<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED);
$_GET['i']=htmlspecialchars(strip_tags($_GET['i']));
require_once '../myfunctions.inc.php';
require_once '../config.php';
if(function_exists('UPLOADexec'))UPLOADexec();
if($_GET['k']!=sha1(date('Ymd').$password.$sessioncookiehashsalt)&&$_GET['k']!=sha1(date('Ymd',time()-24*60*60).$password.$sessioncookiehashsalt))die('ERROR: Authorization failed.');

require('../getauthuser.inc.php');
if ($allowuseruploads != 'yes') die('document.write("ERROR: Upload not available. REASON: $allowuseruploads is not set to yes in config.php");');
if($sticksessiontoip=='yes')$serverremoteaddr=$_SERVER['REMOTE_ADDR'];
else $serverremoteaddr='';
$filehash1=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$_GET['i'].date('Ymd'));
$filehash2=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$_GET['i'].date('Ymd',time()-24*60*60));
if ($filehash1 != $_GET['h'] && $filehash2 != $_GET['h']) die('<script>alert("ERROR: Rejected, referer wrong. Perhaps your IP address changed. Please try again!");location.href="upload.php?i='.$_GET['i'].'&k='.$_GET['k'].'";</script>');
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <?php if (file_exists('../r/cssupload.css')) echo '<link rel="stylesheet" href="r/cssupload.css?' . filemtime('../r/cssupload.css') . '" type="text/css" title="base"/>' ?>
    <style type="text/css">
        body {
            margin-left: 0px;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
        }
    </style>
</head>

<body>
<?php
$_FILES['userfile']['name'] = str_replace(chr(0), '', $_FILES['userfile']['name']);
$ext = explode('.', $_FILES['userfile']['name']);
$ext = strtolower($ext[tnuoc($ext) - 1]);
require('../configallowedfileextensions.php');
$okext = $extokFRONTend;
if (!in_array($ext, $okext)&&$renamenotallowedfileextensionstotxt=='yes') $_FILES['userfile']['name'] .= '.txt';
else if (!in_array($ext, $okext)) die('<script>alert(".'.$ext.' is not allowed (according to configallowedfileextensions.php)!");location.href="upload.php?i='.$_GET['i'].'&k='.$_GET['k'].'";</script>');

function mkfilename($s)
{
    $t = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.-_';
    $s = trim($s);
    $n = '';
    for ($i = 0; $i < strlen($s); $i++) {
        if (str_replace($s[$i], '', $t) != $t) $n .= $s[$i];
    }
    return $n;
}

function trtml($s) {
global $showemailinuploadfilenames;
if($showemailinuploadfilenames=='no') return '';	
else if($showemailinuploadfilenames=='hash') return crc32($s);	
else return $s;	
}

function mkascii($s)
{
$s=utf8_decode($s);
    $badascii = array(" ", ":", "¦", "|", "@", "&", "¢", "£", "¤", "¥", "€", "§", "¨", "©", "ª", "¬", "®", "¯", "°", "±", "´", "µ", "¶", "…", "·", "¸", "º", "÷", "†", "ß", "„", "“", "”", "‚", "‘", "’", "«", "»", "‹", "›", "¡", "¿", "–", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï", "Ñ", "Ò", "Ó", "Ô", "Õ", "#", "Ö", "Ø", "Œ", "Ù", "Ú", "Û", "Ü", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï", "ñ", "ò", "ó", "ô", "õ", "#", "ö", "ø", "œ", "ù", "ú", "û", "ü", "ÿ", "'", "%", "/", "\\", "<", ">", ",", ";", "+", "=", "(", ")", "[", "]", "{", "}", "*", "?", "!", "`", "\"");
    $goodascii = array("_", "", "", "", "AT", "u", "c", "L", "", "Y", "E", "S", "", "c", "a", "N", "R", "", "o", "pm", "", "u", "P", "", "", "", "o", "", "", "ss", "", "", "", "", "", "", "", "", "", "", "", "", "", "A", "A", "A", "A", "Ae", "A", "Ae", "C", "E", "E", "E", "E", "I", "I", "I", "I", "N", "O", "O", "O", "O", "", "Oe", "O", "Oe", "U", "U", "U", "Ue", "a", "a", "a", "a", "ae", "a", "ae", "c", "e", "e", "e", "e", "i", "i", "i", "i", "n", "o", "o", "o", "o", "", "oe", "o", "oe", "u", "u", "u", "ue", "y", "", "", "", "", "", "", "", "", "u", "", "", "", "", "", "", "", "x", "", "", "", "_");
    $s = str_replace($badascii, $goodascii, $s);
    return mkfilename($s);
}

$rand = rand(1000000000, 9999999999);
@mkdir('../fileversions/useruploads');
if (move_uploaded_file($_FILES['userfile']['tmp_name'], '../fileversions/useruploads/' . mkascii(trtml($_SERVER['PHP_AUTH_USER']) . '_' . $rand . '_' . $_FILES['userfile']['name']))) {
    echo '<span style="color:white;background:green" class="cssupload">OK:&nbsp;' . mkascii($ext) . '&nbsp;&#10004;</span>&nbsp;<span title="DELETE FILE" style="cursor:pointer;color:white;background:black" onclick="cf=confirm(\'DELETE FILE?\');if(cf) {deletefile()}">&#215;</span>&nbsp;<script>parent.document.getElementById("userupload' . $_GET['i'] . '").value="' . str_replace('upload/uploaddone.php', 'fileversions/useruploads/', $scripturi) . mkascii(trtml($_SERVER['PHP_AUTH_USER']) . '_' . $rand . '_' . $_FILES['userfile']['name']) . '";parent.document.getElementById("userupload' . $_GET['i'] . '").style.display="xone";</script>';
} else echo '<span style="color:white;background:red">ERROR</span>
<script>
setTimeout("alert(\'ERROR\');location.href=\'upload.php?i=' . $_GET['i'] . '\'",777);
</script>
';
?>
<script>
    function deletefile() {
        location.href = 'upload.php?i=<?php echo $_GET['i']?>&k=<?php echo $_GET['k']?>';
        parent.document.getElementById("userupload<?php echo $_GET['i']?>").value = '';
        parent.document.getElementById("userupload<?php echo $_GET['i']?>").style.display = 'block';
        parent.document.getElementById("userupload<?php echo $_GET['i']?>").style.background = 'gray';
		if(typeof parent.u5uploaddetected === "function")parent.u5uploaddetected(2);
    }
if(typeof parent.u5uploaddetected === "function")parent.u5uploaddetected(1);
if(parent)if(parent.window)parent.window.focus();
</script>
</body>

</html>
