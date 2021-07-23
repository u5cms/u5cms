<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
changes=0;
if(parent.i1.changes)changes+=parent.i1.changes;
if(parent.i2.changes)changes+=parent.i2.changes;
if(changes>0) {
alert('You have to save the changes made in the editor(s) before saving these configurative parameters!');
history.go(-1);
window.stop();
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="#339900" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
if($previewinitscrollRqHIADRI!='no')require_once('accadminnoclose.inc.php'); 

if ($_GET['pvileft']<1) $_GET['pvileft']=1; 
if ($_GET['pvitop']<1) $_GET['pvitop']=1; 


$pvicode='pvileft='.$_GET['pvileft'].';pvitop='.$_GET['pvitop'].';

scrollstopped=0;
function stoppscroll() {
 if( typeof( window.pageYOffset ) == \'number\' ) {
     //Netscape compliant
     vscroll = window.pageYOffset;
     hscroll = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
     //DOM compliant
     vscroll = document.body.scrollTop;
     hscroll = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
     //IE6 standards compliant mode
     vscroll = document.documentElement.scrollTop;
     hscroll = document.documentElement.scrollLeft;
 }
if (vscroll==pvitop) scrollstopped=1;
else setTimeout("stoppscroll()",333); 
}
setTimeout("stoppscroll()",333); 

setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",1000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",2000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",3000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",4000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",5000);
';

file_put_contents('../r/pviscroll.js',$pvicode);

trxlog('pviscroll '.$_GET['pvileft'].' '.$_GET['pvitop']);
?> 
<div style="margin:-2px 0 0 2px;white-space: nowrap;" onmouseover="this.title=this.innerHTML" onclick="alert(this.innerHTML)">saved <?php echo $_GET['pvileft'].' '.$_GET['pvitop']?></div>
<script>
parent.i1.gotopage('');
setTimeout("location.href='blank.php'",1111);
</script>

</body>
</html>
