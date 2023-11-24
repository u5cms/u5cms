<h1 style="margin:0px">Save&nbsp;history <span style="font-size:10px"><?php echo $_SERVER['PHP_AUTH_USER']?>&#8217;s&nbsp;last&nbsp;7<span id="hplus" style="cursor:pointer;color:blue" onclick="if('<?php echo $_COOKIE['hplus']?>'!='on')document.cookie='hplus=on; expires=Thu, 31 Dec 2037 12:00:00 GMT';else document.cookie='hplus=off; expires=Thu, 31 Dec 2037 12:00:00 GMT'; location.href=location.href">+</span></span>
<?php echo '<div style="float:right;font-size:60%;margin-top:7px"><a href="javascript:void(0)" onclick="f1=window.open(\'snippets.php\',\'_blank\',\'toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');" title="search for code snippets">&#128270;</a>&nbsp;</div>'; ?>
</h1>
<?php require('savehistory.ifr.php')?>
<script>
function replace(string,text,by) {
// Replaces text with by in string
var strLength = string.length, txtLength = text.length;
if ((strLength == 0) || (txtLength == 0)) return string;
var i = string.indexOf(text);
if ((!i) && (text != string.substring(0,txtLength))) return string;
if (i == -1) return string;
var newstr = string.substring(0,i) + by;
if (i+txtLength < strLength)
newstr += replace(string.substring(i+txtLength,strLength),text,by);
return newstr;
}

if (document.getElementById('idgdf')) {
if(replace(document.getElementById('idgdf').value,'*','')!='') {
setTimeout("blinkfilter()",2222);
setTimeout("blinkfilter()",4444);
setTimeout("blinkfilter()",6666);	
setTimeout("document.getElementById('idgdf').style.background='yellow';",8888);	
}

function blinkfilter() {
document.getElementById('idgdf').style.background='yellow';
setTimeout("document.getElementById('idgdf').style.background='white';",1111);	
}

}
<?php if($_COOKIE['hplus']=='on')echo'document.getElementById("hplus").innerHTML="&ndash;";'?>
document.cookie='hplus=off; expires=Thu, 31 Dec 2037 12:00:00 GMT'
</script>

<div style="float:right;margin:2px 0 0 2px"><a href="s2.php">all users</a>&nbsp;</div>
<div style="margin:2px 0 0 2px">
<?php include('fileandtextversions.inc.php') ?>
</div>
