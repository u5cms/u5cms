<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	cchanges=0;document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php include('pidvesanavi.inc.php');?>
<h1>Preview init scroll</h1>

<?php
$pvileft='1';
$pvitop='1';

$pvijs=@file_get_contents('../r/pviscroll.js');

if($pvijs!==false) {
	if(preg_match('/\bpvileft\s*=\s*([0-9]+)/',$pvijs,$m)) $pvileft=$m[1];
	if(preg_match('/\bpvitop\s*=\s*([0-9]+)/',$pvijs,$m)) $pvitop=$m[1];
}

if($pvileft<1) $pvileft=1;
if($pvitop<1) $pvitop=1;
?>

<p>Define the initial scroll position in the preview frame. To properly calibrate, open a very long page in the editor in one frame and as preview in the other frame.</p>

<form onsubmit="cchanges=0;document.querySelectorAll('.asterisk').forEach(e=>e.classList.add('blink_me'));" action="pviscrollsave.php" target="save" name="form1">

&nbsp;left:<br />
<input value="<?php echo $pvileft ?>" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value)" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value)" type="text" id="pvileft" name="pvileft">
<br />
<br />
&nbsp;top:<br />
<input value="<?php echo $pvitop ?>" onchange="if (this.value!=validated(this.value)) this.value=validated(this.value)" onkeyup="if (this.value!=validated(this.value)) this.value=validated(this.value)" type="text" id="pvitop" name="pvitop">
<br />
<br />
<input type="submit" value="save"><span class="asterisk" style="display:none;color:red">*</span>
<?php include('metachg.inc.php') ?><script>initchanges()</script>
<?php require('t1.php') ?>
</form>

<script>
function validated(string) {
    for (var i=0, output='', valid="1234567890"; i<string.length; i++)
        if (valid.indexOf(string.charAt(i)) != -1)
            output += string.charAt(i);
    return output;
}
</script>

</body>
</html>