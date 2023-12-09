<?php
if($_GET['h']!=sha1(date('Ymd').$password.$username.$_SERVER['PHP_AUTH_PW'].$_SERVER['PHP_AUTH_USER']))die('<script>alert("The This transaction could not be executed, please try again!");top.location.href=top.location.href</script>') ;
?>