<?php
require_once('connect.inc.php'); file_put_contents('../fileversions/EDITORrunning.txt',time());
if ($_COOKIE['i1_l']=='') setcookie('i1_l', 'P', time()+3600*24*365*10,'/');
if ($_COOKIE['i2_l']=='') setcookie('i2_l', '1', time()+3600*24*365*10,'/');
if($_SERVER['QUERY_STRING']=='i') setcookie('i1_l', '1', time()+3600*24*365*10,'/');
if($_SERVER['QUERY_STRING']=='i') setcookie('i2_1', '1', time()+3600*24*365*10,'/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>u5CMS - Welcome <?php echo ehtml($_SERVER['PHP_AUTH_USER'])?></title>
<script>
if(/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) location.href='indexios.php';
</script>
<script src="shortcut.js"></script>
<?php require('backendcss.php'); ?>
<style>
html,body{
  width:100%;
  height:100%;
  margin:0;
  padding:0;
  overflow:hidden;
}
body{background:#7695AD}
#editorlayout{
  position:fixed;
  top:0;
  left:0;
  width:100vw;
  height:100vh;
  height:100dvh;
  table-layout:fixed;
  border-collapse:separate;
  border-spacing:1px 0;
  box-sizing:border-box;
  margin:0;
  padding:0;
  border:0;
}
#editorlayout tbody,#editorlayout tr,#editorlayout td{
  height:100%;
}
#editorlayout td{
  padding:0;
  overflow:hidden;
  box-sizing:border-box;
}
#td3{width:260px}
#td1,#td2{position:relative}
#editorlayout iframe{
  display:block;
  width:100%;
  min-width:0;
  min-height:0;
  margin:0;
  padding:0;
  border:0;
  box-sizing:border-box;
}
#td1>iframe,#td2>iframe{
  position:absolute;
  top:0;
  left:0;
  height:100%;
}
#saveiframe{
  height:20px;
}
#i3iframe{
  height:calc(100% - 20px);
}
#td3>.hiddenframe{display:none}
@media(max-width:1599px){
  #td3{width:16%}
}
</style>
</head>
<body id="idbody" onload="loader()">
<table id="editorlayout" width="100%" border="0" cellspacing="1" cellpadding="0">
  <tr>
    <td valign="top" id="td1"><iframe name="i1" frameborder="0" src="editor.php" scrolling="no"></iframe></td>
    <td valign="top" id="td2"><iframe name="i2" frameborder="0" src="editor.php" scrolling="no"></iframe></td>
    <td valign="top" id="td3">
      <iframe id="saveiframe" name="save" frameborder="0" src="iblank.php" scrolling="no"></iframe>
      <iframe id="i3iframe" name="i3" frameborder="0" src="pidvesa.php"></iframe>
      <iframe class="hiddenframe" name="i4a" frameborder="0" src="blank.php"></iframe>
      <iframe class="hiddenframe" name="i4b" frameborder="0" src="blank.php"></iframe>
      <iframe class="hiddenframe" name="i4c" frameborder="0" src="blank.php"></iframe>
      <iframe class="hiddenframe" name="i4d" frameborder="0" src="blank.php"></iframe>
      <iframe class="hiddenframe" name="i4e" frameborder="0"></iframe>
    </td>
  </tr>
</table>
<div style="display:none" id="documenttitle"></div>
<div style="display:none" id="documentuser"><?php echo ehtml($_SERVER['PHP_AUTH_USER'])?></div>
<script>
(function() {
  'use strict';

  var layout=document.getElementById('editorlayout');
  var root=document.documentElement;
  var tableBody=layout.tBodies[0];
  var tableRow=tableBody && tableBody.rows[0];
  var td1=document.getElementById('td1');
  var td2=document.getElementById('td2');
  var td3=document.getElementById('td3');
  var mainFrame1=td1.querySelector('iframe[name="i1"]');
  var mainFrame2=td2.querySelector('iframe[name="i2"]');
  var saveFrame=document.getElementById('saveiframe');
  var detailsFrame=document.getElementById('i3iframe');
  var lastViewport='';
  var resizePending=false;

  function setImportant(element,property,value) {
    if(element) element.style.setProperty(property,value,'important');
  }

  function minPositive(values) {
    var result=[];
    for(var i=0;i<values.length;i++) {
      if(typeof values[i]==='number' && isFinite(values[i]) && values[i]>0) result.push(values[i]);
    }
    return result.length ? Math.min.apply(null,result) : 1;
  }

  function viewportBox() {
    var visual=window.visualViewport;
    var layoutWidth=minPositive([window.innerWidth,root.clientWidth]);
    var layoutHeight=minPositive([window.innerHeight,root.clientHeight]);
    var width=layoutWidth;
    var height=layoutHeight;
    var left=0;
    var top=0;

    if(visual) {
      width=minPositive([layoutWidth,visual.width]);
      height=minPositive([layoutHeight,visual.height]);
      left=Math.max(0,visual.offsetLeft || 0);
      top=Math.max(0,visual.offsetTop || 0);
      left=Math.min(left,Math.max(0,layoutWidth-width));
      top=Math.min(top,Math.max(0,layoutHeight-height));
    }

    return {
      left:Math.floor(left),
      top:Math.floor(top),
      width:Math.max(1,Math.floor(width)),
      height:Math.max(1,Math.floor(height))
    };
  }

  function applyViewport(force) {
    var box=viewportBox();
    var signature=box.left+':'+box.top+':'+box.width+':'+box.height;

    if(force || signature!==lastViewport) {
      lastViewport=signature;
      setImportant(layout,'position','fixed');
      setImportant(layout,'left',box.left+'px');
      setImportant(layout,'top',box.top+'px');
      setImportant(layout,'width',box.width+'px');
    }

    var fullHeight=box.height+'px';
    var detailsHeight=Math.max(1,box.height-20)+'px';

    [layout,tableBody,tableRow,td1,td2,td3].forEach(function(element) {
      setImportant(element,'height',fullHeight);
      setImportant(element,'max-height','none');
    });

    [td1,td2,td3].forEach(function(element) {
      setImportant(element,'overflow','hidden');
    });

    setImportant(td1,'position','relative');
    setImportant(td2,'position','relative');

    [mainFrame1,mainFrame2].forEach(function(frame) {
      setImportant(frame,'position','absolute');
      setImportant(frame,'top','0');
      setImportant(frame,'left','0');
      setImportant(frame,'width','100%');
      setImportant(frame,'height',fullHeight);
      setImportant(frame,'min-height','0');
      setImportant(frame,'max-height','none');
    });

    setImportant(saveFrame,'height','20px');
    setImportant(saveFrame,'min-height','0');
    setImportant(saveFrame,'max-height','none');
    setImportant(detailsFrame,'height',detailsHeight);
    setImportant(detailsFrame,'min-height','0');
    setImportant(detailsFrame,'max-height','none');
  }

  function scheduleViewport() {
    if(resizePending) return;
    resizePending=true;
    var update=function() {
      resizePending=false;
      applyViewport(false);
    };
    if(window.requestAnimationFrame) window.requestAnimationFrame(update);
    else setTimeout(update,0);
  }

  function editorState(frameName) {
    var state={name:'',changed:false};
    try {
      var frame=window.frames[frameName];
      var doc=frame && frame.document;
      if(doc && doc.form1 && doc.form1.page) state.name=doc.form1.page.value;
      var saveButton=doc && doc.getElementById('savebutton');
      if(saveButton && saveButton.innerHTML.indexOf('*')!==-1) state.changed=true;
    }
    catch(error) {}
    return state;
  }

  function updateDocumentTitle() {
    var left=editorState('i1');
    var right=editorState('i2');
    if(!left.name && !right.name) return;

    var user=document.getElementById('documentuser').textContent || '';
    var title='';
    if(left.name===right.name) {
      title=(left.changed || right.changed ? '*' : '')+right.name;
    }
    else {
      title=(left.changed ? '*' : '')+left.name+' | '+(right.changed ? '*' : '')+right.name;
    }
    if(user) title+=' | '+user;

    document.getElementById('documenttitle').textContent=title;
    document.title=title;
  }

  function saveEditor(frameName) {
    try {
      var frame=window.frames[frameName];
      if(!frame) return;
      if(frame.document && frame.document.form1) frame.document.form1.submit();
      if(typeof frame.ctrls==='function') frame.ctrls();
    }
    catch(error) {}
  }

  if(window.shortcut && typeof window.shortcut.add==='function') {
    window.shortcut.add('Ctrl+S',function() {
      saveEditor('i1');
      saveEditor('i2');
    });
  }

  // Keep the original global entry points for code loaded inside the frames.
  window.loader=function() {
    applyViewport(true);
    updateDocumentTitle();
  };
  window.sizer=function() {
    applyViewport(true);
  };
  window.resizer=function() {
    applyViewport(true);
    updateDocumentTitle();
  };

  applyViewport(true);
  updateDocumentTitle();
  setInterval(updateDocumentTitle,1111);
  setInterval(scheduleViewport,1000);

  window.addEventListener('load',function() {
    applyViewport(true);
    setTimeout(function() { applyViewport(true); },250);
    setTimeout(function() { applyViewport(true); },1000);
  });
  window.addEventListener('resize',scheduleViewport);
  window.addEventListener('orientationchange',scheduleViewport);
  window.addEventListener('scroll',scheduleViewport);
  window.addEventListener('pageshow',function() { applyViewport(true); });
  document.addEventListener('visibilitychange',function() {
    if(!document.hidden) applyViewport(true);
  });

  if(window.visualViewport) {
    window.visualViewport.addEventListener('resize',scheduleViewport);
    window.visualViewport.addEventListener('scroll',scheduleViewport);
  }

  if(window.ResizeObserver) {
    new ResizeObserver(scheduleViewport).observe(root);
  }
})();
</script>
<?php
include('zr.php');
if (file_put_contents('../r/x','x')) {
echo '<!--w ../r/ ok -->';
unlink('../r/x');
}
else echo '<script>alert("PROBLEM: The server does not have the right to write into the folder named \'r\'.\n\nEFFECTS: You cannot create or upload files in the backend nor change the htmltemplate or css\'s, and your customers cannot upload files in your forms where the script \'Pupload\' is used.\n\nSOLUTION: CHMOD the folder \'r\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>';

if (file_put_contents('../fileversions/x','x')) {
echo '<!--w ../r/ ok -->';
unlink('../fileversions/x');
}
else echo '<script>alert("PROBLEM: The server does not have the right to write into the folder named \'fileversions\'.\n\nEFFECTS: Your customers cannot upload files in your forms where the script \'upload\' is used, and there is no versioning of your files uploaded in the backend.\n\nSOLUTION: CHMOD the folder \'fileversions\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>';
?>
<script src="patches.js"></script>
</body>
</html>