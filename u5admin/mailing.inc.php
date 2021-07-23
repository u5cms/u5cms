<?php require_once('connect.inc.php'); ?>
<div id="u5srmaya" style="background:lightyellow;margin:0px 8px 0px 15px">

<a href="javascript:void(0)" onclick="if(document.getElementById('mailing').style.display=='none'){document.getElementById('mailing').style.display='block';document.getElementById('fcdiv').style.display='block';document.getElementById('fc').focus();document.getElementById('fcdiv').style.display='none'}else document.getElementById('mailing').style.display='none'">Send serial mails to the above data selection</a>
<div id="mailing" style="display:none;padding:11px">
<div style="font-size:12px;color:#555">
<script>
instructions="This system generates a single, individual e-mail for each recipient with individualized text-pieces coming from the data table (to send hundreds of mails set $serialmailmethod=1; in config.php).\n\nReference the column names of the data table on the page at hand in square brackets. E. g. [fieldname].\n\nIf the column name contains a *, this character must be indicated too, e. g. [fieldname*]. So, if your recipients' e-mail addresses are in the column named youremail* you have to indicate in the To-field: [youremail*]\n\nIf you want to output conditional text, you can use the 2nd and 3rd parameter, delimited with pipes, e. g. [fieldname|valuetobetrue|outputtext]. An example: if the column sex has value 1 for male and the value 2 for female and you want to render the strings Sir or Madam, you can use the syntax [sex|1|Sir][sex|2|Madam]. This also works if the values themselves are strings: [sex|male|Sir][sex|female|Madam]\n\nIf you have line feeds in your data, these are rendered as pipes (|). You can change the pipe to something else with the 4th parameter, let's say you would like to display the line feeds as real line feeds: [fieldname|||<br>]\n\nIMPORTANT INFORMATION ABOUT From, To, Cc, Bcc\nSome providers only accept e-mail-addresses as 'From' if they belong to the domain in use. So you have to test, which From-address(es) work, otherwise nothing will be sent. You have to test it by generating mail going to To-addresses not belonging to the domain in use because such tests could be false positive. When using Cc and/or Bcc be aware that for EVERY To-address the respective copy will be (b)cc'd!\n\nSomething else to know: If you set a variable into the To-field and the variable contains anything but e-mail addresses, the To-field in the preview section remains empty. In other words, the fields To, Cc and Bcc extract e-mail adresses from strings (purifying), so, if no address is present, nothing remains. This purifying mechanism is helpful if a variable contains more than the e-mail address. Thanks to it such 'unstructured-text-containing-variables' can be used in the To-field.";

function teleinsert(thevar) {
if(me.document.getElementById('loaded'))me.teleinsert(thevar);
}
lansel='';
function putdoins(putstr) {
teleinsert(putstr);
}
</script>

Available variables (<a href="javascript:alert(instructions)">Instructions</a>): <b>
<?php 
$lasthead = str_replace('<small>Nฐ</small><br>','',$lasthead);
$lasthead = explode(';',$lasthead);
for ($l=0;$l<tnuoc($lasthead);$l++) {
if($l<4 || $l>tnuoc($lasthead)-3) $q='?';
else {
$q='';
$lasthead[$l]=substr($lasthead[$l],1);
}
$lasthead[$l]=trim($lasthead[$l]);
if(substr($lasthead[$l],0,10)!='userupload') echo '<a style="text-decoration:none;cursor:pointer" onclick="teleinsert(this.innerHTML)">['.$q.$lasthead[$l].']</a> ';
}
?>
&nbsp;&nbsp;</b>Characters:<b>&nbsp;&nbsp;<a title="escaped square brackets" style="text-decoration:none;cursor:pointer" onclick="teleinsert(this.innerHTML)">(จจ)</a>&nbsp;&nbsp;<a title="non-breaking space" style="text-decoration:none;cursor:pointer" onclick="teleinsert(this.innerHTML)">ค</a>&nbsp;&nbsp;</b><a href="javascript:void(0)" onclick="u1=window.open('characters.php?w='+(window.outerWidth-300)+'&amp;h='+window.outerHeight,'_blank','toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=640,height=640');" title="special characters">&#29305;&#27530;</a>
<?php include('checkidn.inc.php');?>
</div>
<br />
<iframe id="f1" name="mp" src="mailingpreview.php" style="width:34%;height:800px"></iframe>
<iframe id="f2" name="me" src="mailingeditorpre.php" style="width:36%;height:800px"></iframe>
<iframe id="f3" name="ml" src="mailinglist.php?n=<?php echo $_GET['n'] ?>&t=<?php echo $tsql ?>" style="width:25%;height:800px"></iframe>


<script>
res=150;
function sizer() {
document.getElementById('f1').style.height=document.documentElement.clientHeight-res+'px';
document.getElementById('f2').style.height=document.documentElement.clientHeight-res+'px';
document.getElementById('f3').style.height=document.documentElement.clientHeight-res+'px';
setTimeout("sizer()",333);
}
sizer();
</script>
<div id="fcdiv"><input type="radio" id="fc" /></div>
</div>
</div>