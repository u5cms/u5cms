<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
$(document).ready(function() {
  $(window).keydown(function(event){
    if(event.keyCode == 13) {
      event.preventDefault();
      return false;
    }
  });
});
grcfnstarted=0;
function grcfn() {
grcfnstarted=1;
if(document.querySelectorAll("input[type=submit]")) {
if (grecaptcha.getResponse() == ""){
document.querySelectorAll("input[type=submit]")[0].style.visibility='hidden';
if(document.getElementById('Layer1'))document.getElementById('Layer1').style.top='650px';
} 
else {
document.querySelectorAll("input[type=submit]")[0].style.visibility='visible';
document.querySelectorAll("input[type=submit]")[0].style.background='lightgreen';
document.querySelectorAll("input[type=submit]")[0].style.color='black';
}
}
setTimeout("grcfn()",1111);
}
window.onload=function(){grcfn()};
setTimeout("if(grcfnstarted==0)grcfn()",333);
</script>
<div class="g-recaptcha" data-sitekey="<?php echo $u5googlerecaptchasitekey ?>"></div>
<br>