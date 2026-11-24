/* Quiet, sequential maintenance. No browsing contexts, page navigation or HTML execution. */
(function (window) {
    'use strict';
    if (window.u5BackgroundWork) { window.u5BackgroundWork.drain(); return; }
    var queue = [], jobs = Object.create(null), active = false, ready = document.readyState === 'complete';
    var minimumDelay = 1000, retryDelay = 10000, requestTimeout = 120000;

    function address(raw, base, expectedPath) {
        var url = new URL(raw, base);
        if (url.origin !== window.location.origin || url.username || url.password
            || (expectedPath && url.pathname !== expectedPath)) throw new Error('Unexpected maintenance destination.');
        url.hash = '';
        return url;
    }
    function status(job, text) {
        if (!job.status) return;
        var target = document.getElementById(job.status);
        if (target) target.textContent = text;
    }
    function finish(job, text) {
        delete jobs[job.key];
        status(job, text);
        next();
    }
    function complete(job, reply) {
        if (!reply.completed) return;
        try {
            var top = window.top;
            top.document.title = (job.kind === 'index' ? ';' : ':') + top.document.title;
            if (job.kind === 'provision') {
                for (var current = window, depth = 0; current && depth < 4; depth++, current = current.parent) {
                    var notice = current.document.getElementById('htaccess');
                    if (notice) notice.style.display = 'block';
                    if (current === current.parent) break;
                }
            }
        } catch (_) { /* An embedding parent may belong to another origin. */ }
        try {
            var audio = new window.Audio(new URL('u5admin/' + (job.kind === 'index' ? 'indexingdone' : 'provisioningdone') + '.mp3', job.url).href);
            if (job.kind === 'provision') audio.volume = 0.05;
            var played = audio.play();
            if (played && played.catch) played.catch(function () {});
        } catch (_) { /* Notification failure must never repeat successful work. */ }
    }
    function wait(job, delay) {
        active = false;
        job.waiting = true;
        window.setTimeout(function () { job.waiting = false; queue.push(job); next(); }, delay);
        next();
    }
    function run(job) {
        if (typeof window.fetch !== 'function') { finish(job, 'Update pending. This browser cannot run background updates.'); return; }
        active = true;
        status(job, 'Updating...');
        var controller = typeof window.AbortController === 'function' ? new window.AbortController() : null;
        var timer = controller ? window.setTimeout(function () { controller.abort(); }, requestTimeout) : null;
        Promise.resolve().then(function () { return window.fetch(job.url.href, {
            credentials: 'same-origin', cache: 'no-store', redirect: 'error',
            headers: { 'Accept': 'application/json', 'X-U5-Background-Work': '1' },
            signal: controller ? controller.signal : undefined
        }); }).then(function (response) {
            if (!/^application\/json(?:;|$)/i.test(response.headers.get('Content-Type') || '')) throw new Error('Unexpected maintenance response.');
            return response.json().then(function (reply) {
                if (!reply || reply.u5work !== 1 || reply.kind !== job.kind
                    || ['done', 'idle', 'busy', 'wait', 'pending', 'error'].indexOf(reply.state) === -1) throw new Error('Invalid maintenance response.');
                if (reply.state === 'wait' && response.status !== 200 && response.status !== 202 && response.status !== 409) throw new Error('Invalid maintenance continuation.');
                if ((reply.state === 'done' || reply.state === 'idle') && !response.ok) throw new Error('Unsuccessful maintenance response.');
                return reply;
            });
        }).then(function (reply) {
            if (timer !== null) window.clearTimeout(timer);
            active = false;
            if (reply.state === 'wait') {
                try {
                    if (typeof reply.next !== 'string' || !Number.isFinite(reply.delay) || reply.delay < 0 || reply.delay > 86400000) throw new Error('Invalid continuation.');
                    job.url = address(reply.next, job.url.href, job.path);
                } catch (_) { finish(job, 'Update pending. Please try again.'); return; }
                job.failures = 0;
                wait(job, Math.max(minimumDelay, reply.delay));
            } else {
                if (reply.state === 'done') complete(job, reply);
                var message = 'Update pending. Please try again.';
                if (reply.state === 'done') message = 'Update complete.';
                if (reply.state === 'idle') message = 'Already up to date.';
                if (reply.state === 'busy') message = 'An update is already running.';
                finish(job, message);
            }
        }).catch(function () {
            if (timer !== null) window.clearTimeout(timer);
            active = false;
            if (job.failures++ < 2) wait(job, retryDelay);
            else finish(job, 'Update pending. Please try again.');
        });
    }
    function next() {
        if (!ready || active || !queue.length) return;
        run(queue.shift());
    }
    function drain() {
        var incoming = window.u5BackgroundWorkQueue || [];
        window.u5BackgroundWorkQueue = [];
        incoming.forEach(function (input) {
            try {
                if (!input || (input.kind !== 'index' && input.kind !== 'provision')) return;
                var url = address(input.url, document.baseURI);
                if (url.pathname.slice(-12) !== '/indexer.php' && input.kind === 'index') return;
                if (url.pathname.slice(-13) !== '/htaccess.php' && input.kind === 'provision') return;
                var key = input.kind + ':' + url.href;
                if (jobs[key]) return;
                var job = { key: key, kind: input.kind, url: url, path: url.pathname, status: input.status, failures: 0 };
                jobs[key] = job;
                queue.push(job);
            } catch (_) { /* Keep durable work pending rather than guessing a destination. */ }
        });
        next();
    }
    window.u5BackgroundWork = { drain: drain, pending: function () { return Object.keys(jobs).length; } };
    if (!ready) window.addEventListener('load', function () { ready = true; next(); }, { once: true });
    drain();
})(window);
