</div>
<div id="pidvesadivendinc" style="height:91px;background:#eee"></div>
<script>
function pidvesadivinc() {
var header=document.getElementById('pidvesadivinc');
var spacer=document.getElementById('pidvesadivendinc');
if (!header || !spacer) return;
var height=Math.ceil(header.getBoundingClientRect().height)+'px';
if (spacer.style.height!==height) {
spacer.style.height=height;
if (window.u5FScroll && u5FScroll.refresh) u5FScroll.refresh();
}
}
pidvesadivinc();
(function() {
var queued=false;
function schedule() {
if (queued) return;
queued=true;
var update=function() { queued=false; pidvesadivinc(); };
if (window.requestAnimationFrame) window.requestAnimationFrame(update);
else setTimeout(update,0);
}
window.addEventListener('resize',schedule);
window.addEventListener('load',schedule);
if (window.ResizeObserver) {
var header=document.getElementById('pidvesadivinc');
if (header) new ResizeObserver(schedule).observe(header);
}
if (document.fonts && document.fonts.ready) document.fonts.ready.then(schedule);
})();
</script>