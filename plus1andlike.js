moveris=0;
isopen=0;
oldwpx=0;
function mover() {
if (oldwpx==0 && document.getElementById('facebook')) oldwpx=document.getElementById('facebook').style.width;	
setTimeout("if (moveris==1 && document.getElementById('facebook')) document.getElementById('facebook').style.width='450px'",1111);
setTimeout("if (moveris==1 && document.getElementById('facebook')) document.getElementById('facebook').style.height='80px'",1111);
setTimeout("if (moveris==1 && document.getElementById('facebook')) document.getElementById('facebook').style.background='#eee'",1111);
setTimeout("isopen=1",1111);
}


function mout() {
setTimeout("if (moveris==0 && document.getElementById('facebook')) document.getElementById('facebook').style.width=oldwpx",1111);
setTimeout("if (moveris==0 && document.getElementById('facebook')) document.getElementById('facebook').style.height='30px'",1111);
setTimeout("if (moveris==0 && document.getElementById('facebook')) document.getElementById('facebook').style.background='#f7f7f7'",1111);
setTimeout("isopen=0",1111);
}