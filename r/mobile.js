u5cmsswitchtowebappifscreennarrowerthan = 1000;

//THIS SCRIPT CONVERTS YOUR WEBPAGE TO A WEB APP ON MOBILE DEVICES. DOCUMENTATION SEE http://yuba.ch/wa
u5cmsswitchtowebapp = false;
if ((screen.availWidth < u5cmsswitchtowebappifscreennarrowerthan && document.cookie.indexOf('u5cmsfrcthsvw=desktop') < 0) || window.location.href.indexOf('mobile=on') > 0 || document.cookie.indexOf('u5cmsfrcthsvw=mobile') > -1) u5cmsswitchtowebapp = true;

if (u5cmsswitchtowebapp && document.getElementsByTagName('html')[0]) {
	document.getElementsByTagName('html')[0].style.opacity = 0;
	setTimeout("document.getElementsByTagName('html')[0].style.opacity=1", 3333);
}

function u5mkmobile() {
	webappmobilesymbol = '&#128241;';

	if (u5cmsswitchtowebapp) {
		if (document.getElementsByName('fsearch')[0]) document.getElementsByName('fsearch')[0].style.visibility = 'hidden';
		if (document.getElementById('languages')) {
			document.getElementById('languages').style.border = 0;
			document.getElementById('languages').style.marginTop = '-22px';
			document.getElementById('languages').style.marginBottom = '-35px';
			setTimeout("if(document.getElementById('webapp_monafloat'))if(document.getElementById('webapp_monafloat').innerHTML.indexOf('search')<0)document.getElementById('webapp_monafloat').innerHTML+='<div style=margin-top:11px><a style=margin-left:11px href=?c=_search>&#128269;</a></div>'", 1111);
		}
	}

	if (u5cmsswitchtowebapp) {
		jQuery.loadScript = function(url, callback) {
			jQuery.ajax({
				url: url,
				dataType: 'script',
				success: callback,
				async: true
			});
		}
		if (typeof jsmobilegeneralloaded == 'undefined') jQuery.loadScript('r/mobilebase.js?u5v=121100', function() {

			//START SITE SPECIFIC CODE TO CREATE A WEB APP. IF YOU CHANGE THE HTML TEMPLATE MAY BE YOU HAVE TO MAKE CHANGES HERE TOO.

			frombtmwebappnavlinkscrollto = 100;
			webappdesktopsymbol = '&#128187;';

			if (document.getElementById('clickhome')) document.getElementById('clickhome').style.width = 'auto';

			if (document.getElementById('webapp_mona')) {
				document.getElementById('webapp_mona').style.margin = '-12px 0 70px 10px';
				document.getElementById('webapp_mona').style.fontSize = '120%';
			}
			if (document.getElementById('webapp_monalink')) {
				document.getElementById('webapp_monalink').style.marginTop = '10px';
			}
			if (document.getElementById('webapp_monalinka')) {
				document.getElementById('webapp_monalinka').style.background = '#fafafa';
				document.getElementById('webapp_monalinka').style.display = 'block';
				document.getElementById('webapp_monalinka').style.padding = '3px';
			}
			if (document.getElementById('webapp_monaclose')) {
				document.getElementById('webapp_monaclose').style.background = '#e63320';
			}
			if (document.getElementById('webapp_monalinkbtm')) {
				document.getElementById('webapp_monalinkbtm').style.padding = '20px 0  20px 0';
			}
			if (document.getElementById('webapp_monalinkabtm')) {
				document.getElementById('webapp_monalinkabtm').style.background = '#fafafa';
				document.getElementById('webapp_monalinkabtm').style.display = 'block';
				document.getElementById('webapp_monalinkabtm').style.padding = '3px';
			}
			if (document.getElementById('navigation')) {
				document.getElementById('navigation').style.display = 'none';
			}
			if (document.getElementById('main')) {
				document.getElementById('main').style.width = '100%';
				document.getElementById('main').style.boxShadow = 'none';
			}
			if (document.getElementById('header')) {
				document.getElementById('header').style.borderRadius = 0;
				document.getElementById('header').style.boxShadow = 'none';
				document.getElementById('header').style.width = '100%';
				document.getElementById('header').style.height = '90px';
			}
			if (document.getElementById('bit')) {
				//document.getElementById('bit').style.position = 'absolute';
				document.getElementById('bit').style.marginLeft = '10%';
				//document.getElementById('bit').style.top = '155px';
			}
			if (document.getElementById('vline1')) {
				document.getElementById('vline1').style.display = 'none';
			}
			if (document.getElementById('vline2')) {
				document.getElementById('vline2').style.display = 'none';
			}

			if (document.getElementById('outer')) {
				document.getElementById('outer').style.margin = 0;
				document.getElementById('outer').style.padding = 0;
				document.getElementById('outer').style.width = '100%';
				document.getElementById('outer').style.boxShadow = 'none';
			}
			if (document.getElementById('content')) {
				var content = document.getElementById('content');
				content.style.minHeight = '100px';
				content.style.borderRight = 0;
				content.style.padding = 0;
				content.style.margin = 0;
				if (document.getElementById('checkboxlang')) content.style.marginTop = '111px';
				content.style.width = '100%';

				var contentinner = document.getElementById('contentinner');
				if (!contentinner) {
					contentinner = document.createElement('div');
					contentinner.id = 'contentinner';

					while (content.firstChild) {
						contentinner.appendChild(content.firstChild);
					}

					content.appendChild(contentinner);
				}

				contentinner.style.padding = '7px';
				content.style.height = contentinner.style.height;
			}
			if (document.getElementById('news')) {
				document.getElementById('news').style.background = '#fafafa';
				document.getElementById('news').style.margin = '0px';
				document.getElementById('news').style.padding = '3px';
				document.getElementById('news').style.width = '100%';
			}
			if (document.getElementById('footer')) {
				document.getElementById('footer').style.padding = 0;
				document.getElementById('footer').style.margin = 0;
				document.getElementById('footer').style.width = '100%';
				document.getElementById('footer').style.lineHeight = '180%';
				document.getElementById('footer').innerHTML = '<div id="footerinner">' + document.getElementById('footer').innerHTML + '</div>';
			}
			if (document.getElementById('footerinner')) {
				document.getElementById('footerinner').style.padding = '20px';
			}
			if (document.getElementById('metanavi')) {
				document.getElementById('metanavi').style.marginRight = '-50px';
				document.getElementById('metanavi').style.marginTop = '11px';
			}
			e = document.getElementById('webapp_mona').getElementsByTagName('ul');
			for (i = 0; i < e.length; i++) {
				e[i].style.width = Math.floor(document.getElementById('content').clientWidth * 0.8) + 'px';
			}

			e = document.getElementById('header').getElementsByTagName('img');
			for (i = 0; i < e.length; i++) {
				e[i].style.width = Math.floor(document.getElementById('content').clientWidth * 0.4) + 'px';
			}

			e = document.getElementById('content').getElementsByTagName('fieldset');
			for (i = 0; i < e.length; i++) {
				e[i].style.width = '91%';
			}

			//END SITE SPECIFIC CODE TO CREATE A WEB APP
			if (document.getElementsByTagName('html')[0]) {
				setTimeout("window.scrollTo(0,7)", 1);
				document.getElementsByTagName('html')[0].style.opacity = 1;
			}
		});
	}
	if (document.cookie.indexOf('u5cmsfrcthsvw=desktop') > -1) document.getElementById('footer').innerHTML += ('<div id="webapp_monadesktop" style="position:fixed;top:100px;left:-30px;z-index:9999999;cursor:pointer;font-size:100px" onclick="document.cookie=\'u5cmsfrcthsvw=mobile;\';location.href=location.href.replace(/mobile=off/,\'\')">' + webappmobilesymbol + '</div>');

}

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
            '#webapp_mona.u5-mobile-tools-menu{padding-left:44px!important}',
            '#webapp_monafloat.u5-mobile-tools{left:3px!important;width:50px!important;display:flex!important;flex-direction:column!important;align-items:center!important;gap:8px!important}',
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
            menu.classList.add('u5-mobile-tools-menu');
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
