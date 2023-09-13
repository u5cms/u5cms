pvileft=270;pvitop=180;

scrollstopped=0;
function stoppscroll() {
 if( typeof( window.pageYOffset ) == 'number' ) {
     //Netscape compliant
     vscroll = window.pageYOffset;
     hscroll = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
     //DOM compliant
     vscroll = document.body.scrollTop;
     hscroll = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
     //IE6 standards compliant mode
     vscroll = document.documentElement.scrollTop;
     hscroll = document.documentElement.scrollLeft;
 }
if (vscroll==pvitop) scrollstopped=1;
else setTimeout("stoppscroll()",333); 
}
setTimeout("stoppscroll()",333); 

setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",1000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",2000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",3000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",4000);
//setTimeout("if (scrollstopped==0) window.scroll(pvileft,pvitop)",5000);
