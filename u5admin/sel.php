<?php 
echo 'document.getElementById("sel'.$_GET['id'].'")['.($_GET['status']-1).'].selected="true"';
?>