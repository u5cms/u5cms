<?php require('connect.inc.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="#FF0D13" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div style="margin:-2px 0 0 2px;white-space: nowrap;" onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)">unsaved content changes</div>
<script>
function savereminder() {
if(parent)if(parent.i1)if(parent.i1.document.getElementById('savebutton'))if(parent.i1.document.getElementById('savebutton').innerHTML.indexOf('*')>0)parent.i1.document.getElementById('savebutton').classList.add("sblink");
if(parent)if(parent.i2)if(parent.i2.document.getElementById('savebutton'))if(parent.i2.document.getElementById('savebutton').innerHTML.indexOf('*')>0)parent.i2.document.getElementById('savebutton').classList.add("sblink");
var audio = new Audio('savereminder.mp3');
audio.play();
setTimeout("savereminder()",1000*60*5);
}
setTimeout("savereminder()",1000*60*15);
</script>
</body>
</html>