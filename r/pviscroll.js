pvileft=270;pvitop=180;
var pviScrolling = false;
var pviDone = false;
var pviStartedAt = new Date().getTime();
var pviInterval = null;
var pviLastMaxX = -1;
var pviLastMaxY = -1;
var pviUnchangedMaxCount = 0;

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
    var dx = Math.abs(pos.x - pvileft);
    var dy = Math.abs(pos.y - pvitop);

    if (dx <= 1 && dy <= 1) {
        pviStopWatching();
        return;
    }

    var max = pviGetMaxScrollPos();

    if (max.x === pviLastMaxX && max.y === pviLastMaxY) {
        pviUnchangedMaxCount++;
    } else {
        pviUnchangedMaxCount = 0;
        pviLastMaxX = max.x;
        pviLastMaxY = max.y;
    }

    if (pviUnchangedMaxCount >= 9 && (pvileft > max.x || pvitop > max.y)) {
        pviStopWatching();
        return;
    }

    if (!pviScrolling) {
        pviScrolling = true;

        var targetX = pvileft;
        var targetY = pvitop;

        if (targetX > max.x) targetX = max.x;
        if (targetY > max.y) targetY = max.y;

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

            var pos2 = pviGetScrollPos();
            var dx2 = Math.abs(pos2.x - pvileft);
            var dy2 = Math.abs(pos2.y - pvitop);

            if (dx2 <= 1 && dy2 <= 1) {
                pviStopWatching();
            }
        }, 700);
    }
}

pviWatchScroll();
pviInterval = setInterval(pviWatchScroll, 333);
