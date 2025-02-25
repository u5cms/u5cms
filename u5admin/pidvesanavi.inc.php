<?php if(isset($u5phperrorreporting)&&$u5phperrorreporting=='on')error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED); ?>
<script>
function readScroll()
{
 var hscroll2 = readCookie('<?php echo $pidvesascroll ?>scrollposh');
 var vscroll2 = readCookie('<?php echo $pidvesascroll ?>scrollposv');
 window.scrollTo(hscroll2,vscroll2);
}

function createCookie(name,value,days) {
 if (days) {
  var date = new Date();
  date.setTime(date.getTime()+(days*24*60*60*1000));
  var expires = "; expires="+date.toGMTString();
 }
 else var expires = "";
 document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
 var nameEQ = name + "=";
 var ca = document.cookie.split(';');
 for(var i=0;i < ca.length;i++) {
  var c = ca[i];
  while (c.charAt(0)==' ') c = c.substring(1,c.length);
  if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
 }
 return null;
}

function eraseCookie(name) {
 createCookie(name,"",-1);
}

function f_scrollLeft() {
 return f_filterResults (
  window.pageXOffset ? window.pageXOffset : 0,
  document.documentElement ? document.documentElement.scrollLeft : 0,
  document.body ? document.body.scrollLeft : 0
 );
}
function f_scrollTop() {
 return f_filterResults (
  window.pageYOffset ? window.pageYOffset : 0,
  document.documentElement ? document.documentElement.scrollTop : 0,
  document.body ? document.body.scrollTop : 0
 );
}
function f_filterResults(n_win, n_docel, n_body) {
 var n_result = n_win ? n_win : 0;
 if (n_docel && (!n_result || (n_result > n_docel)))
  n_result = n_docel;
 return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

window.onscroll=function saveScroll()
{
    var hscroll = 0, vscroll = 0;
   
 if( typeof( window.pageYOffset ) == 'number' ) {
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
 createCookie('<?php echo $pidvesascroll ?>scrollposh',hscroll,1);
 createCookie('<?php echo $pidvesascroll ?>scrollposv',vscroll,1);
}
</script>
<style>
body {
font-size:11px;
background-color:white;
}
a {
text-decoration:none;
}
a:hover {
font-decoration:underline;
background:white;
}
.nw {
	white-space: pre;
	}
</style>
<span style="font-size:100%">

<table width="100%" style="margin-top:-3px;margin-bottom:-33px;margin-left:-5px">
  <tr><td>
<span class="nw" title="Pages"><input id="idp" onclick="cpc('pidvesa','p');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="p">P</span> 
<span class="nw" title="Images"><input id="idi" onclick="cpc('pidvesa','i');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="i">I</span> 
<span class="nw" title="Documents"><input id="idd" onclick="cpc('pidvesa','d');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="d">D</span> 
<span class="nw" title="Video &amp; Audio"><input id="idv" onclick="cpc('pidvesa','v');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="v">V</span> 
<span class="nw" title="External links"><input id="ide" onclick="cpc('pidvesa','e');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="e">E</span> 
<span class="nw" title="Save history &amp; Special functions"><input id="ids" onclick="cpc('pidvesa','s');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="s">S</span> 

<?php
if ($_COOKIE['shrchv']=='1'  && $donotshowtogglearchive!=1) {
$delstatus=2;
echo '<!--<span style="float:right;background:yellow"><big>&nbsp;Archive!&nbsp<a href="togglearchive.php?s=0" style="background:black;color:white" title="leave archive, go to current">x</a></big></span>
<big><span style="float:right"><a href="javascript:void(0)" onclick="f1=window.open(\'snippets.php\',\'_blank\',\'toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');" title="search for code snippets">&#128270;</a>&nbsp;</span></big>
<script>
setTimeout("if (document.getElementsByTagName(\'body\')[0] && location.href.indexOf(\'s.php\')<0 && location.href.indexOf(\'a.php\')<0 && location.href.indexOf(\'s2.php\')<0) document.getElementsByTagName(\'body\')[0].style.background=\'lightyellow\'",1);
</script>-->';
}
else { $delstatus=0;
?>
<span class="nw" title="Account"><input id="ida" onclick="cpc('pidvesa','a');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="a">A</span> 
<?php if($donotshowtogglearchive!=1) {?>
<!--<big><a href="togglearchive.php?s=1" style="float:right;background:black;color:white" title="go to archive">a</a></big>-->
<?php } ?>
<!--<big><span style="float:right"><a href="javascript:void(0)" onclick="f1=window.open('snippets.php','_blank','toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=800,height=999');" title="search for code snippets">&#128270;</a>&nbsp;</span></big>-->

<?php } ?>

<div style="float:right;margin-right:-5px">
<a id="isloaded" style="font-size:120%" href="u5shortreference.pdf" title="short reference" target="_blank">?</a>

                <?php
                if ($usesessioninsteadofbasicauth != 'no') echo ' <span title="logout"><a style="font-size:120%" lang="ja" href="javascript:void(0)" onclick="parent.location.href=\'logout.php?u=\'+parent.location.href">&#x51FA;</a></span>'
                ?>
</div>
</td>
  </tr></table><br /><br /></span>

<?php require('filterfield.inc.php')?>

<script>
if (document.getElementById('id<?php echo $pidvesa?>')) document.getElementById('id<?php echo $pidvesa?>').checked=true;

<?php 
if ($pidvesa=='f') echo "if (document.getElementById('idi')) document.getElementById('idi').checked=true;";
if ($pidvesa=='y') echo "if (document.getElementById('idv')) document.getElementById('idv').checked=true;";
if ($pidvesa=='c') echo "if (document.getElementById('idv')) document.getElementById('ids').checked=true;";
?>

function cpc(name,value) {
var date = new Date();
date.setTime(date.getTime()+(365*24*60*60*1000));
var expires = "; expires="+date.toGMTString();
document.cookie = name+"="+value+expires+"; path=/";
}
</script>