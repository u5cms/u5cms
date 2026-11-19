<?php error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED ^ E_USER_DEPRECATED); ?>
<script>
var scrollCookieWrites={};
function readScroll()
{
 var hscroll2 = readCookie('<?php echo $pidvesascroll ?>scrollposh');
 var vscroll2 = readCookie('<?php echo $pidvesascroll ?>scrollposv');
 if (window.u5FScroll && u5FScroll.active()) return;
 window.scrollTo(hscroll2,vscroll2);
}

function createCookie(name,value,days) {
 value=String(value);
 var now=Date.now();
 if (days>=0 && readCookie(name)===value && scrollCookieWrites[name] && now-scrollCookieWrites[name]<300000) return;
 if (days) {
  var date = new Date();
  date.setTime(date.getTime()+(days*24*60*60*1000));
  var expires = "; expires="+date.toGMTString();
 }
 else var expires = "";
 document.cookie = name+"="+value+expires+"; path=/";
 scrollCookieWrites[name]=now;
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

function saveScrollNow()
{
    if (window.u5FScroll && u5FScroll.active() && u5FScroll.hold()) return;
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
 if (window.u5FScroll && u5FScroll.active()) u5FScroll.saveAnchor();
}

var scrollSaveTimer=null, lastScrollSave=0;
window.onscroll=function saveScroll() {
var delay=100-(Date.now()-lastScrollSave);
if (delay<=0) {
if (scrollSaveTimer!==null) clearTimeout(scrollSaveTimer);
scrollSaveTimer=null; lastScrollSave=Date.now(); saveScrollNow();
} else if (scrollSaveTimer===null) scrollSaveTimer=setTimeout(function() {
scrollSaveTimer=null; lastScrollSave=Date.now(); saveScrollNow();
},delay);
};
window.addEventListener('pagehide',function() {
if (scrollSaveTimer!==null) clearTimeout(scrollSaveTimer);
scrollSaveTimer=null; saveScrollNow();
});

// The F-filter and a one-shot preview switch share the restoration controller.
window.u5FScroll=(function() {
var key='<?php echo $pidvesascroll ?>';
var previewMode=<?php echo $_GET['pvs_p']=='on' ? "'on'" : "'off'" ?>;
var storageKey='u5preview:'+location.pathname+':'+key;
var targetX=Number(readCookie(key+'scrollposh')) || 0;
var targetY=Number(readCookie(key+'scrollposv')) || 0;
var targetAnchor=null;
try { targetAnchor=JSON.parse(decodeURIComponent(readCookie(key+'fscrollanchor') || 'null')); } catch(e) {}
if (targetAnchor && (targetAnchor.y!==targetY || targetAnchor.x!==targetX)) targetAnchor=null;
if (!targetAnchor || typeof targetAnchor.id!='string' || typeof targetAnchor.top!='number' || !isFinite(targetAnchor.top)) targetAnchor=null;
var originalX=targetX, originalY=targetY, originalAnchor=targetAnchor;
var preview=takePreview(), previewChecked=false, objectTracking=!!preview;
var pageLoaded=false, previewTimer=null;
if (preview) {
targetX=preview.x; targetY=preview.y; targetAnchor=preview.anchor;
}
var pending=true, ready=false, adjusting=false;
function filtered() {
var form=document.form1;
return !!(form && form.pvs_f && form.pvs_f[1] && form.pvs_f[1].checked);
}
function context() {
var form=document.form1, search=document.getElementById('idgdf');
return JSON.stringify([filtered(),!!(form && form.pvs_s && form.pvs_s[1] && form.pvs_s[1].checked),search ? search.value : '',readCookie('shrchv') || '0']);
}
function number(value) { return typeof value=='number' && isFinite(value); }
function clearPreviewStorage() {
try { window.sessionStorage.removeItem(storageKey); } catch(e) {}
if (readCookie(key+'previewscroll')) eraseCookie(key+'previewscroll');
}
function takePreview() {
var raw=null, state=null;
try { raw=window.sessionStorage.getItem(storageKey); } catch(e) {}
if (!raw) raw=readCookie(key+'previewscroll');
clearPreviewStorage();
try { state=JSON.parse(raw ? decodeURIComponent(raw) : 'null'); } catch(e) {}
if (!state || state.path!==location.pathname || state.mode!==previewMode || typeof state.context!='string') return null;
if (!number(state.time) || Date.now()-state.time<0 || Date.now()-state.time>60000) return null;
if (!number(state.x) || !number(state.y) || state.x<0 || state.y<0) return null;
var a=state.anchor;
if (!a || a.centered!==true || typeof a.id!='string' || a.id.indexOf('tr1_')!==0 || !number(a.top) || !number(a.offset)) return null;
return state;
}
function active() {
if (preview && !previewChecked && document.form1) {
previewChecked=true;
if (preview.context!==context()) {
endPreview(); objectTracking=false;
targetX=originalX; targetY=originalY; targetAnchor=originalAnchor;
}
}
return filtered() || !!preview;
}
function viewTop() {
var header=document.getElementById('pidvesadivinc');
if (!header || window.getComputedStyle(header).position!='fixed') return 0;
return Math.max(0,Math.min(window.innerHeight,header.getBoundingClientRect().bottom));
}
function viewMid() {
var top=viewTop(), bottom=window.innerHeight;
var footer=document.getElementById('u5listfooter');
var footers=footer ? [footer] : document.querySelectorAll('div[style*="bottom"]');
for (var f=0;f<footers.length;f++) {
var footer=footers[f];
if (!footer.getClientRects().length || window.getComputedStyle(footer).position!='fixed') continue;
var rect=footer.getBoundingClientRect();
if (rect.bottom>=window.innerHeight-1 && rect.top>=top) bottom=Math.min(bottom,rect.top);
}
return (top+Math.max(top,bottom))/2;
}
function objectAnchor(collapse) {
var rows=document.querySelectorAll('tr[id^="tr1_"]'), edge=viewTop(), middle=viewMid();
var best=null, nearest=Infinity;
for (var r=0;r<rows.length;r++) {
var row=rows[r];
if (!row.getClientRects().length) continue;
var rect=row.getBoundingClientRect(), bottom=rect.bottom;
var detail=document.getElementById('tr2_'+row.id.substring(4));
if (detail && detail.getClientRects().length) bottom=Math.max(bottom,detail.getBoundingClientRect().bottom);
if (bottom<=edge || rect.top>=window.innerHeight) continue;
var distance=Math.max(rect.top-middle,middle-bottom,0);
if (distance>=nearest) continue;
nearest=distance;
// If the midpoint lies in a disappearing preview, center its owning main row.
var offset=collapse && rect.bottom<=middle ? 0 : rect.top+rect.height/2-middle;
best={id:row.id,top:rect.top,offset:offset,centered:true};
if (distance==0) break;
}
return best;
}
function preparePreview(mode) {
clearPreviewStorage();
if (mode===previewMode) return;
var a=preview && pending ? targetAnchor : objectAnchor(mode=='off');
if (!a) return;
var state={path:location.pathname,mode:mode,context:context(),time:Date.now(),anchor:a,x:preview && pending ? targetX : window.pageXOffset,y:preview && pending ? targetY : window.pageYOffset};
var raw=encodeURIComponent(JSON.stringify(state));
try { window.sessionStorage.setItem(storageKey,raw); }
catch(e) { try { if (raw.length<3800) createCookie(key+'previewscroll',raw,1/1440); } catch(ignore) {} }
}
function anchor() {
if (objectTracking) return objectAnchor(false);
var rows=document.querySelectorAll('tr[id^="tr1_"],tr[id^="tr2_"]');
for (var r=0;r<rows.length;r++) {
var row=rows[r];
if (!row.getClientRects().length) continue;
var rect=row.getBoundingClientRect();
if (rect.bottom>0 && rect.top<window.innerHeight) return {id:row.id,top:rect.top};
}
return null;
}
function saveAnchor() {
if (!ready || pending || adjusting) return;
var a=anchor();
if (a) { a.x=window.pageXOffset; a.y=window.pageYOffset; }
createCookie(key+'fscrollanchor',encodeURIComponent(JSON.stringify(a)),1);
}
function move(x,y) {
adjusting=true;
try { window.scrollTo(x,y); } finally { adjusting=false; }
}
function endPreview() {
preview=null;
if (previewTimer!==null) { clearTimeout(previewTimer); previewTimer=null; }
}
function restore(fallback) {
if (!pending || !ready) return;
var row=targetAnchor && document.getElementById(targetAnchor.id);
if (row && !row.getClientRects().length) {
if (!fallback) return;
row=null;
}
var top=preview && row ? viewMid()+targetAnchor.offset-row.getBoundingClientRect().height/2 : targetAnchor && targetAnchor.top;
var x=targetX, y=row ? window.pageYOffset+row.getBoundingClientRect().top-top : targetY;
if (pageLoaded && (preview && !filtered() || fallback)) {
var root=document.scrollingElement || document.documentElement;
x=Math.max(0,Math.min(x,root.scrollWidth-root.clientWidth));
y=Math.max(0,Math.min(y,root.scrollHeight-root.clientHeight));
}
move(x,y);
// A short interim list must never overwrite the original target.
if ((!preview || pageLoaded) && Math.abs(window.pageYOffset-Math.max(0,y))<1 && Math.abs(window.pageXOffset-Math.max(0,x))<1) {
pending=false;
endPreview();
window.onscroll();
}
}
function loaded() {
if (!active()) return;
pageLoaded=true;
if (!filtered()) ready=true;
restore();
}
function parsed() {
if (!active()) return;
if (!filtered()) ready=true;
restore();
// Start the deadline after parsing; a stalled image must not block it.
if (pending && previewTimer===null) previewTimer=setTimeout(function() {
if (!pending || !active()) return;
pageLoaded=true; ready=true; restore(true);
},15000);
}
window.addEventListener('DOMContentLoaded',parsed);
if (document.readyState!='loading') setTimeout(parsed,0);
window.addEventListener('load',function() { setTimeout(loaded,0); });
function before() {
if (!active()) return null;
return {anchor:ready && !pending ? anchor() : null,x:window.pageXOffset,y:window.pageYOffset};
}
function after(state) {
if (!state || !active()) return;
ready=true;
if (pending) { restore(); return; }
var a=state.anchor;
var row=a && document.getElementById(a.id);
if (row && row.getClientRects().length) {
var rect=row.getBoundingClientRect();
var top=a.centered===true ? viewMid()+a.offset-rect.height/2 : a.top;
move(state.x,window.pageYOffset+rect.top-top);
} else move(state.x,state.y);
window.onscroll();
}
function userScrollIntent(event) {
if (!active()) return;
if (preview && event.type=='pointerdown' && event.target && event.target.name=='pvs_p') return;
if (event.type=='keydown') {
var tag=event.target && event.target.tagName;
if (tag=='TEXTAREA' || tag=='SELECT' || (event.target && event.target.isContentEditable)) return;
if (tag=='INPUT' && event.keyCode!=33 && event.keyCode!=34) return;
if ([32,33,34,35,36,37,38,39,40].indexOf(event.keyCode)==-1) return;
}
pending=false;
endPreview();
}
window.addEventListener('wheel',userScrollIntent,{passive:true});
window.addEventListener('touchstart',userScrollIntent,{passive:true});
window.addEventListener('pointerdown',userScrollIntent);
window.addEventListener('keydown',userScrollIntent);
return {active:active,hold:function(){return pending || adjusting || !ready;},saveAnchor:saveAnchor,before:before,after:after,preview:preparePreview,refresh:function(){if(active() && pending && ready)restore();}};
})();
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
h1 {
	font-size:15px;
	}
input {
	margin-right:1px;
	}

</style>
<span style="font-size:100%">

<table width="100%" style="margin-top:-3px;margin-bottom:-33px;margin-left:-5px">
  <tr><td style="letter-spacing:-1px">
<span class="nw" title="Pages"><input id="idp" onclick="cpc('pidvesa','p');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="p">P</span> 
<span class="nw" title="Images"><input id="idi" onclick="cpc('pidvesa','i');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="i">I</span> 
<span class="nw" title="Documents"><input id="idd" onclick="cpc('pidvesa','d');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="d">D</span> 
<span class="nw" title="Video &amp; Audio"><input id="idv" onclick="cpc('pidvesa','v');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="v">V</span> 
<span class="nw" title="External links"><input id="ide" onclick="cpc('pidvesa','e');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="e">E</span> 
<span class="nw" title="Save history &amp; Special funcs"><input id="ids" onclick="cpc('pidvesa','s');location.href=this.value+'.php'" style="width:10px" name="navi" type="radio" value="s">S</span> 

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