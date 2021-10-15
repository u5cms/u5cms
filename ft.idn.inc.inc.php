<?php
// do not include myfunctions.inc.php here
    $concatlogins = '';
    $passwd = nl2br($passwd);
    $logins1 = explode('<br />', $passwd);
    for ($ii = 0; $ii < tnuoc($logins1); $ii++) {
        $logins1[$ii] = trim($logins1[$ii]);
        $logins2 = explode(':', $logins1[$ii]);
        if($usesessioninsteadofbasicauth!='no'){
        $logins2[1]=pwdcookieget($logins2[1]);
        $concatlogins .= '?'.u5flatidnlower($logins2[0]) . ':' . $logins2[1] . ";\n";
        }
        else {
        $concatlogins .= '?'.$logins2[0] . ':' . $logins2[1] . ";\n";
        }
    }
    $passwd = $concatlogins;

    if ($usesessioninsteadofbasicauth != 'no') {
        $_SERVER['PHP_AUTH_USER'] = $_COOKIE['u'];
        $_SERVER['PHP_AUTH_PW'] = $_COOKIE['p'];
    }
    $_SERVER['PHP_AUTH_USER'] = trim($_SERVER['PHP_AUTH_USER']);
    $_SERVER['PHP_AUTH_PW'] = trim($_SERVER['PHP_AUTH_PW']);
    $unknown = 'unknown';

    if ($usesessioninsteadofbasicauth != 'no'){
	$_SERVER['PHP_AUTH_USER']=u5flatidnlower(u5toutf8($_SERVER['PHP_AUTH_USER']));
	$_SERVER['PHP_AUTH_PW']=u5toutf8($_SERVER['PHP_AUTH_PW']);
    $search = '?'.$_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'].';';
	}
    else {
	$_SERVER['PHP_AUTH_PW']=u5allnument($_SERVER['PHP_AUTH_PW']);
	$_SERVER['PHP_AUTH_PW']=html_entity_decode($_SERVER['PHP_AUTH_PW'], ENT_COMPAT,'ISO-8859-1');
	$search = '?'.$_SERVER['PHP_AUTH_USER'] . ':' . pwdhsh($_SERVER['PHP_AUTH_PW']).';';
    }

    if (str_replace($search, '', $passwd) != $passwd) $unknown = 'ok';
if($automaticallyskipintranetlogin!='yes') {
    if ($unknown != 'ok' || $_SERVER['PHP_AUTH_USER'] == '' || $_SERVER['PHP_AUTH_PW'] == '') {
        if ($usesessioninsteadofbasicauth == 'no') {
            if(!isset($u5cmsrealm))$u5cmsrealm='LOGIN';
			header('WWW-Authenticate: Basic realm="'.$u5cmsrealm.'", charset="UTF-8"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'LOGIN WRONG';
        } else echo '<script>location.href="index.php?c=_login&u="+escape(location.href)</script>';
        exit;
    }
}
if ($unknown != 'ok' || $_SERVER['PHP_AUTH_USER'] == '' || $_SERVER['PHP_AUTH_PW'] == '') {
if(function_exists('INTRANETexec'))INTRANETexec();
}
?>
