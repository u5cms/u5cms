<?php
require('connect.inc.php');
require_once('u5admin/u5idn.inc.php');

if (isset($_GET['u'])) {
    $u = $_GET['u'];
    if (!is_string($u)) die('<center style="color:red">Error: Invalid redirect target.</center>');
    if (class_exists('Normalizer')) $u = Normalizer::normalize($u, Normalizer::FORM_KC);
    $target = trim($u);
    if (strlen($target) > 2000) die('<center style="color:red">Error: Invalid redirect target.</center>');
    for ($i=0;$i<3;$i++){ $d=rawurldecode($target); if ($d===$target) break; $target=$d; }
    if ($target==='' ||
        preg_match('/\p{C}/u',$target) ||
        preg_match('/\\\\/',$target) ||
        preg_match('#/(?:\.\.?)(?:/|$)#',$target) ||
        preg_match('#^//#',$target)) {
        die('<center style="color:red">Error: Invalid redirect target.</center>');
    }
    $p = parse_url($target);
    if ($p===false) die('<center style="color:red">Error: Invalid redirect target.</center>');
    if (!empty($p['scheme']) && !in_array(strtolower($p['scheme']),['http','https'],true)) {
        die('<center style="color:red">Error: Invalid redirect target.</center>');
    }
    if (!empty($p['user']) || !empty($p['pass'])) {
        die('<center style="color:red">Error: Invalid redirect target.</center>');
    }
    if (!empty($p['host'])) {
        $reqHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $reqHost = preg_replace('/:\d+$/','',$reqHost);
        $cmpA = function($h){ $h=strtolower($h); $idn=function_exists('idn_to_ascii')?idn_to_ascii($h,IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46):$h; return $idn?:$h; };
        if (strcasecmp($cmpA($p['host']), $cmpA($reqHost))!==0) {
            die('<center style="color:red">Error: Redirect to a different domain is not allowed.</center>');
        }
        $reqPort = (int)($_SERVER['SERVER_PORT'] ?? 80);
        if (!empty($p['port']) && (int)$p['port']!==$reqPort) {
            die('<center style="color:red">Error: Redirect to a different port is not allowed.</center>');
        }
    }
}

if (isset($u5samlsalt)&&$u5samlsalt!='') {
    if ($u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain!='' && str_replace($u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain,'',$_GET['u'])==$_GET['u']) $u5samlinfrontend='yes';
    if ($u5samlinfrontend != 'no' || $_GET['u'] == 'u5admin') {
        require('saml.inc.php');
        if (!isset($_POST['u']) || empty($_POST['u'])) {
            // in case SAML-Login and not backend user
			$_POST['u']=$_COOKIE['u5samlusername'];

            $sql_a="SELECT * FROM intranetsalt";
            $result_a=mysql_query($sql_a);
            if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';
            $row_a = mysql_fetch_array($result_a);
            $salt=$row_a['salt'];
            $_POST['p']=floor(crc32(u5flatidnlower($_COOKIE['u5samlusername']).$salt));
			
			if(strpos($_GET['u'],'c=!')<1) {
			$thegc=explode('c=',$_GET['u']);
			$thegc=explode('&',$thegc[1]);
			$thegc=$thegc[0];
            $sql_a="SELECT logins FROM resources WHERE name= '".mysql_real_escape_string($thegc)."' AND logins LIKE '%?".mysql_real_escape_string($_COOKIE['u5samlusername']).":%'";
            $result_a=mysql_query($sql_a);
            if ($result_a==false) echo 'SQL_a-Query failed!...!<p>';
            $num_a = mysql_num_rows($result_a);
			if($num_a>0) {
			$row_a = mysql_fetch_array($result_a);
			$rowalogins=$row_a['logins'];
			$rowalogins=explode('?'.$_COOKIE['u5samlusername'].':',$rowalogins);
			$rowalogins=explode(';',$rowalogins[1]);
			$rowalogins=$rowalogins[0];
			if($rowalogins!='')$_POST['p']=$rowalogins;	
			}
			}			
        }
    }
}

$_POST['u']=u5flatidn($_POST['u']);

setcookie('u', trim($_POST['u']), 0, '/', '', $httpsisinuse, true);
setcookie('p', pwdcookie(trim($_POST['p'])), 0, '/', '', $httpsisinuse, true);
$u=explode('#',$_GET['u']);
if(isset($u[1]))$u[1]='#'.$u[1];
else $u[1]='';
if(trim($u[0])=='')$u[0]='index.php';
if(strpos($u[0],'?')>1)$u[0].='&'.time().$u[1];
else $u[0].='?'.time().$u[1];
header("Location: ".$u[0]);
?>