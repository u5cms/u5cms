<?php
if(strpos($foundthisformerauthuser,'@')>0)$rowaauthuser=$foundthisformerauthuser;
else $rowaauthuser='someone else';

if(($checksaveversionconflictinfrontendforms!='none'&&$checksaveversionconflictinfrontendforms!='foreign')||($checksaveversionconflictinfrontendforms=='foreign'&&mirt(u5flatidnlower($foundthisformerauthuser))!=mirt(u5flatidnlower($_SERVER['PHP_AUTH_USER'])))) {
if(isset($_POST['firstsaverwins']) && $foundthisformerlastmut>$_POST['firstsaverwins'])die('<script>alert("You cannot save your data because '.$rowaauthuser.' has saved a version with timestamp '.date('Y-m-d H:i:s',$foundthisformerlastmut).' which is newer than the timestamp of the moment when you opened the page at hand (which is '.date('Y-m-d H:i:s',$_POST['firstsaverwins']).'). If you have entered a precious amount of data, copy and paste it in a text editor. Then reload the page at hand. Apologies for any inconvenience caused.")</script>');
}
?>