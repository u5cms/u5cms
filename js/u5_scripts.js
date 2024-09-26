if(window.name=='fu5prvldd')setTimeout("location.href=location.href",2222);

//Script for [?:][:?] and [??:][:??]
oldsbthis=false;
function showblockscript(sbthis) {
if (sbthis.className=='showblockinactivetab') {
	sbthis.className='showblockactivetab';
	sbthis.nextSibling.style.display='block';
	if (oldsbthis && oldsbthis!=sbthis) {
	oldsbthis.className='showblockinactivetab';
    oldsbthis.nextSibling.style.display='none';
	}
}
else {
	sbthis.className='showblockinactivetab';
	sbthis.nextSibling.style.display='none';	
}
oldsbthis=sbthis;
}

function showblock2script(sbthis2) {
if (sbthis2.className=='showblock2inactivetab') {
	sbthis2.className='showblock2activetab';
	sbthis2.nextSibling.style.display='block';
}
else {
	sbthis2.className='showblock2inactivetab';
	sbthis2.nextSibling.style.display='none';	
}
}

function u5fancyboxunescape(text) {
text=text.replace(/%80/g,'%u20AC');
text=text.replace(/%82/g,'%u201A');
text=text.replace(/%83/g,'%u0192');
text=text.replace(/%84/g,'%u201E');
text=text.replace(/%85/g,'%u2026');
text=text.replace(/%86/g,'%u2020');
text=text.replace(/%87/g,'%u2021');
text=text.replace(/%88/g,'%u02C6');
text=text.replace(/%89/g,'%u2030');
text=text.replace(/%8A/g,'%u0160');
text=text.replace(/%8B/g,'%u2039');
text=text.replace(/%8C/g,'%u0152');
text=text.replace(/%8E/g,'%u017D');
text=text.replace(/%91/g,'%u2018');
text=text.replace(/%92/g,'%u2019');
text=text.replace(/%93/g,'%u201C');
text=text.replace(/%94/g,'%u201D');
text=text.replace(/%95/g,'%u2022');
text=text.replace(/%96/g,'%u2013');
text=text.replace(/%97/g,'%u2014');
text=text.replace(/%98/g,'%u02DC');
text=text.replace(/%99/g,'%u2122');
text=text.replace(/%9A/g,'%u0161');
text=text.replace(/%9B/g,'%u203A');
text=text.replace(/%9C/g,'%u0153');
text=text.replace(/%9E/g,'%u017E');
text=text.replace(/%9F/g,'%u0178');
return unescape(text);
}

function resizeIframe(obj) {
obj.style.height = (obj.contentWindow.document.body.scrollHeight-0+20) + 'px';
}

function abbroracro(that) {
that.name=that.innerHTML.replace(/</g,'&lt;').replace(/>/g,'&gt;');that.innerHTML=that.title.replace(/</g,'&lt;').replace(/>/g,'&gt;');that.title=that.name.replace(/</g,'&lt;').replace(/>/g,'&gt;');
}