<?php
$u5cmsscrttkngt=hash('sha512',date('Ymd').$password.$username.$_SERVER['PHP_AUTH_PW'].$_SERVER['PHP_AUTH_USER']);
?>