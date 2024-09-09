<?php $h=sha1($username.$password.$_SERVER['PHP_AUTH_USER'].$_SERVER['PHP_AUTH_PW'].$sql_a)?>
<iframe id="filter2inc" style="visibility:hidden" frameborder="0" width="0" height="0"></iframe>
<script>
setTimeout("document.getElementById('filter2inc').src='filter2.inc.php?sql=<?php echo rawurlencode($sql_a) ?>&h=<?php echo $h ?>'",3333);	
</script>