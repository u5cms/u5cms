<script>
function getcheckstring() {
e1=document.getElementsByTagName('input');
e2=document.getElementsByTagName('textarea');	
e3=document.getElementsByTagName('select');	
checkstring='';
for(i=0;i<e1.length;i++) {
if(e1[i].name!='u5scrtytknfrfrms')checkstring+=e1[i].value;	
}
for(i=0;i<e2.length;i++) {
if(e2[i].name!='u5scrtytknfrfrms')checkstring+=e2[i].value;	
}
for(i=0;i<e3.length;i++) {
if(e3[i].name!='u5scrtytknfrfrms')checkstring+=e3[i].value;	
}
}

function initchanges() {
getcheckstring();
checkstringold=checkstring;	
getchanges();
}

function getchanges() {
getcheckstring();
if(checkstring!=checkstringold){
changes();
checkstringold=checkstring;
}
setTimeout("getchanges()",777);
}

cchanges=0;
function changes() {
cchanges++;	
if(cchanges>0) {
if(document.getElementsByClassName('asterisk')[0]){
document.getElementsByClassName('asterisk')[0].style.display='inline';
document.getElementsByClassName('asterisk')[0].style.marginLeft='-8px';
}
if(document.getElementsByClassName('asterisk')[1]){
document.getElementsByClassName('asterisk')[1].style.display='inline';
document.getElementsByClassName('asterisk')[1].style.marginLeft='-8px';
}
}
}
window.onbeforeunload = function() {
if (cchanges>0) {self.focus();return('You have unsaved changes which will be lost if you are proceeding.');}
}

function cchangesreset() {
cchanges=0;
if(document.getElementsByClassName('asterisk')[0]){
document.getElementsByClassName('asterisk')[0].style.display='none';
}
if(document.getElementsByClassName('asterisk')[1]){
document.getElementsByClassName('asterisk')[1].style.display='none';
}
	
}
</script>
