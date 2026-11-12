/* Optional navigation disclosure only. CSS owns all layout and media sizing. */
(function () {
    'use strict';
    var body = document.body;
    if (!body || body.getAttribute('data-u5-layout') !== 'responsive') return;
    var toggle = document.getElementById('u5-navigation-toggle');
    var navigation = document.getElementById('navigation');
    if (!toggle || !navigation || body.hasAttribute('data-u5-nav-ready')) return;
    var narrow = window.matchMedia('(max-width: 48rem)');
    var open = false;
    function sync() {
        toggle.setAttribute('aria-expanded', String(!narrow.matches || open));
    }
    function setOpen(value) {
        open = value;
        body.setAttribute('data-u5-nav-open', String(value));
        sync();
    }
    toggle.addEventListener('click', function () { setOpen(!open); });
    navigation.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && narrow.matches && open) {
            setOpen(false);
            toggle.focus();
        }
    });
    // A skip link / direct fragment to navigation must reveal the real target.
    function revealHash() {
        var id;
        try { id = decodeURIComponent(location.hash.slice(1)); } catch (_) { return; }
        var target = document.getElementById(id) || document.getElementsByName(id)[0];
        if (target && (target === navigation || navigation.contains(target))) setOpen(true);
    }
    window.addEventListener('hashchange', revealHash);
    document.addEventListener('click', function (event) {
        var link = event.target.closest && event.target.closest('a[href]');
        if (!link) return;
        var url = new URL(link.href, location.href);
        if (url.origin !== location.origin || url.pathname !== location.pathname || url.search !== location.search || !url.hash) return;
        var target;
        try { target = document.getElementById(decodeURIComponent(url.hash.slice(1))) || document.getElementsByName(decodeURIComponent(url.hash.slice(1)))[0]; } catch (_) { return; }
        if (target && (target === navigation || navigation.contains(target))) setOpen(true);
    });
    function resize() {
        // Never hide the element that currently owns keyboard focus.
        if (narrow.matches && navigation.contains(document.activeElement)) setOpen(true);
        else sync();
    }
    if (narrow.addEventListener) narrow.addEventListener('change', resize);
    else narrow.addListener(resize);
    setOpen(false);
    revealHash();
    if (navigation.contains(document.activeElement)) setOpen(true);
    body.setAttribute('data-u5-nav-ready', 'true');
    toggle.hidden = false;
}());
