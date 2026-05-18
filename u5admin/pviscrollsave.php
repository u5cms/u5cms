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

$pvicode = 'pvileft='.(int)$_GET['pvileft'].';pvitop='.(int)$_GET['pvitop'].';
var pviDone = false;
var pviStartedAt = new Date().getTime();
var pviInterval = null;
var pviMutationObserver = null;
var pviLastDocH = -1;
var pviStableDocCount = 0;
var pviReachedCount = 0;

function pviStopWatching() {
    if (pviInterval !== null) {
        clearInterval(pviInterval);
        pviInterval = null;
    }
    if (pviMutationObserver) {
        try { pviMutationObserver.disconnect(); } catch (e) {}
        pviMutationObserver = null;
    }
    pviDone = true;
}

function pviGetScrollRoot() {
    if (document.scrollingElement) return document.scrollingElement;
    if (document.documentElement) return document.documentElement;
    if (document.body) return document.body;
    return null;
}

function pviGetScrollPos() {
    var root = pviGetScrollRoot();
    var x = 0;
    var y = 0;

    if (typeof window.pageXOffset != "undefined") x = window.pageXOffset;
    if (typeof window.pageYOffset != "undefined") y = window.pageYOffset;

    if (root) {
        if (root.scrollLeft > x) x = root.scrollLeft;
        if (root.scrollTop > y) y = root.scrollTop;
    }

    if (document.documentElement) {
        if (document.documentElement.scrollLeft > x) x = document.documentElement.scrollLeft;
        if (document.documentElement.scrollTop > y) y = document.documentElement.scrollTop;
    }

    if (document.body) {
        if (document.body.scrollLeft > x) x = document.body.scrollLeft;
        if (document.body.scrollTop > y) y = document.body.scrollTop;
    }

    return { x: x || 0, y: y || 0 };
}

function pviGetViewportHeight() {
    var h = 0;
    if (typeof window.innerHeight != "undefined" && window.innerHeight > 0) h = window.innerHeight;
    if (document.documentElement && document.documentElement.clientHeight > 0 && (h == 0 || document.documentElement.clientHeight < h)) h = document.documentElement.clientHeight;
    if (document.body && document.body.clientHeight > 0 && (h == 0 || document.body.clientHeight < h)) h = document.body.clientHeight;
    return h || 0;
}

function pviGetDocHeight() {
    var h = 0;
    var de = document.documentElement;
    var db = document.body;
    var root = pviGetScrollRoot();

    if (de) {
        if (de.scrollHeight > h) h = de.scrollHeight;
        if (de.offsetHeight > h) h = de.offsetHeight;
        if (de.clientHeight > h) h = de.clientHeight;
    }

    if (db) {
        if (db.scrollHeight > h) h = db.scrollHeight;
        if (db.offsetHeight > h) h = db.offsetHeight;
        if (db.clientHeight > h) h = db.clientHeight;
    }

    if (root) {
        if (root.scrollHeight > h) h = root.scrollHeight;
        if (root.offsetHeight > h) h = root.offsetHeight;
        if (root.clientHeight > h) h = root.clientHeight;
    }

    return h || 0;
}

function pviGetMaxScrollTop() {
    var maxY = pviGetDocHeight() - pviGetViewportHeight();
    if (maxY < 0) maxY = 0;
    return maxY;
}

function pviForceScrollTo(x, y) {
    var root = pviGetScrollRoot();

    try { window.scrollTo(x, y); } catch (e) {}
    try { window.scrollTo({ left:x, top:y, behavior:"auto" }); } catch (e) {}

    if (root) {
        try { root.scrollLeft = x; } catch (e) {}
        try { root.scrollTop = y; } catch (e) {}
    }

    if (document.documentElement) {
        try { document.documentElement.scrollLeft = x; } catch (e) {}
        try { document.documentElement.scrollTop = y; } catch (e) {}
    }

    if (document.body) {
        try { document.body.scrollLeft = x; } catch (e) {}
        try { document.body.scrollTop = y; } catch (e) {}
    }
}

function pviWatchScroll() {
    if (pviDone) return;

    if ((new Date().getTime() - pviStartedAt) > 5555) {
        pviStopWatching();
        return;
    }

    var docH = pviGetDocHeight();
    if (docH === pviLastDocH) pviStableDocCount++;
    else {
        pviStableDocCount = 0;
        pviLastDocH = docH;
    }

    var targetX = pvileft;
    var targetY = pvitop;
    var maxY = pviGetMaxScrollTop();

    if (targetY > maxY) targetY = maxY;

    pviForceScrollTo(targetX, targetY);

    var pos = pviGetScrollPos();
    var dx = Math.abs(pos.x - targetX);
    var dy = Math.abs(pos.y - targetY);

    if (dx <= 1 && dy <= 1) pviReachedCount++;
    else pviReachedCount = 0;

    if (pviReachedCount >= 3) {
        pviStopWatching();
        return;
    }

    if (pviStableDocCount >= 20 && targetY < pvitop && dy <= 1) {
        pviStopWatching();
        return;
    }
}

function pviKick() {
    if (pviDone) return;
    setTimeout(pviWatchScroll,0);
    setTimeout(pviWatchScroll,100);
    setTimeout(pviWatchScroll,500);
}

try { window.addEventListener("load", pviKick, false); } catch (e) {}
try { window.addEventListener("resize", pviKick, false); } catch (e) {}

if (typeof MutationObserver != "undefined") {
    try {
        pviMutationObserver = new MutationObserver(function() {
            pviKick();
        });
        if (document.documentElement) pviMutationObserver.observe(document.documentElement,{childList:true,subtree:true,attributes:true});
    } catch (e) {
        pviMutationObserver = null;
    }
}

pviWatchScroll();
pviKick();
pviInterval = setInterval(pviWatchScroll, 250);
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
