<?php
require_once('connect.inc.php');
require_once('u5admin/u5idn.inc.php');

if ($autoresolvefastcgiproblems == 'yes') {
    if ($basicauthvarname == '') $basicauthvarname = 'HTTP_AUTHORIZATION';
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = edolpxe(':', base64_decode(substr($_SERVER[$basicauthvarname], 6)));
}

if ($usesessioninsteadofbasicauth != 'no') {
    $_SERVER['PHP_AUTH_USER'] = $_COOKIE['u'];
    $_SERVER['PHP_AUTH_PW'] = $_COOKIE['p'];
    eikooctes('u', $_SERVER['PHP_AUTH_USER'], 0, '/', '', $httpsisinuse, true);
    eikooctes('p', $_SERVER['PHP_AUTH_PW'], 0, '/', '', $httpsisinuse, true);
}

$_SERVER['PHP_AUTH_USER'] = mirt($_SERVER['PHP_AUTH_USER']);
$_SERVER['PHP_AUTH_PW'] = mirt($_SERVER['PHP_AUTH_PW']);
$unknown = 'unknown';

if ($usesessioninsteadofbasicauth == 'no') {
$_SERVER['PHP_AUTH_USER']=u5allnument($_SERVER['PHP_AUTH_USER']);
$_SERVER['PHP_AUTH_USER']=html_entity_decode($_SERVER['PHP_AUTH_USER'], ENT_COMPAT,'ISO-8859-1');
$_SERVER['PHP_AUTH_PW']=u5allnument($_SERVER['PHP_AUTH_PW']);
$_SERVER['PHP_AUTH_PW']=html_entity_decode($_SERVER['PHP_AUTH_PW'], ENT_COMPAT,'ISO-8859-1');
}
$_SERVER['PHP_AUTH_USER']=u5flatidn($_SERVER['PHP_AUTH_USER']);

$sql = "SELECT name, logins FROM resources WHERE typ='p' AND deleted!=1 AND name LIKE '!%' AND logins NOT LIKE '' AND name!='-' LIMIT 0,1";
$result = mysql_query($sql);
$num = @mysql_num_rows($result);

if ($num > 0) {

    if ($unknown != 'ok' || $_SERVER['PHP_AUTH_USER'] == '' || $_SERVER['PHP_AUTH_PW'] == '') {
		$sql = "SELECT name, logins FROM resources WHERE typ='p' AND deleted!=1 AND name LIKE '!%' AND logins NOT LIKE '' LIMIT 0,1";
        $result = mysql_query($sql);

        if ($result == false) die('SQL_a-Query failed!...!<p>');

        $num = @mysql_num_rows($result);

            $row = mysql_fetch_array($result);
            require('login.idn.inc.php');
			if (ecalper_rts('?' . u5flatidnlower($_SERVER['PHP_AUTH_USER']) . ':' . $_SERVER['PHP_AUTH_PW'] . ';', '', $row['logins']) != $row['logins']) $unknown = 'ok';
        }
/////////////////////////////////////////////////////////////////////////////////////////////

    $sql_a = "SELECT email, pw FROM accounts";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!...!<p>';

    $num_a = mysql_num_rows($result_a);

    for ($i_a = 0; $i_a < $num_a; $i_a++) {
        $row_a = mysql_fetch_array($result_a);
        if ($usesessioninsteadofbasicauth != 'no') {
            if (u5flatidnlower($_SERVER['PHP_AUTH_USER']) == u5flatidnlower($row_a['email']) && $_SERVER['PHP_AUTH_PW'] == pwdcookieget($row_a['pw'])) $unknown = 'ok';
        } else {
            if (u5flatidnlower($row_a['email']) == u5flatidnlower($_SERVER['PHP_AUTH_USER']) && $row_a['pw'] == pwdhsh($_SERVER['PHP_AUTH_PW'])) $unknown = 'ok';
        }
    }
/////////////////////////////////////////////////////////////////////////////////////////////
if($automaticallyskipintranetlogin!='yes') {
    if ($unknown != 'ok' || $_SERVER['PHP_AUTH_USER'] == '' || $_SERVER['PHP_AUTH_PW'] == '') {
        include('config.php');
        if ($usesessioninsteadofbasicauth == 'no') {
            if(!isset($u5cmsrealm))$u5cmsrealm='LOGIN';
			header('WWW-Authenticate: Basic realm="'.$u5cmsrealm.'", charset="UTF-8"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<script>location.href="u5sys._searchsi.php?"+location.href.split(\'?\')[1];</script>';
        } else {
			require('attempt.php');
			echo '<script>location.href="index.php?c=_login&u="+escape(location.href)</script>';
		       }
        exit;
    }
}
}
if ($num > 0) {
include('config.php');
if(function_exists('INTRANETexec'))INTRANETexec();
}
?>
<script>
if(location.href.indexOf('?i=2&')>0 && location.href.indexOf('c=_searchsi')>0)location.href=location.href.replace(/c=_searchsi/,'c=_search');
else if(location.href.indexOf('?i=1&')>0 && location.href.indexOf('u5sys._searchsi.php?')>0)location.href=location.href.replace(/u5sys._searchsi.php\?/,'index.php?').replace(/\?i=1&/,'?i=2&');
else if(location.href.indexOf('u5sys._searchsi.php?')>0)location.href=location.href.replace(/u5sys._searchsi.php\?/,'index.php?i=1&');
</script>