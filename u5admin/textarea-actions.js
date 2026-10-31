/* u5CMS source-text actions. No rendered-content replacement or rich-text editor. */
(function (root, factory) {
    'use strict';
    var api = factory();
    if (typeof module === 'object' && module.exports) module.exports = api;
    if (root && root.document) api.install(root, root.u5TextareaConfig || {});
}(typeof window === 'undefined' ? null : window, function () {
    'use strict';
    var namePattern = /^[A-Za-z0-9_!]+(?:-[A-Za-z0-9_!-]+)?$/;
    function validName(name) {
        return typeof name === 'string' && name.length > 0 && name.length <= 255 &&
            namePattern.test(name) && (name.indexOf('-') < 0 || /![A-Za-z0-9]+$/.test(name));
    }
    function protectedRanges(text) {
        var ranges = [], match;
        var pattern = /\[h:\][\s\S]*?(?:\[:h\]|$)|\{{3}[\s\S]*?(?:\}{3}|$)|<!--[\s\S]*?(?:-->|$)|<(script|style|pre|code)\b[^>]*>[\s\S]*?(?:<\/\1\s*>|$)|<\?[\s\S]*?(?:\?>|$)|<[^>\r\n]*>|\[{2,}[^]*?(?:\]{2,}|$)/gi;
        while ((match = pattern.exec(text))) {
            // Angle brackets in a link label are literal source text, not an HTML block.
            if (match[0][0] === '<' && text.lastIndexOf('[',match.index) > text.lastIndexOf(']',match.index)) {
                pattern.lastIndex=match.index+match[0].indexOf('>')+1; continue;
            }
            ranges.push([match.index, pattern.lastIndex]);
        }
        var stack = [], nested = false;
        for (var i = 0; i < text.length; i++) {
            if (text[i] === '[') { if (stack.length) nested = true; stack.push(i); }
            else if (text[i] === ']' && stack.length) {
                var begin = stack.pop();
                if (!stack.length) { if (nested) ranges.push([begin, i + 1]); nested = false; }
            }
        }
        if (stack.length) ranges.push([stack[0], text.length]);
        return ranges;
    }
    function overlaps(ranges, start, end) {
        return ranges.some(function (r) { return start < r[1] && Math.max(end, start + 1) > r[0]; });
    }
    function references(text, reserved) {
        var ranges = protectedRanges(text), refs = [], match;
        var pattern = /\[([^\[\]\r\n]+)\]/g;
        while ((match = pattern.exec(text))) {
            if (overlaps(ranges, match.index, pattern.lastIndex)) continue;
            var inner = match[1], colon = inner.lastIndexOf(':'), target = inner.slice(colon + 1);
            var question = target.indexOf('?'), name = question < 0 ? target : target.slice(0, question);
            if (!validName(name) || name.charAt(0) === '_' || (reserved || []).indexOf(match[0]) >= 0) continue;
            refs.push({ start: match.index, end: pattern.lastIndex, raw: match[0], name: name,
                prefix: colon < 0 ? '' : inner.slice(0, colon + 1),
                query: question < 0 ? '' : target.slice(question),
                targetStart: match.index + 1 + colon + 1,
                inclusion: inner.slice(0, colon) === '$$$', button: inner.charAt(0) === ':' });
        }
        return refs;
    }
    function at(text, start, end, reserved) {
        return references(text, reserved).find(function (r) {
            return start >= r.start && start < r.end && end <= r.end;
        }) || null;
    }
    function replaceTarget(ref, name) {
        if (!validName(name)) throw new Error('Invalid object name.');
        return '[' + ref.prefix + name + ref.query + ']';
    }
    function replaceLabel(ref, label) {
        if (ref.inclusion || typeof label !== 'string' || /[\[\]\r\n]/.test(label) || label === '$$$')
            throw new Error('This label cannot be used in a reference.');
        return '[' + (ref.button ? ':' : '') + label + ':' + ref.name + ref.query + ']';
    }
    function plainLabel(ref) {
        if (ref.inclusion) return '';
        return ref.prefix ? ref.prefix.slice(ref.button ? 1 : 0, -1) : '';
    }
    function format(text, start, end, open, close, reserved) {
        if (overlaps(protectedRanges(text), start, end)) throw new Error('Select source text outside HTML/code blocks.');
        if (references(text, reserved).some(function (r) {
            return (start > r.start && start < r.end) || (end > r.start && end < r.end);
        })) throw new Error('Select the complete reference or text outside it.');
        if (text.slice(Math.max(0, start - open.length), start) === open && text.slice(end, end + close.length) === close) {
            return { start: start - open.length, end: end + close.length, text: text.slice(start, end), select: [0, end - start] };
        }
        return { start: start, end: end, text: open + text.slice(start, end) + close, select: [open.length, open.length + end - start] };
    }
    function install(win, config) {
        var doc = win.document;
        function init() {
            var areas = Array.from(doc.querySelectorAll('textarea[id^="ta"][name^="content_"]'));
            if (!areas.length || win.u5TextareaActions) return;
            var histories = new WeakMap(), active = null, menu = null, serial = 0, pending = null;
            var formats = config.formats || [], reserved = config.reserved || [];
            function snapshot(ta) {
                return { value: ta.value, start: ta.selectionStart, end: ta.selectionEnd,
                    direction: ta.selectionDirection, top: ta.scrollTop, left: ta.scrollLeft };
            }
            function state(ta) {
                var h = histories.get(ta);
                if (!h) { h = { current: snapshot(ta), past: [], future: [], time: 0, kind: '' }; histories.set(ta, h); }
                return h;
            }
            function record(ta, before, kind) {
                var h = state(ta), now = Date.now();
                if (before.value === ta.value) { h.current = snapshot(ta); return; }
                if (!(kind === h.kind && /^(insertText|deleteContentBackward|deleteContentForward)$/.test(kind) && now - h.time < 900))
                    h.past.push(before);
                h.current = snapshot(ta); h.future = []; h.kind = kind; h.time = now;
                var size = h.current.value.length;
                for (var i = h.past.length - 1; i >= 0; i--) {
                    size += h.past[i].value.length;
                    if (h.past.length > 50 || size > 4 * 1024 * 1024) { h.past.shift(); i = h.past.length; size = h.current.value.length; }
                    if (!h.past.length) break;
                }
            }
            function notify(ta) {
                if (typeof win.mychanges === 'function') win.mychanges();
                if (typeof win.dosync === 'function') win.dosync(win.name, ta.id, ta.value);
            }
            function restore(ta, s) {
                ta.value = s.value; ta.focus({ preventScroll: true });
                ta.setSelectionRange(s.start, s.end, s.direction); ta.scrollTop = s.top; ta.scrollLeft = s.left;
                state(ta).current = snapshot(ta); notify(ta);
            }
            function undo(ta, redo) {
                var h = state(ta);
                if (ta.value !== h.current.value) { h.past = []; h.future = []; h.current = snapshot(ta); return; }
                var from = redo ? h.future : h.past, to = redo ? h.past : h.future;
                if (!from.length) return;
                to.push(snapshot(ta)); h.kind = ''; restore(ta, from.pop());
            }
            function dismiss(focus) {
                serial++;
                if (pending) pending.abort(); pending = null;
                if (menu) menu.remove(); menu = null;
                if (focus && active && active.ta.isConnected) {
                    var s = active.snapshot, ta = active.ta;
                    ta.focus({ preventScroll: true });
                    if (ta.value === s.value) { ta.setSelectionRange(s.start, s.end, s.direction); ta.scrollTop = s.top; ta.scrollLeft = s.left; }
                }
            }
            function element(tag, text, parent) {
                var el = doc.createElement(tag); if (text !== undefined) el.textContent = text;
                if (parent) parent.appendChild(el); return el;
            }
            function status(message) {
                var node = menu && menu.querySelector('[role="status"]'); if (node) node.textContent = message;
            }
            function unchanged() {
                if (!active || active.ta.value !== active.snapshot.value) { status('The source changed. Close and reopen the actions.'); return false; }
                return !active.ta.readOnly && !active.ta.disabled;
            }
            function edit(plan) {
                if (!unchanged()) return;
                var ta = active.ta, before = active.snapshot;
                ta.value = before.value.slice(0, plan.start) + plan.text + before.value.slice(plan.end);
                var range = plan.select || [plan.text.length, plan.text.length];
                ta.setSelectionRange(plan.start + range[0], plan.start + range[1]);
                ta.scrollTop = before.top; ta.scrollLeft = before.left;
                record(ta, before, 'action'); notify(ta);
                active.snapshot = snapshot(ta); dismiss(true);
                ta.dispatchEvent(new win.CustomEvent('u5-textarea-edit', { bubbles: true }));
            }
            function button(text, action, parent) {
                var b = element('button', text, parent || menu); b.type = 'button';
                b.addEventListener('click', function () { try { action(); } catch (error) { status(error.message); } }); return b;
            }
            function link(text, url, parent) {
                var resolved;
                try { resolved = new win.URL(url, win.location.href); } catch (e) { return; }
                if (resolved.origin !== win.location.origin || !/^https?:$/.test(resolved.protocol)) return;
                var a = element('a', text, parent); a.href = resolved.href; a.target = '_blank'; a.rel = 'noopener noreferrer';
                a.addEventListener('click', function () { dismiss(true); });
            }
            async function request(params) {
                var controller = new win.AbortController(); pending = controller;
                var url = new win.URL('textarea-resource.php', win.location.href);
                Object.keys(params).forEach(function (k) { url.searchParams.set(k, params[k]); });
                var timer = win.setTimeout(function () { controller.abort(); }, 12000);
                try {
                    var response = await win.fetch(url.href, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal,
                        headers: { Accept: 'application/json' } });
                    if (!response.ok || !(response.headers.get('Content-Type') || '').includes('application/json'))
                        throw new Error('Object information unavailable. Check your login, then retry.');
                    return await response.json();
                } finally { win.clearTimeout(timer); if (pending === controller) pending = null; }
            }
            function errorText(error, generation) {
                if (menu && serial === generation) status(error.name === 'AbortError' ? 'The request timed out. Retry when the server is ready.' : error.message);
            }
            function editorLanguage() { return active.ta.getAttribute('lang') || ''; }
            function inputRow(label, value, action) {
                var form = element('form', undefined, menu), l = element('label', label, form);
                var input = element('input', undefined, l); input.type = 'text'; input.value = value;
                input.maxLength = 1024; var submit = element('button', 'Apply', form); submit.type = 'submit';
                form.addEventListener('submit', function (e) { e.preventDefault(); Promise.resolve(action(input.value)).catch(function (error) { status(error.message); }); });
                input.focus(); input.select(); return input;
            }
            function replaceDialog(ref) {
                if (!unchanged()) return;
                inputRow('Object name', ref.name, async function (name) {
                    if (!validName(name)) throw new Error('Enter a valid object name.');
                    var generation = serial, data = await request({ object: name, language: editorLanguage() });
                    if (!menu || generation !== serial) return;
                    if (!data.found) throw new Error('Object not found. Correct the name or use Search.');
                    edit({ start: ref.start, end: ref.end, text: replaceTarget(ref, data.name) });
                });
            }
            function searchDialog(ref) {
                var results = element('div', undefined, menu);
                inputRow('Search object names', ref.name, async function (term) {
                    var generation = serial, data = await request({ mode: 'search', term: term });
                    if (!menu || generation !== serial) return;
                    results.replaceChildren();
                    (data.results || []).forEach(function (item) {
                        button(item.name + ' (' + item.type.toUpperCase() + ')', function () {
                            edit({ start: ref.start, end: ref.end, text: replaceTarget(ref, item.name) });
                        }, results);
                    });
                    status(data.results && data.results.length ? 'Select an object to replace this reference.' : 'No matching objects.');
                });
            }
            async function usageDialog(ref) {
                var generation = serial, data = await request({ object: ref.name, mode: 'usages' });
                if (!menu || serial !== generation) return;
                var results = element('div', undefined, menu);
                (data.results || []).forEach(function (item) { link(item.name, item.url, results); });
                status(data.more ? 'More candidates exist; narrow the repository search for the complete list.' :
                    ((data.results || []).length ? 'Stored references in current and archived objects.' : 'No stored references found. Unsaved text is not included.'));
            }
            async function objectActions(ref, target) {
                var generation = serial;
                try {
                    var data = await request({ object: ref.name, language: editorLanguage() });
                    if (!menu || serial !== generation) return;
                    target.replaceChildren();
                    if (!data.found) { element('span', 'Object not found: ' + ref.name, target); return; }
                    element('strong', data.name + ' (' + data.type.toUpperCase() + (data.archived ? ', archive' : '') + ')', target);
                    if (data.edit) link('Edit', data.edit, target);
                    if (data.preview) link('Preview', data.preview, target);
                    if (data.metadata) link('Metadata', data.metadata, target);
                    if (data.original) link('Original / download', data.original, target);
                    if (!data.edit) element('span', 'Editing requires higher administrator rights.', target);
                    status('');
                } catch (error) { errorText(error, generation); }
            }
            function show(ta, x, y) {
                dismiss(false); active = { ta: ta, snapshot: snapshot(ta) };
                var s = active.snapshot, ref = at(s.value, s.start, s.end, reserved);
                menu = element('div', undefined, doc.body); menu.className = 'u5-textarea-menu';
                menu.setAttribute('role', 'dialog'); menu.setAttribute('aria-label', 'Text and object actions');
                menu.setAttribute('tabindex', '-1');
                var heading = element('div', undefined, menu); heading.className = 'u5-textarea-heading';
                element('strong', ref ? 'Object reference' : 'Text actions', heading);
                button('Close', function () { dismiss(true); }, heading).setAttribute('aria-label', 'Close text actions');
                var message = element('div', '', menu); message.setAttribute('role', 'status'); message.setAttribute('aria-live', 'polite');
                var row = element('div', undefined, menu);
                formats.forEach(function (f) {
                    var b = button(f.label, function () { edit(format(s.value, s.start, s.end, f.open, f.close, reserved)); }, row);
                    b.disabled = (ref && (s.start !== ref.start || s.end !== ref.end)) || overlaps(protectedRanges(s.value), s.start, s.end) || ta.readOnly || ta.disabled;
                });
                button('Undo', function () { undo(ta, false); active.snapshot = snapshot(ta); dismiss(true); }, row).disabled = !state(ta).past.length;
                button('Redo', function () { undo(ta, true); active.snapshot = snapshot(ta); dismiss(true); }, row).disabled = !state(ta).future.length;
                if (ref) {
                    var info = element('div', 'Loading object...', menu); objectActions(ref, info);
                    var actions = element('div', undefined, menu);
                    button('Replace target', function () { replaceDialog(ref); }, actions);
                    if (!ref.inclusion) button('Link text', function () {
                        inputRow('Link text', plainLabel(ref), function (label) {
                            edit({ start: ref.start, end: ref.end, text: replaceLabel(ref, label) });
                        });
                    }, actions);
                    button('Search / correct', function () { searchDialog(ref); }, actions);
                    button('Uses', function () { usageDialog(ref).catch(function (error) { errorText(error, serial); }); }, actions);
                    button('Copy reference', async function () {
                        if (!win.navigator.clipboard || !win.isSecureContext) {
                            inputRow('Copy reference (Ctrl/Cmd+C)', ref.raw, function () {}); return;
                        }
                        try { await win.navigator.clipboard.writeText(ref.raw); status('Reference copied.'); }
                        catch (error) { if (menu) inputRow('Copy reference (Ctrl/Cmd+C)', ref.raw, function () {}); }
                    }, actions);
                    button('Remove reference', function () { edit({ start: ref.start, end: ref.end, text: plainLabel(ref) }); }, actions);
                }
                menu.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); dismiss(true); return; }
                    var controls = Array.from(menu.querySelectorAll('button:not([disabled]),a,input'));
                    if (e.key === 'Tab' && controls.length) {
                        if (e.shiftKey && doc.activeElement === controls[0]) { e.preventDefault(); controls[controls.length - 1].focus(); }
                        else if (!e.shiftKey && doc.activeElement === controls[controls.length - 1]) { e.preventDefault(); controls[0].focus(); }
                    }
                });
                menu.style.left = Math.max(2, Math.min(x, win.innerWidth - menu.offsetWidth - 2)) + 'px';
                menu.style.top = Math.max(2, Math.min(y, win.innerHeight - menu.offsetHeight - 2)) + 'px';
                var observer = new win.MutationObserver(function () {
                    if (!menu) { observer.disconnect(); return; }
                    menu.style.top = Math.max(2, Math.min(parseFloat(menu.style.top), win.innerHeight - menu.offsetHeight - 2)) + 'px';
                });
                observer.observe(menu, { childList: true, subtree: true });
                menu.focus({ preventScroll: true });
            }
            areas.forEach(function (ta) {
                state(ta); var before = null;
                ta.addEventListener('focus', function () { if (state(ta).current.value !== ta.value) receive(ta.id, ta.value); });
                ta.addEventListener('beforeinput', function (e) {
                    if (e.inputType === 'historyUndo' || e.inputType === 'historyRedo') { e.preventDefault(); undo(ta, e.inputType === 'historyRedo'); }
                    else before = snapshot(ta);
                });
                ta.addEventListener('input', function (e) {
                    record(ta, before || state(ta).current, e.inputType || 'input'); before = null; notify(ta);
                });
                ta.addEventListener('contextmenu', function (e) {
                    if (e.shiftKey) return;
                    var ref = at(ta.value, ta.selectionStart, ta.selectionEnd, reserved);
                    if (ref || ta.selectionStart !== ta.selectionEnd) { e.preventDefault(); show(ta, e.clientX, e.clientY); }
                });
                ta.addEventListener('keydown', function (e) {
                    if (e.isComposing) return;
                    if ((e.altKey && e.key === 'Enter') || e.key === 'ContextMenu' || (e.shiftKey && e.key === 'F10')) {
                        e.preventDefault(); e.stopPropagation(); var box = ta.getBoundingClientRect(); show(ta, box.left + 12, box.top + 12);
                    } else if ((e.ctrlKey || e.metaKey) && !e.altKey && /^(z|y)$/i.test(e.key)) {
                        e.preventDefault(); e.stopPropagation(); undo(ta, e.key.toLowerCase() === 'y' || e.shiftKey);
                    }
                });
                ta.addEventListener('select', function () { if (!menu) active = { ta: ta, snapshot: snapshot(ta) }; });
                ta.addEventListener('focus', function () { active = { ta: ta, snapshot: snapshot(ta) }; });
            });
            function receive(id, value) {
                var ta = doc.getElementById(id); if (!ta || areas.indexOf(ta) < 0) return false;
                if (ta.value === value) return true;
                var before = snapshot(ta); ta.value = value;
                ta.setSelectionRange(Math.min(before.start,value.length),Math.min(before.end,value.length),before.direction);
                ta.scrollTop=before.top;ta.scrollLeft=before.left;
                record(ta, before, 'sync'); return true;
            }
            var trigger = doc.getElementById('u5-textarea-trigger');
            if (trigger) {
                trigger.addEventListener('mousedown', function (e) { e.preventDefault(); });
                trigger.addEventListener('click', function () {
                    var ta = active && active.ta;
                    if (!ta || !ta.offsetHeight) ta = areas.find(function (item) { return item.offsetHeight > 0; });
                    if (ta) { var box = trigger.getBoundingClientRect(); show(ta, box.left, box.bottom); }
                });
                trigger.hidden = false;
            }
            doc.addEventListener('mousedown', function (e) { if (menu && !menu.contains(e.target) && e.target !== trigger) dismiss(false); });
            win.addEventListener('blur', function () { if (menu) dismiss(false); });
            win.addEventListener('resize', function () { dismiss(false); });
            win.u5TextareaActions = { receive: receive, show: show, undo: undo, dismiss: dismiss };
        }
        if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', init); else init();
    }
    return { validName: validName, protectedRanges: protectedRanges, references: references, at: at,
        replaceTarget: replaceTarget, replaceLabel: replaceLabel, plainLabel: plainLabel, format: format, install: install };
}));
