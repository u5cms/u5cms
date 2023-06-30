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
<iframe src="indexer.php?l=<?php echo $lan4na ?>&r=!&k=<?php echo $k ?>"></iframe>
<iframe src="indexer.php?l=<?php echo $lan5na ?>&r=!&k=<?php echo $k ?>"></iframe>
</div>