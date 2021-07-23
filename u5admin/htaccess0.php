<?php
ignore_user_abort(true); 
require_once ('connect.inc.php');
?>
<iframe name="htaccess0" src="htaccess.php"></iframe>
<iframe name="lastsave0" src="lastsave.php"></iframe>
<?php
$k=sha1($db.$username.$password.date('YmdHi'));
?>
<script>
indexer=0;
function u5indexer() {
indexer++;
if(indexer>2)location.reload();	
}
</script>
<div style="display:none">
<iframe src="indexer.php?l=<?php echo $lan1na ?>&r=!&k=<?php echo $k ?>"></iframe>
<iframe src="indexer.php?l=<?php echo $lan2na ?>&r=!&k=<?php echo $k ?>"></iframe>
<iframe src="indexer.php?l=<?php echo $lan3na ?>&r=!&k=<?php echo $k ?>"></iframe>
</div>