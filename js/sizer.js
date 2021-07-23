contentres=216;
leftres=170;
rightres=170;
function sizer() {
if (document.documentElement.clientHeight>700) {	
if (document.getElementById('content')) document.getElementById('content').style.height=document.documentElement.clientHeight-contentres+'px';
if (document.getElementById('left')) document.getElementById('left').style.height=document.documentElement.clientHeight-leftres+'px';
if (document.getElementById('right')) document.getElementById('right').style.height=document.documentElement.clientHeight-rightres+'px';
}
}

function resizer() { 
if (document.getElementById('content') && document.getElementById('content').style.height!=document.documentElement.clientHeight-contentres) sizer();
if (document.getElementById('left') && document.getElementById('left').style.height!=document.documentElement.clientHeight-leftres) sizer();
if (document.getElementById('right') && document.getElementById('right').style.height!=document.documentElement.clientHeight-rightres) sizer();
setTimeout("resizer()",777);
}
setTimeout("resizer()",111);



