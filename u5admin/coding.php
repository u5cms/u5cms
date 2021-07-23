<?php 
require_once('connect.inc.php');

$sql_a="SELECT * FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);

?>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo htmlXspecialchars($row_a['name'])?></title>
<?php require('backendcss.php'); ?>
       <style type="text/css">
	   textarea, .textAreaWithLines textarea,.textAreaWithLines div {
       <?php echo $cssbackendtextarea ?>
       }
       #codeTextarea{
          width:3333px;
          height:55555px;
       }
       .textAreaWithLines{
          font-family:courier;      
          border:1px solid gray;
          
       }
       .textAreaWithLines textarea,.textAreaWithLines div{
          border:0px;
          font-size:12px;
       }
       .lineObj{
          color:white;
       }
       </style>

       <script type="text/javascript">
       
       var lineObjOffsetTop = 2;
       
       function createTextAreaWithLines(id)
       {
          var el = document.createElement('DIV');
          var ta = document.getElementById(id);
          ta.parentNode.insertBefore(el,ta);
          el.appendChild(ta);
          
          el.className='textAreaWithLines';
          el.style.width = (ta.offsetWidth + 30) + 'px';
          ta.style.position = 'absolute';
          ta.style.left = '30px';
          el.style.height = (ta.offsetHeight + 2) + 'px';
          el.style.overflow='hidden';
          el.style.position = 'relative';
          el.style.width = (ta.offsetWidth + 30) + 'px';
          var lineObj = document.createElement('DIV');
          lineObj.style.position = 'absolute';
          lineObj.style.top = lineObjOffsetTop + 'px';
          lineObj.style.left = '0px';
          lineObj.style.width = '27px';
          el.insertBefore(lineObj,ta);
          lineObj.style.textAlign = 'right';
          lineObj.className='lineObj';
          var string = '';
          for(var no=1;no<5000;no++){
             if(string.length>0)string = string + '<br>';
             string = string + no;
          }
          
          ta.onkeydown = function() { positionLineObj(lineObj,ta); if(event.keyCode == 9){doins(this.id,'\t');return false}};
          ta.onmousedown = function() { positionLineObj(lineObj,ta); };
          ta.onscroll = function() { positionLineObj(lineObj,ta); };
          ta.onblur = function() { positionLineObj(lineObj,ta); };
          ta.onfocus = function() { positionLineObj(lineObj,ta); };
          ta.onmouseover = function() { positionLineObj(lineObj,ta); };
          lineObj.innerHTML = string;
          
       }
       
       function positionLineObj(obj,ta)
       {
          obj.style.top = (ta.scrollTop * -1 + lineObjOffsetTop) + 'px';   
       
          
       }

function doins(here,that) {
insertAtCursor(document.getElementById(here), that);
}

function insertAtCursor(myField, myValue) {
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
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	setTimeout("document.form1.submit()",777);
	document.getElementById('savebutton').style.background='lightyellow';
	document.getElementById('bodyid').style.background='lightyellow';
})
tov='';
</script>
</head>
<body bgcolor="#666666" id="bodyid">

<form  name="form1" action="coding2.php?typ=c" method="post" target="saver"><input type="hidden" name="changes" id="changes" />
<div style="margin-top:-15px;margin-left:-12;padding-top:3px;background:#666666;position:fixed;z-index:9;width:102%">
<input type="hidden" name="coco" value="<?php echo time() ?>" />
<?php 
if($_COOKIE['shrchv'] == 1)$deletedvalue='deleted!=1';
else $deletedvalue='deleted=0';
$sql_b="SELECT name, deleted FROM resources WHERE ".$deletedvalue." AND typ='c' ORDER BY deleted, name";
$result_b=mysql_query($sql_b);

if ($result_b==false) {
	echo 'SQL_b-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_b.'</font><p>';
}

$num_b = mysql_num_rows($result_b);
echo '<div style="text-align:center;width:80%;margin-right:222px">';
for ($i_b=0; $i_b<$num_b; $i_b++) {
$row_b = mysql_fetch_array($result_b);
echo '<a id="ci'.$row_b['name'].'" style="font-size:70%;color:white;text-decoration:none" href="coding.php?name='.$row_b['name'].'">'.str_replace('(2)','(a)',str_replace('(0)','','('.$row_b['deleted'].') ')).$row_b['name'].'</a>&nbsp; ';	      
}
echo '</div>';
?>



&nbsp;<input id="savebutton" onClick="this.style.background='lightyellow';document.getElementById('bodyid').style.background='lightyellow'" type="submit" value="save [Ctrl + S]" style="width:90%"/>
       <span style="color:white;font-size:9px;cursor:pointer" onClick="alert('If you can save code and pages containing easy content, but saving code or pages with long and or complex content fails, then you have to tell this issue your provider for that he can lower the security (e.g. mod sec) rules!\n\nFurther, if your CSS files don\'t work, have a look to them by surfing to them in a browser (e.g. http://www.yoursite.ch/r/cssbase.css) and check if your server automatically adds slashes to quotes. If you find this issue, solve it by setting $quotehandling=\'on\' in config.php in the CMS directory on your server.')">can't&nbsp;save?</span>

</div>

       <input type="hidden" name="name" value="<?php echo $_GET['name']?>">
<br><br><br>  <textarea onKeyUp="tov=this.value;watchchanges()" onMouseOut="this.onchange()" onMouseOver="this.onchange()" onChange="tov=this.value;watchchanges()" name="content_d" wrap="off" id="codeTextarea"><?php echo htmlXspecialchars($row_a['content_d'])?></textarea>
</form>
<script type="text/javascript">
       createTextAreaWithLines('codeTextarea');
       </script>
<iframe name="saver" frameborder="0" width="1" height="1"></iframe>
<script>
window.onbeforeunload = function() {
if (document.getElementById('changes').value==1){self.focus();return('You have unsaved changes which will be lost if you are proceeding.');}
}
if (document.getElementById('ci<?php echo $_GET['name'] ?>')) document.getElementById('ci<?php echo $_GET['name'] ?>').style.background='yellow';
if (document.getElementById('ci<?php echo $_GET['name'] ?>')) document.getElementById('ci<?php echo $_GET['name'] ?>').style.color='black';

tov=document.getElementById('codeTextarea').value;
oldtov=document.getElementById('codeTextarea').value;
function watchchanges() {
if (tov!=oldtov) {document.getElementById('changes').value=1;;document.getElementById('savebutton').style.background='pink';document.getElementById('bodyid').style.background='#592100'};
oldtov=document.getElementById('codeTextarea').value;
}
	
</script>
</body>
</html>
