<?php
if(file_get_contents('fileversions/lastsave.txt')>file_get_contents('fileversions/lastindex.txt'))echo'<span lang="en" style="font-size:10px">Search index and sitemap are not yet fully updated, certain entries are as of '.date('Y-m-d H:i:s T',file_get_contents('fileversions/lastindex.txt')).'. The pending update will be carried out automatically later.</span>';
?>