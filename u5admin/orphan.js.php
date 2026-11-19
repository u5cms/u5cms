<script>
(function() {
var timer=null, request=null, etag='';
function schedule() {
if(timer!==null) clearTimeout(timer);
timer=setTimeout(loadScript,1000);
}
function loadScript() {
timer=null;
if(request || document.hidden) { schedule(); return; }
var xhr=new XMLHttpRequest(), completed=false;
request=xhr;
function finish() {
if(completed) return;
completed=true;
try {
if(xhr.status===200) {
var script=document.createElement('script');
script.type='text/javascript';
script.textContent=xhr.responseText;
try {
document.body.appendChild(script);
etag=xhr.getResponseHeader('ETag') || '';
if(typeof parent.filter==='function') parent.filter(true);
} finally { if(script.parentNode) script.parentNode.removeChild(script); }
}
} catch(error) {
// Keep polling if a navigating parent temporarily cannot accept the update.
} finally {
request=null;
schedule();
}
}
xhr.open('GET','orphan.src.php',true);
xhr.timeout=15000;
if(etag) xhr.setRequestHeader('If-None-Match',etag);
xhr.onload=finish;
xhr.onerror=finish;
xhr.ontimeout=finish;
xhr.onabort=finish;
try { xhr.send(); } catch(error) { finish(); }
}
document.addEventListener('visibilitychange',function() {
if(!document.hidden && !request) {
if(timer!==null) clearTimeout(timer);
loadScript();
}
});
window.addEventListener('pagehide',function() {
if(timer!==null) clearTimeout(timer);
if(request) { request.onload=request.onerror=request.ontimeout=request.onabort=null; request.abort(); }
});
schedule();
})();
</script>