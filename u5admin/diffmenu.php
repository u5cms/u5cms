<?php header('Content-Type: text/html; charset=ISO-8859-1'); ?><?php require_once('connect.inc.php') ?><!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>...</title>
<?php require('backendcss.php'); ?></head>
<body>
<script>
parent.document.title='Diff of '+parent.opener.document.getElementsByTagName('select')[0].value;
document.write('<h1>Diff of <i>'+parent.opener.document.getElementsByTagName('select')[0].value+'</i></h1>');

function vpick() {

AL=document.getElementById('VL').value-0;
BL=document.getElementById('LL').value-1;
that=11 + 15 * AL - 1 + BL;
document.getElementById('TL').value=parent.opener.document.getElementsByTagName('textarea')[that].value;	

AR=document.getElementById('VR').value-0;
BR=document.getElementById('LR').value-1;
that=11 + 15 * AR - 1 + BR;
document.getElementById('TR').value=parent.opener.document.getElementsByTagName('textarea')[that].value;	

document.getElementById('diff').submit();
}

</script>

<form id="diff" method="post" action="diffresult.php" target="result">

<center style="margin-top:-55px">

<select id="VL" onchange="vpick()">
<script>
e=parent.opener.document.getElementsByTagName('h2');
for(i=0;i<e.length;i++) {
document.write('<option value="'+i+'">'+i+': '+e[i].innerHTML+'</option>');	
}
</script>
</select>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<select id="VR" onchange="vpick()">
<script>
e=parent.opener.document.getElementsByTagName('h2');
for(i=0;i<e.length;i++) {
document.write('<option value="'+i+'">'+i+': '+e[i].innerHTML+'</option>');	
}
</script>
</select>

<br>

<select id="LL" onchange="vpick();document.cookie='LL='+escape(this.value)+'; expires=Thu, 31 Dec 2037 12:00:00 GMT';">
<option value="1"><?php echo str_replace('0','',$lan1na); ?></option>
<option value="2"><?php echo str_replace('0','',$lan2na); ?></option>
<option value="3"><?php echo str_replace('0','',$lan3na); ?></option>
<option value="4"><?php echo str_replace('0','',$lan4na); ?></option>
<option value="5"><?php echo str_replace('0','',$lan5na); ?></option>
</select>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<select id="LR" onchange="vpick();document.cookie='LR='+escape(this.value)+'; expires=Thu, 31 Dec 2037 12:00:00 GMT';">
<option value="1"><?php echo str_replace('0','',$lan1na); ?></option>
<option value="2"><?php echo str_replace('0','',$lan2na); ?></option>
<option value="3"><?php echo str_replace('0','',$lan3na); ?></option>
<option value="4"><?php echo str_replace('0','',$lan4na); ?></option>
<option value="5"><?php echo str_replace('0','',$lan5na); ?></option>
</select>



</center>

<center style="display:none">

<textarea name="TL" id="TL" style="height:333px;width:555px"></textarea>
<textarea name="TR" id="TR" style="height:333px;width:555px"></textarea>

</center>

</form>
<script>
document.getElementById('VL').selectedIndex=1;
vpick();
if (document.cookie.indexOf('LL=')>-1){ 
document.getElementById('LL').value=('; '+document.cookie).split('; LL=')[1].split(';')[0];
vpick();
}
if (document.cookie.indexOf('LR=')>-1){ 
document.getElementById('LR').value=('; '+document.cookie).split('; LR=')[1].split(';')[0];
document.getElementById('LR').onchange;
vpick();
}
</script>
</body>
</html>