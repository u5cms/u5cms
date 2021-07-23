<?php 
if ($_COOKIE['subs']=='s2') header("Location: s2.php");
else if ($_COOKIE['subs']=='s3') header("Location: c.php");
else header("Location: s1.php");
?>