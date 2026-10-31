<?php
/* ASCII source also survives the legacy Windows-1252 / UTF-8 database boundary. */
function u5MobileToolsSource() {
    return <<<'U5_MOBILE_TOOLS'
/* u5CMS mobile controls enhancement 2026-10-31 */
;(function () {
    'use strict';
    if (window.u5MobileToolsInstalled || window.u5cmsswitchtowebapp !== true) return;
    window.u5MobileToolsInstalled = true;
    var styleId = 'u5-mobile-tools-enhancement-style';
    var labels = {
        de: ['Seitenwerkzeuge', 'Navigation schliessen', 'Desktop-Ansicht anzeigen', 'Website durchsuchen'],
        fr: ['Outils de la page', 'Fermer la navigation', 'Afficher la version pour ordinateur', 'Rechercher sur le site'],
        it: ['Strumenti della pagina', 'Chiudi la navigazione', 'Mostra la versione desktop', 'Cerca nel sito'],
        rm: ['Utensils da la pagina', 'Serrar la navigaziun', 'Mussar la versiun desktop', 'Tschertgar sin la pagina'],
        en: ['Page tools', 'Close navigation', 'Show desktop view', 'Search website']
    };
    var observer, menu;
    function language() {
        var lang = (document.documentElement.lang || '').toLowerCase().split('-')[0];
        if (!labels[lang]) {
            var match = location.pathname.toLowerCase().match(/^\/(de|fr|it|rm|en)(?:\/|$)/);
            lang = match ? match[1] : 'de';
        }
        return labels[lang];
    }
    function styles() {
        if (document.getElementById(styleId)) return;
        var style = document.createElement('style');
        style.id = styleId;
        style.textContent = [
            '#webapp_monafloat.u5-mobile-tools{width:50px!important;display:flex!important;flex-direction:column!important;align-items:center!important;gap:8px!important}',
            '#webapp_monafloat .u5-mobile-tool-wrap{width:44px!important;height:44px!important;margin:0!important;padding:0!important}',
            '#webapp_monafloat .u5-mobile-tool{box-sizing:border-box!important;width:44px!important;height:44px!important;margin:0!important;padding:0!important;display:flex!important;align-items:center!important;justify-content:center!important;overflow:visible!important;border:1px solid #d7d7d7!important;border-radius:9px!important;background:#fff!important;color:#222!important;box-shadow:0 1px 3px rgba(0,0,0,.12)!important;text-decoration:none!important;font-size:0!important;line-height:1!important;cursor:pointer!important;touch-action:manipulation;-webkit-tap-highlight-color:transparent}',
            '#webapp_monafloat .u5-mobile-tool:hover{background:#f4f4f4!important;border-color:#c8c8c8!important}',
            '#webapp_monafloat .u5-mobile-tool:focus-visible{outline:3px solid #1a73e8!important;outline-offset:2px!important}',
            '#webapp_monafloat #webapp_monaclose.u5-mobile-tool{background:#e63320!important;border-color:#e63320!important;color:#fff!important}',
            '#webapp_monafloat #webapp_monaclose.u5-mobile-tool:hover{background:#c92c1c!important;border-color:#c92c1c!important}',
            '#webapp_monafloat .u5-mobile-tool svg{width:22px;height:22px;display:block;pointer-events:none}'
        ].join('\n');
        (document.head || document.documentElement).appendChild(style);
    }
    function icon(element, type) {
        if (element.getAttribute('data-u5-mobile-icon') === type && element.querySelector('svg')) return;
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        var attributes = {'viewBox':'0 0 24 24','aria-hidden':'true','focusable':'false','fill':'none','stroke':'currentColor','stroke-width':'2','stroke-linecap':'round','stroke-linejoin':'round'};
        Object.keys(attributes).forEach(function (name) { svg.setAttribute(name, attributes[name]); });
        var paths = type === 'close' ? ['M6 6l12 12','M18 6L6 18'] : type === 'desktop' ? ['M5 4h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z','M8 21h8','M12 17v4'] : ['M17.5 11a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z','M16 16l4.5 4.5'];
        paths.forEach(function (d) {
            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d); svg.appendChild(path);
        });
        while (element.firstChild) element.removeChild(element.firstChild);
        element.appendChild(svg); element.setAttribute('data-u5-mobile-icon', type);
    }
    function enhance() {
        var bar = document.getElementById('webapp_monafloat');
        if (!bar) return;
        styles();
        var text = language();
        bar.classList.add('u5-mobile-tools'); bar.setAttribute('role', 'group'); bar.setAttribute('aria-label', text[0]);
        var controls = [bar.querySelector('#webapp_monaclose'), bar.querySelector('#webapp_monadesktop'), bar.querySelector('a[href*="c=_search"]')];
        controls.forEach(function (control, index) {
            if (!control) return;
            control.classList.add('u5-mobile-tool');
            control.setAttribute('aria-label', text[index + 1]); control.setAttribute('title', text[index + 1]);
            if (index < 2) {
                control.setAttribute('role', 'button'); control.setAttribute('tabindex', '0');
            }
            if (index === 0) control.setAttribute('aria-controls', 'webapp_mona');
            if (index === 2 && control.parentElement !== bar) control.parentElement.classList.add('u5-mobile-tool-wrap');
            icon(control, ['close','desktop','search'][index]);
        });
    }
    function watch() {
        if (document.getElementById('nevermobile')) return;
        var current = document.getElementById('webapp_mona');
        if (current && current !== menu) {
            menu = current;
            observer.disconnect();
            // Only the navigation is observed once available; page content is not rescanned.
            observer.observe(menu, {childList:true, subtree:true});
        }
        enhance();
    }
    function start() {
        if (document.getElementById('nevermobile')) return;
        observer = new MutationObserver(watch);
        // The common script is asynchronous and may arrive after the page's load event.
        observer.observe(document.documentElement, {childList:true, subtree:true});
        watch();
    }
    document.addEventListener('keydown', function (event) {
        var target = event.target;
        if (event.defaultPrevented || event.repeat || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
        if (!target || !target.matches('#webapp_monafloat #webapp_monaclose, #webapp_monafloat #webapp_monadesktop')) return;
        if (event.key !== 'Enter' && event.key !== ' ') return;
        event.preventDefault(); target.click();
    });
    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.closest || !event.target.closest('#webapp_monafloat #webapp_monaclose')) return;
        window.setTimeout(function () {
            var opener = document.getElementById('webapp_monalinka');
            if (opener && typeof opener.focus === 'function') opener.focus();
        }, 0);
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once:true});
    else start();
}());
U5_MOBILE_TOOLS;
}

function u5MobileToolsAppend($source) {
    // A site with the original info.agov.ch addendum is already enhanced.
    if (strpos($source,'u5-mobile-tools-enhancement-style')!==false) return $source;
    $newline=strpos($source,"\r\n")!==false ? "\r\n" : "\n";
    return $source.$newline.str_replace("\n",$newline,u5MobileToolsSource()).$newline;
}
