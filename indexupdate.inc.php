<?php
if(file_get_contents('fileversions/lastsave.txt')>file_get_contents('fileversions/lastindex.txt'))echo'<div lang="en" style="font-size:10px;line-height:177%;margin-top:12px">Search index &amp; sitemap are not yet fully up to date. A few entries are still as of '.date('Y-m-d H:i T',file_get_contents('fileversions/lastindex.txt')).'. Auto-update will run soon.</div>';
?>