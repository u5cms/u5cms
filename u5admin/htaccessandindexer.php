<iframe name="htaccess0" src="htaccess.php"></iframe><?php
ignore_user_abort(true);set_time_limit(3600);
if($oneverysaveupdateindexandhtaccesscostly!='yes')if(file_get_contents('../fileversions/lastsave.txt')<=file_get_contents('../fileversions/lastindex.txt'))die('<audio id="doneaudio" src="'.rand(1,6).'.mp3" autoplay /><script>var audio = document.getElementById("doneaudio");audio.volume = 0.05;</script>');
require_once ('connect.inc.php');
$k=sha1($db.$username.$password.date('YmdHi'));
?>
<script>
indexer=0;
function u5indexer() {
indexer++;
if(indexer>=5)location.reload();	
}
</script>
<div style="display:none">
<iframe src="indexer.php?l=<?php echo $lan1na ?>&r=!&k=<?php echo $k ?>"></iframe>
<iframe src="indexer.php?l=<?php echo $lan2na ?>&r=!&k=<?php echo $k ?>"></iframe>
<iframe src="indexer.php?l=<?php echo $lan3na ?>&r=!&k=<?php echo $k ?>"></iframe>
<iframe src="indexer.php?l=<?php echo $lan4na ?>&r=!&k=<?php echo $k ?>"></iframe>
<iframe src="indexer.php?l=<?php echo $lan5na ?>&r=!&k=<?php echo $k ?>"></iframe>
</div>
<?php if($oneverysaveupdateindexandhtaccesscostly!='yes' && file_get_contents('../fileversions/lastsave.txt')<=time()-60*60*3) {?>
<script>
parent.save.location.href='logoutreminder.php';
</script>
<?php } ?>