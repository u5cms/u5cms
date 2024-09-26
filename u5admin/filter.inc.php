<?php $h=hash('sha512',$username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$sql_a)?>
<iframe id="filter2inc" style="visibility:hidden" frameborder="0" width="0" height="0"></iframe>
<script>
<?php if(file_get_contents('../fileversions/EDITORrunning.txt')<time()-7) { ?>
setTimeout("document.getElementById('filter2inc').src='filter2.inc.php?sql=<?php echo rawurlencode($sql_a) ?>&h=<?php echo $h ?>'",1);	
<?php } else { ?>
setTimeout("document.getElementById('filter2inc').src='filter2.inc.php?sql=<?php echo rawurlencode($sql_a) ?>&h=<?php echo $h ?>'",7777);	
<?php } ?>
</script>