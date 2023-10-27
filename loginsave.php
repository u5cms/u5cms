<?php
require('connect.inc.php');
require_once('u5admin/u5idn.inc.php');

if (isset($u5samlsalt)&&$u5samlsalt!='') {
    if ($u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain!='' && str_replace($u5samlinfrontendyesenforcedifloginformsgetudoesnotcontain,'',$_GET['u'])==$_GET['u']) $u5samlinfrontend='yes';
    if ($u5samlinfrontend != 'no' || $_GET['u'] == 'u5admin') {
        require('saml.inc.php');
        if (!isset($_POST['u']) || empty($_POST['u'])) {
            // in case SAML-Login and not backend user
            $_POST['u']=$_COOKIE['u5samlusername'];

            $sql_a="SELECT * FROM intranetsalt";
            $result_a=mysql_query($sql_a);

            if ($result_a==false) {
                echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
            }
            $row_a = mysql_fetch_array($result_a);
            $salt=$row_a['salt'];
            $_POST['p']=floor(crc32(u5flatidnlower($_COOKIE['u5samlusername']).$salt));
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
