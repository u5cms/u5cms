<?php
require_once('connect.inc.php');
require_once('globalslogin.inc.php');
if(isset($u5samlsalt)&&$u5samlsalt!='') {
    if ($u5samlinfrontend != 'no') die('<center>'.ehtml($_COOKIE['u5samlusername']).'<br><br><button id="samllogin" onclick="top.location.href=\'loginsave.php?u='.rawurlencode($_GET['u']).'\'">LOGIN</button><br><br>'.$u5samllogininfo.'</center><script>document.getElementById("samllogin").focus();if(isNaN(location.href.split("%26")[location.href.split("%26").length-1]))document.getElementById("samllogin").click();else document.write("<br<br><br><center style=color:red>Is the user '.ehtml($_COOKIE['u5samlusername']).' registered in this u5CMS backend as a backend user or inranet user or close user group member of the target page?</center>")</script>');
}
?>
<h1 class="logintitle"><?php echo def($logintitle_d, $logintitle_e, $logintitle_f) ?></h1>
<span class="loginintro"><?php echo def($loginintro_d, $loginintro_e, $loginintro_f) ?></span>
<form name="loginform" class="loginform" action="loginsave.php?u=<?php echo rawurlencode($_GET['u']) ?>" method="post">
    <fieldset class="loginfieldset">
        <label class="loginlabel" style="display:block"><?php echo def($username_d, $username_e, $username_f) ?></label><input type="text" name="u"/><br/>
        <label class="loginlabel" style="display:block"><?php echo def($password_d, $password_e, $password_f) ?></label><input type="password" name="p"/><br/>
        <label class="loginlabellast" style="display:block">&nbsp;</label><input type="submit" value="<?php echo def($loginbutton_d, $loginbutton_e, $loginbutton_f) ?>"/>
    </fieldset>
</form>
<span class="loginoutro"><?php echo def($loginoutro_d, $loginoutro_e, $loginoutro_f) ?></span>
<script>document.loginform.u.focus();</script>
