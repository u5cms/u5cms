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
		if (typeof jsmobilegeneralloaded == 'undefined') jQuery.loadScript('https://yuba.ch/r/jsmobilegeneral.css', function() {

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
				document.getElementById('content').style.minHeight = '100px';
				document.getElementById('content').style.borderRight = 0;
				document.getElementById('content').style.padding = 0;
				document.getElementById('content').style.margin = 0;
				if (document.getElementById('checkboxlang')) document.getElementById('content').style.marginTop = '111px';
				document.getElementById('content').style.width = '100%';
				document.getElementById('content').innerHTML = '<div id="contentinner">' + document.getElementById('content').innerHTML + '</div>';
				if (document.getElementById('contentinner')) {
					document.getElementById('contentinner').style.padding = '7px';
					document.getElementById('content').style.height = document.getElementById('contentinner').style.height;
				}
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