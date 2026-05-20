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
var pviLastActivityAt = pviStartedAt;
var pviInterval = null;
var pviMutationObserver = null;

var pviLastDocW = -1;
var pviLastDocH = -1;
var pviStableDocCount = 0;
var pviReachedCount = 0;
var pviProgrammaticScrollUntil = 0;
var pviEverTried = false;

var pviMaxRuntime = 15000;
var pviProgrammaticGrace = 500;
var pviQuietTimeReachable = 700;
var pviQuietTimeClamped = 1800;
var pviRequiredStableTicksReachable = 4;
var pviRequiredStableTicksClamped = 8;

function pviNow() {
    return new Date().getTime();
}

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

function pviMarkActivity() {
    if (pviDone) return;
    pviLastActivityAt = pviNow();
    pviStableDocCount = 0;
    pviReachedCount = 0;
}

function pviUserInteraction(isScrollEvent) {
    if (pviDone) return;

    var now = pviNow();

    if (isScrollEvent && now <= pviProgrammaticScrollUntil) return;

    pviStopWatching();
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

function pviGetViewportWidth() {
    var w = 0;
    if (typeof window.innerWidth != "undefined" && window.innerWidth > 0) w = window.innerWidth;
    if (document.documentElement && document.documentElement.clientWidth > 0 && (w == 0 || document.documentElement.clientWidth < w)) w = document.documentElement.clientWidth;
    if (document.body && document.body.clientWidth > 0 && (w == 0 || document.body.clientWidth < w)) w = document.body.clientWidth;
    return w || 0;
}

function pviGetViewportHeight() {
    var h = 0;
    if (typeof window.innerHeight != "undefined" && window.innerHeight > 0) h = window.innerHeight;
    if (document.documentElement && document.documentElement.clientHeight > 0 && (h == 0 || document.documentElement.clientHeight < h)) h = document.documentElement.clientHeight;
    if (document.body && document.body.clientHeight > 0 && (h == 0 || document.body.clientHeight < h)) h = document.body.clientHeight;
    return h || 0;
}

function pviGetDocWidth() {
    var w = 0;
    var de = document.documentElement;
    var db = document.body;
    var root = pviGetScrollRoot();

    if (de) {
        if (de.scrollWidth > w) w = de.scrollWidth;
        if (de.offsetWidth > w) w = de.offsetWidth;
        if (de.clientWidth > w) w = de.clientWidth;
    }

    if (db) {
        if (db.scrollWidth > w) w = db.scrollWidth;
        if (db.offsetWidth > w) w = db.offsetWidth;
        if (db.clientWidth > w) w = db.clientWidth;
    }

    if (root) {
        if (root.scrollWidth > w) w = root.scrollWidth;
        if (root.offsetWidth > w) w = root.offsetWidth;
        if (root.clientWidth > w) w = root.clientWidth;
    }

    return w || 0;
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

function pviGetMaxScrollLeft() {
    var maxX = pviGetDocWidth() - pviGetViewportWidth();
    if (maxX < 0) maxX = 0;
    return maxX;
}

function pviGetMaxScrollTop() {
    var maxY = pviGetDocHeight() - pviGetViewportHeight();
    if (maxY < 0) maxY = 0;
    return maxY;
}

function pviForceScrollTo(x, y) {
    var root = pviGetScrollRoot();

    pviEverTried = true;
    pviProgrammaticScrollUntil = pviNow() + pviProgrammaticGrace;

    try { window.scrollTo(x, y); } catch (e) {}

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

    var now = pviNow();

    if ((now - pviStartedAt) > pviMaxRuntime) {
        pviStopWatching();
        return;
    }

    var docW = pviGetDocWidth();
    var docH = pviGetDocHeight();

    if (docW === pviLastDocW && docH === pviLastDocH) {
        pviStableDocCount++;
    } else {
        pviStableDocCount = 0;
        pviReachedCount = 0;
        pviLastDocW = docW;
        pviLastDocH = docH;
        pviLastActivityAt = now;
    }

    var maxX = pviGetMaxScrollLeft();
    var maxY = pviGetMaxScrollTop();

    var targetX = pvileft;
    var targetY = pvitop;

    var targetXReachable = true;
    var targetYReachable = true;

    if (targetX > maxX) {
        targetX = maxX;
        targetXReachable = false;
    }

    if (targetY > maxY) {
        targetY = maxY;
        targetYReachable = false;
    }

    if (targetX < 0) targetX = 0;
    if (targetY < 0) targetY = 0;

    pviForceScrollTo(targetX, targetY);

    var pos = pviGetScrollPos();
    var dx = Math.abs(pos.x - targetX);
    var dy = Math.abs(pos.y - targetY);

    var reached = dx <= 1 && dy <= 1;
    var fullyReachable = targetXReachable && targetYReachable;
    var quietNeeded = fullyReachable ? pviQuietTimeReachable : pviQuietTimeClamped;
    var stableTicksNeeded = fullyReachable ? pviRequiredStableTicksReachable : pviRequiredStableTicksClamped;

    if (reached) {
        pviReachedCount++;
    } else {
        pviReachedCount = 0;
    }

    if (
        pviEverTried &&
        reached &&
        pviReachedCount >= stableTicksNeeded &&
        pviStableDocCount >= stableTicksNeeded &&
        (now - pviLastActivityAt) >= quietNeeded
    ) {
        pviStopWatching();
        return;
    }
}

function pviKick() {
    if (pviDone) return;
    setTimeout(pviWatchScroll, 0);
    setTimeout(pviWatchScroll, 100);
    setTimeout(pviWatchScroll, 333);
    setTimeout(pviWatchScroll, 777);
    setTimeout(pviWatchScroll, 1500);
}

try {
    window.addEventListener("scroll", function() {
        pviUserInteraction(true);
    }, true);
} catch (e) {}

try {
    window.addEventListener("wheel", function() {
        pviUserInteraction(false);
    }, true);
} catch (e) {}

try {
    window.addEventListener("mousewheel", function() {
        pviUserInteraction(false);
    }, true);
} catch (e) {}

try {
    window.addEventListener("touchstart", function() {
        pviUserInteraction(false);
    }, true);
} catch (e) {}

try {
    window.addEventListener("touchmove", function() {
        pviUserInteraction(false);
    }, true);
} catch (e) {}

try {
    window.addEventListener("mousedown", function() {
        pviUserInteraction(false);
    }, true);
} catch (e) {}

try {
    window.addEventListener("pointerdown", function() {
        pviUserInteraction(false);
    }, true);
} catch (e) {}

try {
    window.addEventListener("keydown", function(e) {
        var k = e.keyCode || e.which;
        if (
            k == 32 ||
            k == 33 ||
            k == 34 ||
            k == 35 ||
            k == 36 ||
            k == 37 ||
            k == 38 ||
            k == 39 ||
            k == 40
        ) {
            pviUserInteraction(false);
        }
    }, true);
} catch (e) {}

try {
    window.addEventListener("load", function() {
        pviMarkActivity();
        pviKick();
    }, false);
} catch (e) {}

try {
    document.addEventListener("load", function() {
        pviMarkActivity();
        pviKick();
    }, true);
} catch (e) {}

try {
    window.addEventListener("resize", function() {
        pviMarkActivity();
        pviKick();
    }, false);
} catch (e) {}

if (typeof MutationObserver != "undefined") {
    try {
        pviMutationObserver = new MutationObserver(function() {
            pviMarkActivity();
            pviKick();
        });
        if (document.documentElement) {
            pviMutationObserver.observe(document.documentElement, {
                childList:true,
                subtree:true,
                attributes:true,
                characterData:true
            });
        }
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
