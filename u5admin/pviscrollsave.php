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
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<?php require('backendcss.php'); ?></head>
<body bgcolor="#339900" text="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php
if($previewinitscrollRqHIADRI!='no')require_once('accadminnoclose.inc.php'); 

if ($_GET['pvileft']<1) $_GET['pvileft']=1; 
if ($_GET['pvitop']<1) $_GET['pvitop']=1; 

$pvicode = 'pvileft='.(int)$_GET['pvileft'].';pvitop='.(int)$_GET['pvitop'].';
var pviScrolling = false;
var pviDone = false;
var pviStartedAt = new Date().getTime();
var pviInterval = null;

var pviDoneX = false;
var pviDoneY = false;

var pviLastMaxX = -1;
var pviLastMaxY = -1;
var pviUnchangedMaxCountX = 0;
var pviUnchangedMaxCountY = 0;

function pviStopWatching() {
    if (pviInterval !== null) {
        clearInterval(pviInterval);
        pviInterval = null;
    }
    pviDone = true;
}

function pviGetScrollPos() {
    var x = window.scrollX != null ? window.scrollX : window.pageXOffset;
    var y = window.scrollY != null ? window.scrollY : window.pageYOffset;

    if ((x == null || y == null) && document.documentElement) {
        x = document.documentElement.scrollLeft;
        y = document.documentElement.scrollTop;
    }

    if ((x == null || y == null) && document.body) {
        x = document.body.scrollLeft;
        y = document.body.scrollTop;
    }

    return {
        x: x || 0,
        y: y || 0
    };
}

function pviGetMaxScrollPos() {
    var de = document.documentElement;
    var db = document.body;

    var scrollWidth = 0;
    var scrollHeight = 0;
    var clientWidth = 0;
    var clientHeight = 0;

    if (de) {
        if (de.scrollWidth > scrollWidth) scrollWidth = de.scrollWidth;
        if (de.scrollHeight > scrollHeight) scrollHeight = de.scrollHeight;
        if (de.clientWidth > clientWidth) clientWidth = de.clientWidth;
        if (de.clientHeight > clientHeight) clientHeight = de.clientHeight;
    }

    if (db) {
        if (db.scrollWidth > scrollWidth) scrollWidth = db.scrollWidth;
        if (db.scrollHeight > scrollHeight) scrollHeight = db.scrollHeight;
        if (db.clientWidth > clientWidth) clientWidth = db.clientWidth;
        if (db.clientHeight > clientHeight) clientHeight = db.clientHeight;
    }

    var maxX = scrollWidth - clientWidth;
    var maxY = scrollHeight - clientHeight;

    if (maxX < 0) maxX = 0;
    if (maxY < 0) maxY = 0;

    return {
        x: maxX,
        y: maxY
    };
}

function pviWatchScroll() {
    if (pviDone) return;

    if ((new Date().getTime() - pviStartedAt) > 30000) {
        pviStopWatching();
        return;
    }

    var pos = pviGetScrollPos();
    var max = pviGetMaxScrollPos();

    if (!pviDoneX) {
        if (Math.abs(pos.x - pvileft) <= 1) {
            pviDoneX = true;
        } else {
            if (max.x === pviLastMaxX) pviUnchangedMaxCountX++;
            else {
                pviUnchangedMaxCountX = 0;
                pviLastMaxX = max.x;
            }

            if (pviUnchangedMaxCountX >= 9 && pvileft > max.x) {
                pviDoneX = true;
            }
        }
    }

    if (!pviDoneY) {
        if (Math.abs(pos.y - pvitop) <= 1) {
            pviDoneY = true;
        } else {
            if (max.y === pviLastMaxY) pviUnchangedMaxCountY++;
            else {
                pviUnchangedMaxCountY = 0;
                pviLastMaxY = max.y;
            }

            if (pviUnchangedMaxCountY >= 9 && pvitop > max.y) {
                pviDoneY = true;
            }
        }
    }

    if (pviDoneX && pviDoneY) {
        pviStopWatching();
        return;
    }

    if (!pviScrolling) {
        var targetX = pos.x;
        var targetY = pos.y;
        var mustScroll = false;

        if (!pviDoneX) {
            targetX = pvileft;
            if (targetX > max.x) targetX = max.x;
            mustScroll = true;
        }

        if (!pviDoneY) {
            targetY = pvitop;
            if (targetY > max.y) targetY = max.y;
            mustScroll = true;
        }

        if (mustScroll) {
            pviScrolling = true;

            try {
                window.scrollTo({
                    left: targetX,
                    top: targetY,
                    behavior: "smooth"
                });
            } catch (e) {
                window.scrollTo(targetX, targetY);
            }

            setTimeout(function () {
                pviScrolling = false;
            }, 700);
        }
    }
}

pviWatchScroll();
pviInterval = setInterval(pviWatchScroll, 333);
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