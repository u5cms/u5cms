<?php
if(file_get_contents('fileversions/lastsave.txt')>file_get_contents('fileversions/lastindex.txt'))echo'<span style="font-size:10px">Search index and sitemap are not yet up to date, the status is '.date('Y-m-d H:i:s T',file_get_contents('fileversions/lastindex.txt')).' . The pending update will be carried out automatically later.</span>';
?>