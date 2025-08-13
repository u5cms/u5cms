<?php require_once('../san.inc.php') ?>
<iframe id="idlocalize" style="display:none"></iframe><iframe id="idcoblel" style="display:none"></iframe>
<script>
function isNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
function tareatextfo() {
cvalue=getCookie(window.name+'edfo');
if(!isNumeric(cvalue))cvalue=12;
e=document.getElementsByTagName('textarea');	
for(i=0;i<e.length;i++){
e[i].style.fontSize=cvalue+'px';	
e[i].style.lineHeight=cvalue-0+8+'px';	
}
}
function edfominus() {
cvalue=getCookie(window.name+'edfo');
if(!isNumeric(cvalue))cvalue=12;
cvalue=cvalue-0-1;
if(cvalue<3)cvalue=3;
setCookie(window.name+'edfo', cvalue, 777);
tareatextfo();
}
function edfoplus() {
cvalue=getCookie(window.name+'edfo');
if(!isNumeric(cvalue))cvalue=12;
cvalue=cvalue-0+1;
if(cvalue>100)cvalue=100;
setCookie(window.name+'edfo', cvalue, 777)
tareatextfo();
}
</script>
<?php
$sqlget='SELECT * FROM resources WHERE name=\''.$_GET['c'].'\'';
$h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$sqlget);
if ($_GET['c']!='navigation') $Rrename='R';
else $Rrename='';
echo '&nbsp; <span id="rlcdspan" style="opacity:0.05" onmouseover="document.getElementById(\'oo_'.$_GET['c'].'\').style.color=\'blue\';document.getElementById(\'idlocalize\').src=\'filter2.inc.php?sql='.rawurlencode($sqlget).'&h='.$h.'\';this.style.opacity=\'1\'" onmouseout="this.style.opacity=\'0.05\'">
<a title="[Ctrl+F11] rename" href="javascript:void(0)" onclick="f1=window.open(\'rename.php?name='.$_GET['c'].'\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">'.$Rrename.'</a>&nbsp;<a title="[Ctrl+F10] localize (where linked in)" href="javascript:void(0)" onclick="f1=window.open(\'localize.php?name='.$_GET['c'].'\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');"><span id="oo_'.$_GET['c'].'">L</span></a>&nbsp;<a title="copy" href="javascript:void(0)" onclick="f1=window.open(\'copy.php?name='.$_GET['c'].'&wn=\'+window.name,\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">C</a>&nbsp;<a title="delete or define archive status" href="javascript:void(0)" onclick="f1=window.open(\'delete.php?name='.md5($_GET['c']).'\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">D</a>&nbsp;<a title="[Ctrl+F9] focus in repository" href="javascript:void(0)" onclick="parent.i3.location.href=\'focus.php?c='.$_GET['c'].'\'">F</a>&nbsp;&nbsp;&nbsp;<a href="javascript:edfominus()" title="decrease editor font-size">&minus;</a>&nbsp;<a href="javascript:edfoplus()" title="increase editor font-size">+</a>&nbsp;</span>';
?>