<?php require_once('connect.inc.php') ?>
<br><span style="white-space: nowrap;font-size:80%">
<form name="form1" style="display:inline" method="get"><?php $inputradiostyle='style="width:10px;margin-left:-1px"';?>
<span class="nw" title="Preview (off | on)">P<input <?php echo $inputradiostyle ?> onClick="cpc('pvs_p','on');document.form1.submit()" name="pvs_p" type="radio" value="off" <?php if ($_GET['pvs_p']!='on') echo'checked="checked"'?> /><input <?php echo $inputradiostyle ?> onClick="cpc('pvs_p','off');document.form1.submit()" name="pvs_p" type="radio" value="on" <?php if ($_GET['pvs_p']=='on') echo'checked="checked"'?> />&nbsp;</span> <span class="nw" title="Sort (name ASC | date DESC)" id="sortascdesc">S<input <?php echo $inputradiostyle ?> onClick="cpc('pvs_s','on');document.form1.submit()"  name="pvs_s" type="radio" value="name" <?php if ($_GET['pvs_s']!='date') echo'checked="checked"'?> /><input <?php echo $inputradiostyle ?> onClick="cpc('pvs_s','off');document.form1.submit()"  name="pvs_s" type="radio" value="date" <?php if ($_GET['pvs_s']=='date') echo'checked="checked"'?> />&nbsp;</span> <span class="nw" title="Filter (all | linked in the editors &amp; orphans)">F<input <?php echo $inputradiostyle ?> onClick="cpc('pvs_f','on');document.form1.submit()"  name="pvs_f" type="radio" value="all" <?php if ($_GET['pvs_f']!='here') echo'checked="checked"'?> /><input <?php echo $inputradiostyle ?> onClick="cpc('pvs_f','off');document.form1.submit()"  name="pvs_f" type="radio" value="here" <?php if ($_GET['pvs_f']=='here') echo'checked="checked"'?> />&nbsp;&nbsp;</span></form>
</span>
<button id="newbutton" <?php if ($delstatus==2) echo 'style="display:none"' ?> onClick="f1=window.open('new.php?typ=<?php echo $pidvesa ?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">new</button>
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
</script>

<?php
if ($_COOKIE['shrchv']=='1'  && $donotshowtogglearchive!=1) {
$delstatus=2;
echo '<span style="float:right;background:yellow"><big>&nbsp;Archive!&nbsp<a href="togglearchive.php?s=0" style="background:black;color:white" title="leave archive, go to current">x</a></big></span>
<big><span style="float:right"><a href="javascript:void(0)" onclick="f1=window.open(\'snippets.php\',\'_blank\',\'toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');" title="search for code snippets">&#128270;</a>&nbsp;</span></big>
<script>
setTimeout("if (document.getElementsByTagName(\'body\')[0] && location.href.indexOf(\'s.php\')<0 && location.href.indexOf(\'a.php\')<0 && location.href.indexOf(\'s2.php\')<0) document.getElementsByTagName(\'body\')[0].style.background=\'lightyellow\'",1);
</script>';
}
else { $delstatus=0;
?>

<?php if($donotshowtogglearchive!=1) {?>
<big><a href="togglearchive.php?s=1" style="float:right;background:black;color:white" title="go to archive">a</a></big>
<?php } ?>
<big><span style="float:right"><a href="javascript:void(0)" onclick="f1=window.open('snippets.php','_blank','toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=800,height=999');" title="search for code snippets">&#128270;</a>&nbsp;</span></big>

<?php } ?>




