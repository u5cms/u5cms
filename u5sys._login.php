<?php
require_once('connect.inc.php');
require_once('globalslogin.inc.php');
if(isset($u5samlsalt)&&$u5samlsalt!='') {
    if ($u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain!='' && str_replace($u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain,'',$_GET['u'])==$_GET['u']) $u5samlinfrontend='yes';
   if ($u5samlinfrontend != 'no') die('<center>'.ehtml($_COOKIE['u5samlusername']).'<br><br><button id="samllogin" onclick="top.location.href=\'loginsave.php?u='.rawurlencode($_GET['u']).'\'">LOGIN</button><br><br>'.$u5samllogininfo.'</center><script>document.getElementById("samllogin").focus();if(isNaN(location.href.split("%26")[location.href.split("%26").length-1])&&location.href.split("%3Bamp").length<7)document.getElementById("samllogin").click();else document.write("<br<br><br><center style=color:red>Is the user '.ehtml($_COOKIE['u5samlusername']).' registered in this u5CMS backend as a backend user or inranet user or closed user group member of the target page?</center>")</script>');
}
?>
<h1 class="logintitle"><?php echo def($logintitle_1, $logintitle_2, $logintitle_3, $logintitle_4, $logintitle_5) ?></h1>
<span class="loginintro"><?php echo def($loginintro_1, $loginintro_2, $loginintro_3, $logintitle_4, $logintitle_5) ?></span>
<form name="loginform" class="loginform" action="loginsave.php?u=<?php echo rawurlencode($_GET['u']) ?>" method="post">
    <fieldset class="loginfieldset">
        <label class="loginlabel" style="display:block"><?php echo def($username_1, $username_2, $username_3, $username_4, $username_5) ?></label><input type="text" name="u"/><br/>
        <label class="loginlabel" style="display:block"><?php echo def($password_1, $password_2, $password_3, $password_4, $password_5) ?></label><input type="password" name="p"/><br/>
        <label class="loginlabellast" style="display:block">&nbsp;</label><input type="submit" value="<?php echo def($loginbutton_1, $loginbutton_2, $loginbutton_3, $loginbutton_4, $loginbutton_5) ?>"/>
    </fieldset>
</form>
<span class="loginoutro"><?php echo def($loginoutro_1, $loginoutro_2, $loginoutro_3, $loginoutro_4, $loginoutro_5) ?></span>
<script>document.loginform.u.focus();</script>
