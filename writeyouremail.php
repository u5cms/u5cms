<script>
function writeyouremail() {
if (parent.document.getElementById('youremail_mandatory') && parent.document.getElementById('youremail_mandatory').value.indexOf('@')<1) {

parent.document.getElementById('youremail_mandatory').value='<?php echo rawurlencode($_COOKIE['youremail']) ?>'; 

parent.document.getElementById('youremail_mandatory').value=unescape(parent.document.getElementById('youremail_mandatory').value);

}

}
setTimeout("writeyouremail()",333);
</script>