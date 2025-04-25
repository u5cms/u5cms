<?php require_once('connect.inc.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body style="font-size:12px">
<input type="hidden" id="afield" />
<script>
document.write('<!--'+unescape(location.href)+'-->');

function teleinsert(thevar) {
if(document.getElementById('afield').value=='')alert('Please place your cursor in the mail form at the position where you want to insert this variable before clicking the variable.');
else insertAtCursor(eval('document.form.'+document.getElementById('afield').value),thevar);
}

function insertAtCursor(myField, myValue) {
if(myField=='') myField=document.form.mailtext;
keepscrolltop=myField.scrollTop;
  //IE support
  myValue=unescape(myValue);
  if (document.selection) {
    myField.focus();
    sel = document.selection.createRange();
    sel.text = myValue;
  }
  //MOZILLA/NETSCAPE support
  else if (myField.selectionStart || myField.selectionStart == '0') {
    var startPos = myField.selectionStart;
    var endPos = myField.selectionEnd;
    myField.value = myField.value.substring(0, startPos)
                  + myValue
                  + myField.value.substring(endPos, myField.value.length);
  } else {
    myField.value += myValue;
  }
    myField.focus();
	if (myField.setSelectionRange) myField.setSelectionRange(startPos + myValue.length, startPos + myValue.length);
myField.scrollTop=keepscrolltop;
}

</script>
<?php

$sql_a="SELECT * FROM mailing WHERE id='".mysql_real_escape_string($_GET['id'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';

$row_a = mysql_fetch_array($result_a);
?>
<script src="shortcut.js"></script>
<?php if($row_a['mailsent']==0) {?>
<script>
shortcut.add("Ctrl+S",function() {
	document.form.submit();
})
</script>
<?php } else {?>
<script>
shortcut.add("Ctrl+S",function() {
	location.reload();
})
</script>
<?php } ?>
<?php $h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$_GET['t'])?>
<form style="display:none" target="mapr_calc" name="pform" method="post" target="ml" action="mapr_calc.php?t=<?php echo $_GET['t']?>&n=<?php echo $_GET['n'] ?>&h=<?php echo $h ?>">
<input type="text" name="mfrom" style="width:100%" value="<?php echo ehtml($row_a['mailfrom']) ?>" />
<input type="text" name="mto" style="width:100%" value="<?php echo ehtml($row_a['mailto']) ?>" />
<input type="text" name="mcc" style="width:100%" value="<?php echo ehtml($row_a['mailcc']) ?>" />
<input type="text" name="mbcc" style="width:100%" value="<?php echo ehtml($row_a['mailbcc']) ?>" />
<input type="text" name="msubject" style="width:100%" maxlength="255" value="<?php echo ehtml($row_a['mailsubject']) ?>" />
<textarea name="mmessage" style="width:100%;"><?php echo ehtml($row_a['mailtext']) ?></textarea>
<?php require('t1.php') ?></form>

<form style="margin-top:47px" name="form" method="post" target="ml" action="mailsave.php?n=<?php echo $_GET['n'] ?>&id=<?php echo $_GET['id'] ?>&t=<?php echo $_GET['t'] ?>">
<table style="width:100%">
<tr><td width="1">From: <small><a href="javascript:void(0)" onclick="alert('Enter ONE valid e-mail address to which replies shall be sent. Example:\n\nj.smith@example.com\n\nUSUALLY IT MUST BE AN E-MAIL ADDRESS WITH THE SAME DOMAIN AS THE SITE AT HAND AND IT MUST BE EXISTING!\n\nIt is recommended to include your human readable name. You may do this like this:\n\nJohn Smith <j.smith@example.com>')" style="color:red">!?</a></small></td><td><input <?php include('mailingeditor.onchange.php') ?> placeholder="The domain of this sender e-mail address MUST belong to this website AND be an EXISTING mailbox there!" onfocus="document.getElementById('afield').value=this.name" type="text" name="mfrom" style="width:100%" value="<?php echo ehtml($row_a['mailfrom']) ?>" /></td></tr>
<tr><td>To:</td><td><input <?php include('mailingeditor.onchange.php') ?> onfocus="document.getElementById('afield').value=this.name"type="text" name="mto" style="width:100%" value="<?php echo ehtml($row_a['mailto']) ?>" /></td></tr>
<tr><td>Cc:</td><td><input <?php include('mailingeditor.onchange.php') ?> onfocus="document.getElementById('afield').value=this.name"type="text" name="mcc" style="width:100%" value="<?php echo ehtml($row_a['mailcc']) ?>" /></td></tr>
<tr><td>Bcc:</td><td><input <?php include('mailingeditor.onchange.php') ?> onfocus="document.getElementById('afield').value=this.name"type="text" name="mbcc" style="width:100%" value="<?php echo ehtml($row_a['mailbcc']) ?>" /></td></tr>
<tr><td> </td><td><hr /> </td></tr>
<tr><td>Subject:</td><td><input maxlength="255" onfocus="document.getElementById('afield').value=this.name"type="text" name="msubject" style="width:100%" value="<?php echo ehtml($row_a['mailsubject']) ?>" /></td></tr>
</table>
<input type="hidden" name="changes"  />
<p>Message: <small><a href="javascript:void(0)" onclick="alert('The message is an HTML-message.\n\nThat means:\n\n1) You may use HTML e. g. &lt;b&gt;bold text&lt;/b&gt;\n\n2) You have to write the &lt;-character and the &gt;-character as HTML-entities &amp;lt; and &amp;gt; if to be displayed in your e-mail!')">?</a></small>

<?php if($row_a['mailsent']==0) {?>
<input id="save" style="float:right" type="submit" value="save mailjob &#19904;<?php echo $_GET['id'] ?> (does not send any mail)" title="[Ctrl+s] save mailjob &#19904;<?php echo $_GET['id'] ?>. To execute the mailjob, use the send button on the list in the very right column." />
<?php }
else echo 'sent ' . date('Y-m-d H:i:s',$row_a['mailsent']) . ' by ' . $row_a['mailsentop'];
?>


<textarea onfocus="document.getElementById('afield').value=this.name" id="t1" name="mmessage" style="font-size:14px;width:100%;height:550px"><?php echo ehtml($row_a['mailtext']) ?></textarea>
<input type="hidden" name="coco" value="<?php echo time() ?>" />

<input type="hidden" name="oldfrom" value="<?php echo ehtml($row_a['mailfrom']) ?>" />
<input type="hidden" name="oldto" value="<?php echo ehtml($row_a['mailto']) ?>" />
<input type="hidden" name="oldcc" value="<?php echo ehtml($row_a['mailcc']) ?>" />
<input type="hidden" name="oldbcc" value="<?php echo ehtml($row_a['mailbcc']) ?>" />
<input type="hidden" name="oldsubject" value="<?php echo ehtml($row_a['mailsubject']) ?>" />
<textarea style="display:none" name="oldmessage"><?php echo ehtml($row_a['mailtext']) ?></textarea>

<?php require('t1.php') ?></form>
<textarea style="display:none" id="xgram">&#19904;</textarea>
<script>
window.onbeforeunload = function() {
if (document.form.changes.value>0) {self.focus();return('You have unsaved changes which will be lost if you are proceeding.');}
}


function dopreview() {
tp=0;
}

tp=0;
function dopreview2() {
tp++;
//document.form.msubject.value=tp;
if(tp==7) evokepreview();
setTimeout("dopreview2()",100);
}

onc=0;
function evokepreview() {
<?php if($row_a['mailsent']>0) echo 'if(onc==3)alert("You cannot save nor change mailjob "+document.getElementById("xgram").value+"'.$_GET['id'].' because it has been sent at '.date('Y-m-d H:i:s',$row_a['mailsent']).' by '.$row_a['mailsentop'].'");'?>
onc++;
document.pform.mfrom.value=document.form.mfrom.value;
document.pform.mto.value=document.form.mto.value;
document.pform.mcc.value=document.form.mcc.value;
document.pform.mbcc.value=document.form.mbcc.value;
document.pform.msubject.value=document.form.msubject.value;
document.pform.mmessage.value=document.form.mmessage.value;
document.pform.submit();
}


function preview() {
if(document.getElementById('save'))document.form.changes.value=1;
if(document.getElementById('save'))document.getElementById('save').style.background='pink';
if(parent) if(parent.ml.document.getElementById('send<?php echo $row_a['id']?>'))parent.ml.document.getElementById('send<?php echo $row_a['id']?>').style.display='none';
if(parent) if(parent.ml.document.getElementById('nosend<?php echo $row_a['id']?>'))parent.ml.document.getElementById('nosend<?php echo $row_a['id']?>').style.display='inline';

if(parent) if(parent.ml.document.getElementById('test<?php echo $row_a['id']?>'))parent.ml.document.getElementById('test<?php echo $row_a['id']?>').style.display='none';
if(parent) if(parent.ml.document.getElementById('notest<?php echo $row_a['id']?>'))parent.ml.document.getElementById('notest<?php echo $row_a['id']?>').style.display='inline';

dopreview();
}


res=260;
function sizer() {
document.getElementById('t1').style.height=document.documentElement.clientHeight-res+'px';
setTimeout("sizer()",333);

if(document.form.mfrom.value!=document.form.oldfrom.value) preview();
document.form.oldfrom.value=document.form.mfrom.value;

if(document.form.mto.value!=document.form.oldto.value) preview();
document.form.oldto.value=document.form.mto.value;

if(document.form.mcc.value!=document.form.oldcc.value) preview();
document.form.oldcc.value=document.form.mcc.value;

if(document.form.mbcc.value!=document.form.oldbcc.value) preview();
document.form.oldbcc.value=document.form.mbcc.value;

if(document.form.msubject.value!=document.form.oldsubject.value) preview();
document.form.oldsubject.value=document.form.msubject.value;

if(document.form.mmessage.value!=document.form.oldmessage.value) preview();
document.form.oldmessage.value=document.form.mmessage.value;

if(document.form.changes.value==1) {
if(parent) if(parent.ml.document.getElementById('send<?php echo $_GET['id']?>'))parent.ml.document.getElementById('send<?php echo $_GET['id']?>').style.display='none';
if(parent) if(parent.ml.document.getElementById('nosend<?php echo $_GET['id']?>'))parent.ml.document.getElementById('nosend<?php echo $_GET['id']?>').style.display='inline';

if(parent) if(parent.ml.document.getElementById('test<?php echo $_GET['id']?>'))parent.ml.document.getElementById('test<?php echo $_GET['id']?>').style.display='none';
if(parent) if(parent.ml.document.getElementById('notest<?php echo $_GET['id']?>'))parent.ml.document.getElementById('notest<?php echo $_GET['id']?>').style.display='inline';
}
else {
if(parent) if(parent.ml.document.getElementById('send<?php echo $_GET['id']?>'))parent.ml.document.getElementById('send<?php echo $_GET['id']?>').style.display='inline';
if(parent) if(parent.ml.document.getElementById('nosend<?php echo $_GET['id']?>'))parent.ml.document.getElementById('nosend<?php echo $_GET['id']?>').style.display='none';

if(parent) if(parent.ml.document.getElementById('test<?php echo $_GET['id']?>'))parent.ml.document.getElementById('test<?php echo $_GET['id']?>').style.display='inline';
if(parent) if(parent.ml.document.getElementById('notest<?php echo $_GET['id']?>'))parent.ml.document.getElementById('notest<?php echo $_GET['id']?>').style.display='none';
}
}
sizer();
dopreview();
dopreview2();
if(parent) if(parent.ml.document.getElementById('send<?php echo $_GET['id']?>'))parent.ml.document.getElementById('send<?php echo $_GET['id']?>').style.display='inline';
if(parent) if(parent.ml.document.getElementById('nosend<?php echo $_GET['id']?>'))parent.ml.document.getElementById('nosend<?php echo $_GET['id']?>').style.display='none';

if(parent) if(parent.ml.document.getElementById('test<?php echo $_GET['id']?>'))parent.ml.document.getElementById('test<?php echo $_GET['id']?>').style.display='inline';
if(parent) if(parent.ml.document.getElementById('notest<?php echo $_GET['id']?>'))parent.ml.document.getElementById('notest<?php echo $_GET['id']?>').style.display='none';

if(parent && parent.ml && parent.ml.location.href.indexOf('mailinglist.php')>0) parent.ml.location.reload();
</script>
<span id="loaded"></span>
</body>
</html>
