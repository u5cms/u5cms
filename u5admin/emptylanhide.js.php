if (document.form1 && document.form1.content_d) {
if (document.form1.content_d.value.split("\n").length>3 || document.form1.content_d.value.length>100 ) {
window.moveTo(0,0);
window.resizeTo(screen.availWidth, screen.availHeight);
document.form1.content_d.style.height='200px';
document.form1.content_e.style.height='200px';
document.form1.content_f.style.height='200px';
}
}
if (document.form1 && document.form1.desc_d) {
if (document.form1.desc_d.value.split("\n").length>3 || document.form1.desc_d.value.length>100 ) {
window.moveTo(0,0);
window.resizeTo(screen.availWidth, screen.availHeight);
if (document.form1.desc_d.value.split("\n").length>1) {
document.form1.desc_d.style.height='200px';
document.form1.desc_e.style.height='200px';
document.form1.desc_f.style.height='200px';
}
}
}
<?php 
include('connect.inc.php');
if(trim($lan2name=='')) echo "if (document.getElementById('lan2name')) document.getElementById('lan2name').style.display='none';";
if(trim($lan3name=='')) echo "if (document.getElementById('lan3name')) document.getElementById('lan3name').style.display='none';";
?>