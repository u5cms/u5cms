<?php $h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$sql_a)?>
<iframe id="filter2inc" style="visibility:hidden" frameborder="0" width="0" height="0"></iframe>
<script>
// Hide empty preview rows before the filter iframe has loaded.
(function() {
var form=document.form1;
if (!form || !form.pvs_p || !form.pvs_p[1] || form.pvs_p[1].checked) return;
var rows=document.querySelectorAll('tr[id^="tr2_"]');
for (var r=0;r<rows.length;r++) rows[r].style.display='none';
})();
<?php if(file_get_contents('../fileversions/EDITORrunning.txt')<time()-7) { ?>
setTimeout("document.getElementById('filter2inc').src='filter2.inc.php?sql=<?php echo rawurlencode($sql_a) ?>&h=<?php echo $h ?>'",1);	
<?php } else { ?>
setTimeout("document.getElementById('filter2inc').src='filter2.inc.php?sql=<?php echo rawurlencode($sql_a) ?>&h=<?php echo $h ?>'",7777);	
<?php } ?>
</script>