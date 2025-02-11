<iframe id="owrite" src="orphan.write.php"></iframe>
<iframe id="ojs" src="orphan.js.php"></iframe>
<script>
setTimeout("document.getElementById('owrite').src='blank.php'",777);
function orphan() {
if(top.location.href.indexOf('/snippets')>0)document.getElementById('ojs').src='orphan.js.php';
else if(parent.document.getElementsByName('pvs_f')[1].checked)document.getElementById('ojs').src='orphan.js.php';

setTimeout("orphan()",1111);
}
setTimeout("orphan()",333);
</script>