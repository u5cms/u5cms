if(location.href.indexOf('mobile=on')>0)document.cookie='u5cmsfrcthsvw=mobile;';
if(location.href.indexOf('mobile=off')>0)document.cookie='u5cmsfrcthsvw=desktop;';

var jsmobilegeneralloaded=true;
if(typeof webappdesktopsymbol=='undefined') webappdesktopsymbol='&#128187;';
if(typeof webappmobilesymbol=='undefined') webappmobilesymbol='&#128241;';

document.getElementById('body').style.maxWidth='600px';
document.getElementById('body').style.margin='auto';

document.getElementById('content').insertAdjacentHTML('afterbegin','<center><div id="webapp_monalink"><a style="text-decoration:none" id="webapp_monalinka" href="javascript:void(0)" onclick="this.style.display=\'none\';document.getElementById(\'webapp_mona\').style.display=\'block\'">&#9776;&nbsp;Navigation</a></div><div id="webapp_mona" style="display:none;text-align:left;padding:0 17px 0 17px"></div></center>');

if(document.getElementById('navTop'))document.getElementById('navTop').style.display='none';
if(document.getElementById('navLeftSubTop'))document.getElementById('navLeftSubTop').id='navLeft';

if(document.getElementById('webapp_mona'))if(document.getElementById('navLeft'))document.getElementById('webapp_mona').innerHTML=document.getElementById('navLeft').innerHTML;
if(document.getElementById('webapp_monalinka').style.display='block')wbppamnlnksts='block';
else wbppamnlnksts='inline';
wamonafloat='<div id="webapp_monafloat" style="width:50px;position:absolute;left:-10px"><div id="webapp_monaclose" style="width:22px;overflow:hidden;color:white;background:#e63320;cursor:pointer;padding:3px 7px 7px 7px;font-size:150%;margin-bottom:10px;" onclick="document.getElementById(\'webapp_mona\').style.display=\'none\';document.getElementById(\'webapp_monalinka\').style.display=wbppamnlnksts">&nbsp;&times;&nbsp;</div><div id="webapp_monadesktop" style="width:36px;overflow:hidden;background:transparent;padding:3px 0 3px 0;cursor:pointer;font-size:36px" onclick="document.cookie=\'u5cmsfrcthsvw=desktop;\';location.href=location.href.replace(/mobile=on/,\'\')">'+webappdesktopsymbol+'</div></div>';
document.getElementById('webapp_mona').innerHTML=wamonafloat+'<div style="margin:0;padding:0" id="navLeft">'+document.getElementById('webapp_mona').innerHTML+'</div>';
document.getElementById('navLeft').style.width='100%';
document.getElementById('navLeft').style.fontSize='80%';
document.getElementById('navLeft').style.lineHeight='90%';
document.getElementById('navLeft').style.lineHeight='90%';

e=document.getElementById('content').getElementsByTagName('table');
for(i=0;i<e.length;i++) {
if(e[i].clientWidth>400) {
e[i].style.width='100%';
}
}

e=document.getElementsByTagName('iframe');
for(i=0;i<e.length;i++) {
if(e[i].width>200) {
e[i].width='100%';
if(document.getElementById('content'))e[i].height=Math.floor(document.getElementById('content').clientWidth*0.3);
}
}

e=document.getElementsByTagName('dl');
for(i=0;i<e.length;i++) {
if(e[i].style.width.replace(/px/,'')>200) {
e[i].style.width='none';
e[i].style.maxWidth='100%';
}
}

e=document.getElementsByTagName('audio');
for(i=0;i<e.length;i++) {
if(e[i].style.width.replace(/px/,'')>200) {
e[i].style.width='100%';
}
}

e=document.getElementsByTagName('video');
for(i=0;i<e.length;i++) {
if(e[i].style.width.replace(/px/,'')>200) {
e[i].style.width='100%';
e[i].style.height=e[i].style.width;
}
}

e=document.getElementsByTagName('div');
for(i=0;i<e.length;i++) {
if(e[i].innerHTML.indexOf('albumhorizontal')>0) {
e[i].style.width='90%';
e[i].style.height='100px';
}
}

e=document.getElementsByTagName('div');
for(i=0;i<e.length;i++) {
if(e[i].innerHTML.indexOf('albumhorizontal')>0 && e[i].style.overflowY=='hidden') {
e[i].style.width='100%';
}
}

e=document.getElementsByTagName('form');
for(i=0;i<e.length;i++) {
e[i].style.width='100%';
}

e=document.getElementsByTagName('fieldset');
for(i=0;i<e.length;i++) {
e[i].style.width='87%';
}

e=document.getElementsByTagName('label');
for(i=0;i<e.length;i++) {
e[i].style.width='100%';
e[i].style.marginBottom='-7px';
e[i].style.marginTop='-10px';
e[i].style.border='none';
}

e=document.getElementsByTagName('input');
for(i=0;i<e.length;i++) {
if(e[i].id!='search_Input') {
if(e[i].clientWidth>100){
if(e[i].type=='text')e[i].style.width='100%';
if(e[i].type=='email')e[i].style.width='100%';
}
}
}

e=document.getElementsByTagName('select');
for(i=0;i<e.length;i++) {
if(e[i].clientWidth>100)e[i].style.width='100%';
}

e=document.getElementsByTagName('textarea');
for(i=0;i<e.length;i++) {
e[i].style.width='100%';
}

e=document.getElementsByClassName('showblockdivinner');
for(i=0;i<e.length;i++) {
e[i].style.width='inherit';
}

e=document.getElementsByClassName('showblock2divinner');
for(i=0;i<e.length;i++) {
e[i].style.width='inherit';
}
e=document.getElementsByClassName('stayright');
for(i=0;i<e.length;i++) {
e[i].style.marginLeft='0';
}
e=document.getElementsByClassName('imgBoxLeft');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByClassName('imgBoxRight');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByClassName('freeImgLeft');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByClassName('freeImgRight');
for(i=0;i<e.length;i++) {
//e[i].style.float='none';
}

e=document.getElementsByTagName('img');
for(i=0;i<e.length;i++) {
if(e[i].width>290) {
e[i].style.width='100%';
}
}

e=document.getElementsByClassName('albumhorizontal');
for(i=0;i<e.length;i++) {
e[i].innerHTML=e[i].innerHTML.replace(/class=/g,'style="display:block;width:100px;height:100px;overflow:hidden" class=');
e[i].innerHTML=e[i].innerHTML.replace(/img height/g,'img style="height:100px;width:auto"');
}

frombtmwebappnavlinkscrollto=0;
document.getElementById('content').insertAdjacentHTML('beforeend','<center><div id="webapp_monalinkbtm"><a id="webapp_monalinkabtm" style="text-decoration:none" href="javascript:void(0)" onclick="window.scroll(0,frombtmwebappnavlinkscrollto);document.getElementById(\'webapp_monalinka\').click()">&#9776;&nbsp;Navigation</a></div></center>');

e=document.getElementsByTagName('button');
for(i=0;i<e.length;i++) {
ftriggerid=e[i].id
if(e[i].id.indexOf('f_trigger_b')===0)setTimeout("window[ftriggerid]();",111);
}

e=document.getElementsByTagName('iframe');
for(i=0;i<e.length;i++) {
if(e[i].name=='ifrmonofill') {
locationhref=location.href;if(locationhref.indexOf('c=')<0) locationhref+='&c=MF_NOT_ALLOWED_ON_YOUR_MAIN_PAGE';if(locationhref.indexOf('id=')<0) locationhref+='&id=0';e[i].src='monofill.php?n='+locationhref.split('c=')[1].split('&')[0]+'&id='+locationhref.split('id=')[1].split('&')[0];
}
if(e[i].name=='ifrmonofillshared') {
locationhref=location.href;if(locationhref.indexOf('c=')<0) locationhref+='&c=MF_NOT_ALLOWED_ON_YOUR_MAIN_PAGE';if(locationhref.indexOf('id=')<0) locationhref+='&id=0';e[i].src='monofillshared.php?n='+locationhref.split('c=')[1].split('&')[0]+'&id='+locationhref.split('id=')[1].split('&')[0];
}
}

document.getElementsByTagName('html')[0].style.overflowX='hidden';
document.getElementsByTagName('body')[0].style.overflowX='hidden';
