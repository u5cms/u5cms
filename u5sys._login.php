<?php
require_once('connect.inc.php');
require_once('globalslogin.inc.php');
if(isset($u5samlsalt)&&$u5samlsalt!='')die('<center>'.ehtml($_COOKIE['u5samlusername']).'<br><br><button id="samllogin" onclick="top.location.href=\'loginsave.php?u='.rawurlencode($_GET['u']).'\'">LOGIN</button><br><br>'.$u5samllogininfo.'</center><script>document.getElementById("samllogin").focus();if(isNaN(location.href.split("%26")[location.href.split("%26").length-1]))document.getElementById("samllogin").click();else document.write("<br<br><br><center style=color:red>Is the user '.ehtml($_COOKIE['u5samlusername']).' registered in this u5CMS backend as a backend user or inranet user or close user group member of the target page?</center>")</script>');
?>
<h1 class="logintitle"><?php echo def($logintitle_1, $logintitle_2, $logintitle_3) ?></h1>
<span class="loginintro"><?php echo def($loginintro_1, $loginintro_2, $loginintro_3) ?></span>
<form name="loginform" class="loginform" action="loginsave.php?u=<?php echo rawurlencode($_GET['u']) ?>" method="post">
    <fieldset class="loginfieldset">
        <label class="loginlabel" style="display:block"><?php echo def($username_1, $username_2, $username_3) ?></label><input type="text" name="u"/><br/>
        <label class="loginlabel" style="display:block"><?php echo def($password_1, $password_2, $password_3) ?></label><input type="password" name="p"/><br/>
        <label class="loginlabellast" style="display:block">&nbsp;</label><input type="submit" value="<?php echo def($loginbutton_1, $loginbutton_2, $loginbutton_3) ?>"/>
    </fieldset>
</form>
<span class="loginoutro"><?php echo def($loginoutro_1, $loginoutro_2, $loginoutro_3) ?></span>
<script>document.loginform.u.focus();</script>