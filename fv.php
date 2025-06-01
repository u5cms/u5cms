<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body bgcolor="#FF0000">
<script>
//parent.document.getElementsByTagName('iframe')[0].style.height='200px';
//parent.document.getElementsByTagName('iframe')[0].style.width='1200px';

function firstsaverwins() {
if(parent)if(parent.u5form)if(parent.u5form.firstsaverwins)parent.u5form.firstsaverwins.value='<?php echo time()?>';
}

function werror(werr) {
if(parent)if(parent.document.getElementsByTagName('body')[0].innerHTML.indexOf(werr)<1)parent.document.getElementsByTagName('body')[0].innerHTML='<font color=red>'+werr+'</font><hr>'+parent.document.getElementsByTagName('body')[0].innerHTML;
setTimeout("parent.window.scrollTo(0,0)",777);
}

function valu5form() {
firstsaverwins();
Vu5form = parent.document.querySelectorAll('[name="u5form"]').length;
if(Vu5form>1) werror('FATAL FORM ERROR: You must use <b>[fo:] ... [:fo]</b> only once per page!');

Vu5felems = parent.document.u5form.getElementsByTagName("*");

var Vnames=new Array();
for(i=0;i<Vu5felems.length;i++) {

if((Vu5felems[i].name)!==undefined && (Vu5felems[i].name)!='') {

//Check radio
if(Vu5felems[i]) if(Vu5felems[i].name) if(Vu5felems[i].type) if(Vu5felems[i].name.toLowerCase().indexOf('_mandatory')>0 && Vu5felems[i].type.toLowerCase()=='radio') werror('FORM ERROR: The radio <b>'+Vu5felems[i].name+'</b> contains _mandatory! Never declare radio buttons as _mandatory. Please use select tags instead of radio buttons.');

//Check name uniqueness
if(Vnames.indexOf(Vu5felems[i].name.toLowerCase())>-1 && Vu5felems[i].type.toLowerCase()!='radio') werror('FATAL FORM ERROR: The form element name <b>'+Vu5felems[i].name+'</b> is used more than once!');
Vnames.push(Vu5felems[i].name.toLowerCase());

//Check valid characters in names
Vcheckname=Vu5felems[i].name.replace(/[^A-Za-z_0-9]/, '');
if(Vcheckname!=Vu5felems[i].name) werror('FORM ERROR: The form element name <b>'+Vu5felems[i].name+'</b> contains forbidden characters! Only use latin alphabetic letters (without any diacritics), numeric characters and the underscore.');

Vcheckname=Vu5felems[i].name[0].replace(/[^A-Za-z]/, '');
if(Vcheckname!=Vu5felems[i].name[0]) werror('FORM ERROR: The first character of the form element name <b>'+Vu5felems[i].name+'</b> must be a latin alphabetic letter (without diacritics, no numerics)!');

}
}

}
setTimeout("firstsaverwins();",111);
setTimeout("firstsaverwins();",11);
setTimeout("firstsaverwins();",1);
setTimeout("valu5form()",777);
</script>
<iframe style="display:none" src="nameattributes.php"></iframe>
</body>
</html> 
