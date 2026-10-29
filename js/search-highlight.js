/* u5CMS search: shared normalization, literal matches and DOM-safe highlighting. */
(function (host, factory) {
    'use strict';
    var api = factory(host);
    if (typeof module === 'object' && module.exports) module.exports = api;
    if (host && host.document) {
        host.u5SearchHighlight = api;
        if (host.u5SearchConfig) api.ready = api.start(host.u5SearchConfig);
    }
}(typeof window === 'object' ? window : null, function (host) {
    'use strict';

    var MAX_TEXT = 1000000;
    var MAX_TERMS = 16;
    var MAX_MATCHES = 1000;
    var MAX_PARTS = 5000;
    var OWN_MARK = '[data-u5-search-hit]';
    // Match search-index.inc.php. Presentation CSS must not change query semantics.
    var BLOCK_TAGS = /^(address|article|aside|blockquote|body|br|caption|dd|details|dialog|div|dl|dt|fieldset|figcaption|figure|footer|form|h1|h2|h3|h4|h5|h6|header|hgroup|hr|html|legend|li|main|menu|nav|ol|p|pre|section|summary|table|tbody|td|tfoot|th|thead|tr|ul)$/;
    var mappingRequests = Object.create(null);
    var generation = 0;
    var stopPrevious = function () {};

    function foldWithOffsets(source, mapping) {
        var map = mapping.map || mapping;
        var output = [], starts = [], ends = [];
        var offset = 0, previousGroup = 0;
        for (var character of source) {
            var end = offset + character.length;
            var folded = Object.prototype.hasOwnProperty.call(map, character) ? map[character] : character;
            if (folded === '') {
                for (var old = previousGroup; old < ends.length; old++) ends[old] = end;
            } else {
                previousGroup = output.length;
                for (var i = 0; i < folded.length; i++) {
                    var unit = folded.charAt(i);
                    if (unit === ' ' && output.length && output[output.length - 1] === ' ') {
                        ends[ends.length - 1] = end;
                        if (previousGroup === output.length) previousGroup--;
                    } else {
                        output.push(unit);
                        starts.push(offset);
                        ends.push(end);
                    }
                }
            }
            offset = end;
        }
        return { text: output.join(''), starts: starts, ends: ends };
    }

    function normalizeText(text, mapping) {
        return foldWithOffsets(String(text), mapping).text.replace(/^ +| +$/g, '');
    }

    function findRanges(folded, terms) {
        var candidates = [], seen = Object.create(null), truncated = false;
        var needles = [];
        (terms || []).slice(0, MAX_TERMS).forEach(function (term) {
            var needle = term && term.normalized;
            if (typeof needle !== 'string' || needle === '' || needle.indexOf('\u0000') !== -1 || seen[needle]) return;
            seen[needle] = true;
            needles.push(needle);
        });
        needles.forEach(function (needle) {
            var offset = 0, found = 0, at;
            while ((at = folded.text.indexOf(needle, offset)) !== -1) {
                if (found++ >= MAX_MATCHES) { truncated = true; break; }
                candidates.push({ start: folded.starts[at], end: folded.ends[at + needle.length - 1] });
                offset = at + 1;
            }
        });
        candidates.sort(function (a, b) { return a.start - b.start || b.end - a.end; });
        var ranges = [];
        candidates.forEach(function (range) {
            var previous = ranges[ranges.length - 1];
            if (previous && range.start < previous.end) previous.end = Math.max(previous.end, range.end);
            else ranges.push(range);
        });
        if (ranges.length > MAX_MATCHES) { ranges.length = MAX_MATCHES; truncated = true; }
        return { ranges: ranges, truncated: truncated };
    }

    function searchRoot(document, config) {
        var root;
        if (config.rootSelector) {
            try { root = document.querySelector(config.rootSelector); } catch (ignore) {}
        }
        return root || document.querySelector('main') || document.body;
    }

    function clearMarks(root) {
        Array.from(root.querySelectorAll(OWN_MARK)).forEach(function (mark) {
            var parent = mark.parentNode;
            while (mark.firstChild) parent.insertBefore(mark.firstChild, mark);
            parent.removeChild(mark);
            parent.normalize();
        });
    }

    function isPanel(element) {
        return element.classList.contains('showblockdivinner') || element.classList.contains('showblock2divinner');
    }

    function collectText(root) {
        var document = root.ownerDocument, view = document.defaultView;
        var pieces = [], nodes = [], length = 0, truncated = false;
        function append(value, node) {
            if (length >= MAX_TEXT) { truncated = true; return; }
            var available = MAX_TEXT - length;
            if (value.length > available) {
                value = value.slice(0, available);
                if (/[\uD800-\uDBFF]$/.test(value)) value = value.slice(0, -1);
                truncated = true;
            }
            if (!value) return;
            pieces.push(value);
            if (node) nodes.push({ node: node, start: length, end: length + value.length });
            length += value.length;
        }
        function visit(node) {
            if (length >= MAX_TEXT) { truncated = true; return; }
            if (node.nodeType === 3) { append(node.data, node); return; }
            if (node.nodeType !== 1) return;
            var tag = node.tagName.toLowerCase();
            if (/^(script|style|noscript|template)$/.test(tag)) return;
            var style = view.getComputedStyle(node);
            var panel = isPanel(node);
            if ((node.hidden || style.display === 'none' || node.getAttribute('aria-hidden') === 'true') && !panel) return;
            if (/^(form|input|select|textarea|button|iframe|canvas|svg|math|object|embed|mark)$/.test(tag) ||
                node.isContentEditable || node.hasAttribute('data-u5-search-ignore') || node.classList.contains('hitshilite')) {
                // A visible excluded subtree is a barrier, never a false phrase join.
                append('\u0000');
                return;
            }
            if (tag === 'br' || tag === 'hr') { append(' '); return; }
            var block = BLOCK_TAGS.test(tag);
            if (block) append(' ');
            for (var child = node.firstChild; child; child = child.nextSibling) visit(child);
            if (block) append(' ');
        }
        visit(root);
        return { text: pieces.join(''), nodes: nodes, truncated: truncated };
    }

    function wrapRanges(root, collected, ranges) {
        var plans = [], groups = [], nodeIndex = 0, truncated = false;
        for (var r = 0; r < ranges.length; r++) {
            var range = ranges[r], parts = [];
            while (nodeIndex < collected.nodes.length && collected.nodes[nodeIndex].end <= range.start) nodeIndex++;
            for (var n = nodeIndex; n < collected.nodes.length && collected.nodes[n].start < range.end; n++) {
                var record = collected.nodes[n];
                var start = Math.max(range.start, record.start) - record.start;
                var end = Math.min(range.end, record.end) - record.start;
                if (end > start) parts.push({ node: record.node, start: start, end: end, group: groups.length });
            }
            if (plans.length + parts.length > MAX_PARTS) { truncated = true; break; }
            if (parts.length) { plans.push.apply(plans, parts); groups.push([]); }
        }
        var document = root.ownerDocument;
        for (var i = plans.length - 1; i >= 0; i--) {
            var plan = plans[i], text = plan.node;
            text.splitText(plan.end);
            var selected = text.splitText(plan.start);
            var mark = document.createElement('span');
            mark.className = 'hitshilite';
            mark.setAttribute('data-u5-search-hit', String(plan.group));
            selected.parentNode.replaceChild(mark, selected);
            mark.appendChild(selected);
            groups[plan.group].unshift(mark);
        }
        return { groups: groups, parts: plans.length, truncated: truncated };
    }

    function meaningfulHash(view) {
        var hash = view.location.hash;
        if (!hash || hash === '#') return false;
        if (hash.indexOf(':~:text=') !== -1) return true;
        try { hash = decodeURIComponent(hash.slice(1)); } catch (ignore) { hash = hash.slice(1); }
        return Boolean(view.document.getElementById(hash) || view.document.getElementsByName(hash).length);
    }

    function visible(element) {
        if (!element || !element.isConnected || !element.getClientRects().length) return false;
        var view = element.ownerDocument.defaultView;
        for (var parent = element; parent; parent = parent.parentElement) {
            var style = view.getComputedStyle(parent);
            if (parent.hidden || style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity) === 0) return false;
        }
        var rect = element.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    function revealAncestors(element, root) {
        var ancestors = [], parent = element.parentElement;
        while (parent) {
            ancestors.push(parent);
            if (parent === root) break;
            parent = parent.parentElement;
        }
        // Open outside-in without the old singleton accordion closing an ancestor.
        ancestors.reverse().forEach(function (ancestor) {
            if (ancestor.tagName === 'DETAILS') ancestor.open = true;
            if (!isPanel(ancestor)) return;
            var second = ancestor.classList.contains('showblock2divinner');
            var prefix = second ? 'showblock2' : 'showblock';
            var toggle = ancestor.previousElementSibling;
            ancestor.hidden = false;
            ancestor.style.display = 'block';
            if (ancestor.hasAttribute('aria-hidden')) ancestor.setAttribute('aria-hidden', 'false');
            if (toggle && (toggle.classList.contains(prefix + 'inactivetab') || toggle.classList.contains(prefix + 'activetab'))) {
                toggle.classList.remove(prefix + 'inactivetab');
                toggle.classList.add(prefix + 'activetab');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    }

    function headerOffset(document) {
        var view = document.defaultView, bottom = 0;
        var elements = document.querySelectorAll('body > *, header, nav, [role="banner"], #header, [data-u5-search-header], .fixed-top, .sticky-top');
        Array.from(elements).forEach(function (element) {
            var style = view.getComputedStyle(element);
            if (style.position !== 'fixed' && style.position !== 'sticky') return;
            var rect = element.getBoundingClientRect();
            if (!rect.width || !rect.height || rect.height > view.innerHeight / 2 || style.visibility === 'hidden' || style.display === 'none') return;
            if (style.position === 'fixed' && rect.top >= -2 && rect.top < view.innerHeight / 2) bottom = Math.max(bottom, rect.bottom);
            if (style.position === 'sticky' && style.top !== 'auto') {
                var top = parseFloat(style.top);
                if (top >= 0 && top < view.innerHeight / 2) bottom = Math.max(bottom, top + rect.height);
            }
        });
        return bottom + 16;
    }

    function revealScrollContainers(element) {
        var document = element.ownerDocument, view = document.defaultView;
        for (var parent = element.parentElement; parent && parent !== document.body && parent !== document.documentElement; parent = parent.parentElement) {
            var style = view.getComputedStyle(parent);
            var vertical = /^(auto|scroll|hidden)$/.test(style.overflowY) && parent.scrollHeight > parent.clientHeight;
            var horizontal = /^(auto|scroll|hidden)$/.test(style.overflowX) && parent.scrollWidth > parent.clientWidth;
            if (!vertical && !horizontal) continue;
            var box = parent.getBoundingClientRect(), hit = element.getBoundingClientRect();
            var top = box.top + parent.clientTop, left = box.left + parent.clientLeft;
            var nextTop = parent.scrollTop, nextLeft = parent.scrollLeft;
            if (vertical && (hit.top < top || hit.bottom > top + parent.clientHeight)) {
                nextTop += hit.top - top - Math.min(16, Math.max(0, (parent.clientHeight - hit.height) / 2));
            }
            if (horizontal) {
                if (hit.left < left) nextLeft += hit.left - left;
                else if (hit.right > left + parent.clientWidth) nextLeft += hit.right - left - parent.clientWidth;
            }
            // Scroll offsets may be rounded to whole CSS pixels by the browser.
            if (nextTop !== parent.scrollTop) nextTop = nextTop > parent.scrollTop ? Math.ceil(nextTop) : Math.floor(nextTop);
            if (nextLeft !== parent.scrollLeft) nextLeft = nextLeft > parent.scrollLeft ? Math.ceil(nextLeft) : Math.floor(nextLeft);
            // Resolve inner clipping before computing the window's destination. Instant
            // movement also avoids a container's own scroll-behavior invalidating it.
            if (nextTop !== parent.scrollTop || nextLeft !== parent.scrollLeft) parent.scrollTo({ top: nextTop, left: nextLeft, behavior: 'instant' });
        }
    }

    function watchInteraction(view) {
        var state = { cancelled: false };
        var events = ['pointerdown', 'touchstart', 'wheel', 'keydown'];
        function cancel(event) { if (event.isTrusted) state.cancelled = true; }
        events.forEach(function (name) { view.addEventListener(name, cancel, { passive: true, capture: true }); });
        state.stop = function () { events.forEach(function (name) { view.removeEventListener(name, cancel, true); }); };
        return state;
    }

    function scheduleScroll(root, groups, interaction, config) {
        var document = root.ownerDocument, view = document.defaultView;
        if (!groups.length || config.autoScroll === false || meaningfulHash(view) || view.scrollY > 16) {
            interaction.stop();
            return function () {};
        }
        var stopped = false, pending = false, attempts = 0, didScroll = false;
        var first = null, lastTop = null, lastMove = 0, observer = null, resizeObserver = null;
        var deadline = view.setTimeout(stop, 3500);
        function stop() {
            stopped = true;
            view.clearTimeout(deadline);
            if (observer) observer.disconnect();
            if (resizeObserver) resizeObserver.disconnect();
            view.removeEventListener('resize', queue);
            view.removeEventListener('load', queue);
            document.removeEventListener('load', queue, true);
            interaction.stop();
        }
        function move() {
            pending = false;
            if (stopped || interaction.cancelled || meaningfulHash(view) || document.visibilityState === 'hidden') { stop(); return; }
            // The mobile template hides the document until it has moved the content.
            if (!visible(root)) return;
            if (!first || !visible(first)) {
                first = null;
                for (var i = 0; i < groups.length; i++) {
                    revealAncestors(groups[i][0], root);
                    first = groups[i].find(visible) || null;
                    if (first) break;
                }
            }
            if (!first) return;
            revealScrollContainers(first);
            var offset = headerOffset(document);
            var target = Math.max(0, first.getBoundingClientRect().top + view.scrollY - offset);
            // A mobile wrapper may preserve position but scroll to 7px afterwards.
            if (didScroll && Math.abs(target - lastTop) < 2 && (Math.abs(view.scrollY - target) < 4 || Date.now() - lastMove < 550)) return;
            if (attempts++ >= 4) { stop(); return; }
            first.style.scrollMarginTop = offset + 'px';
            var reduced = view.matchMedia && view.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var behavior = !didScroll && !reduced ? 'smooth' : 'auto';
            lastTop = target;
            lastMove = Date.now();
            didScroll = true;
            view.scrollTo({ top: target, left: view.scrollX, behavior: behavior });
            view.setTimeout(queue, 600);
        }
        function queue() {
            if (pending || stopped) return;
            pending = true;
            // Let mobile DOM moves and their zero-delay scroll complete first.
            view.requestAnimationFrame(function () { view.requestAnimationFrame(move); });
        }
        if (view.MutationObserver) {
            observer = new view.MutationObserver(queue);
            // The mobile template's visibility fallback changes html, not body.
            observer.observe(document.documentElement, { subtree: true, childList: true, attributes: true, attributeFilter: ['style', 'class', 'hidden', 'open'] });
        }
        if (view.ResizeObserver) { resizeObserver = new view.ResizeObserver(queue); resizeObserver.observe(root); }
        view.addEventListener('resize', queue);
        view.addEventListener('load', queue);
        document.addEventListener('load', queue, true);
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(queue, function () {});
        queue();
        return stop;
    }

    function announce(document, result) {
        var detail = { status: result.status, count: result.count || 0, parts: result.parts || 0, truncated: Boolean(result.truncated) };
        document.dispatchEvent(new document.defaultView.CustomEvent('u5-search-highlight', { detail: detail }));
        return result;
    }

    function highlight(document, config, mapping, interaction) {
        var root = searchRoot(document, config);
        if (!root) return { status: 'no-root', count: 0, parts: 0 };
        clearMarks(root);
        var collected = collectText(root);
        var matching = findRanges(foldWithOffsets(collected.text, mapping), config.terms);
        var wrapped = wrapRanges(root, collected, matching.ranges);
        var result = {
            status: 'complete', count: wrapped.groups.length, parts: wrapped.parts,
            truncated: collected.truncated || matching.truncated || wrapped.truncated
        };
        interaction = interaction || watchInteraction(document.defaultView);
        var stopScroll = scheduleScroll(root, wrapped.groups, interaction, config);
        stopPrevious = function () { stopScroll(); interaction.stop(); };
        if (!wrapped.groups.length || config.autoScroll === false || meaningfulHash(document.defaultView)) interaction.stop();
        return announce(document, result);
    }

    function loadMapping(url) {
        if (!mappingRequests[url]) {
            mappingRequests[url] = (async function () {
                var controller = typeof host.AbortController === 'function' ? new host.AbortController() : null;
                var timer = controller ? host.setTimeout(function () { controller.abort(); }, 5000) : null;
                try {
                    var response = await host.fetch(url, { credentials: 'same-origin', cache: 'force-cache', signal: controller ? controller.signal : undefined });
                    if (!response.ok) throw new Error('Search normalization unavailable');
                    var mapping = await response.json();
                    if (!mapping || mapping.version !== 1 || !mapping.map || typeof mapping.map !== 'object') throw new Error('Invalid search normalization');
                    return mapping;
                } finally { if (timer !== null) host.clearTimeout(timer); }
            }());
            mappingRequests[url].catch(function () { delete mappingRequests[url]; });
        }
        return mappingRequests[url];
    }

    async function start(config) {
        if (!host || !host.document) return { status: 'no-document', count: 0 };
        stopPrevious();
        var current = ++generation, document = host.document;
        var interaction = watchInteraction(host);
        stopPrevious = interaction.stop;
        try {
            if (document.readyState === 'loading') await new Promise(function (resolve) { document.addEventListener('DOMContentLoaded', resolve, { once: true }); });
            if (current !== generation) { interaction.stop(); return { status: 'superseded', count: 0 }; }
            if (!config || !Array.isArray(config.terms) || !config.terms.length) {
                var root = searchRoot(document, config || {});
                if (root) clearMarks(root);
                interaction.stop();
                return announce(document, { status: 'empty', count: 0 });
            }
            var mapping = config.normalization || await loadMapping(config.normalizationUrl || 'search-normalization.json');
            if (current !== generation) { interaction.stop(); return { status: 'superseded', count: 0 }; }
            return highlight(document, config, mapping, interaction);
        } catch (error) {
            interaction.stop();
            return announce(document, { status: 'normalization-unavailable', count: 0, parts: 0 });
        }
    }

    return { start: start, highlight: highlight, normalizeText: normalizeText, foldWithOffsets: foldWithOffsets, findRanges: findRanges };
}));
