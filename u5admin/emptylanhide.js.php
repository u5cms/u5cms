<?php 
include('connect.inc.php');
if(trim($lan5name!='')) echo "window.resizeTo(800,screen.availHeight);"; 
?>
if (document.form1 && document.form1.content_1) {
if (document.form1.content_1.value.split("\n").length>3 || document.form1.content_1.value.length>100 ) {
window.moveTo(0,0);
window.resizeTo(screen.availWidth, screen.availHeight);
document.form1.content_1.style.height='200px';
document.form1.content_2.style.height='200px';
document.form1.content_3.style.height='200px';
document.form1.content_4.style.height='200px';
document.form1.content_5.style.height='200px';
}
}
if (document.form1 && document.form1.desc_1) {
if (document.form1.desc_1.value.split("\n").length>3 || document.form1.desc_1.value.length>100 ) {
window.moveTo(0,0);
window.resizeTo(screen.availWidth, screen.availHeight);
if (document.form1.desc_1.value.split("\n").length>1) {
document.form1.desc_1.style.height='200px';
document.form1.desc_2.style.height='200px';
document.form1.desc_3.style.height='200px';
document.form1.desc_4.style.height='200px';
document.form1.desc_5.style.height='200px';
}
}
}
if(document.querySelector('input[type="submit"]')&&location.href.indexOf('titlefixum.php')<0) { document.querySelector('input[type="submit"]').style.position='fixed';
document.querySelector('input[type="submit"]').style.top='1px';
}
<?php 
if(trim($lan2name=='')) echo "if (document.getElementById('lan2name')) document.getElementById('lan2name').style.display='none';";
if(trim($lan3name=='')) echo "if (document.getElementById('lan3name')) document.getElementById('lan3name').style.display='none';";
if(trim($lan4name=='')) echo "if (document.getElementById('lan4name')) document.getElementById('lan4name').style.display='none';";
if(trim($lan5name=='')) echo "if (document.getElementById('lan5name')) document.getElementById('lan5name').style.display='none';";
?>