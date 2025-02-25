<?php
if(!isset($_COOKIE['p'])) {
if(!isset($waitsecondstillindexingsincelastsave))$waitsecondstillindexingsincelastsave=60*60*0.5;
if(file_get_contents('fileversions/lastsave.txt')>file_get_contents('fileversions/lasthtaccess.txt') && !(file_exists('fileversions/htarunning.txt') && file_get_contents('fileversions/htarunning.txt')!=0 && file_get_contents('fileversions/htarunning.txt')>time()-60*15))echo'<script>if(!opener&&top==self)document.write(\'<iframe style="display:none" src="htaccess.php"></iframe>\')</script>'; 

else if(file_get_contents('fileversions/lastsave.txt')>file_get_contents('fileversions/lastindex.txt') && !(file_exists('fileversions/indexerrunning.txt') && file_get_contents('fileversions/indexerrunning.txt')!=0 && file_get_contents('fileversions/indexerrunning.txt')>time()-60*15) && file_get_contents('fileversions/lastsave.txt')<time()-$waitsecondstillindexingsincelastsave)echo'<script>if(!opener&&top==self)document.write(\'<iframe style="display:none" src="indexer.php"></iframe>\')</script>'; 
}
?>
