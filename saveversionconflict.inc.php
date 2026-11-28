<?php
$foundthisformerauthuser=$foundthisformerauthuser ?? '';
if(strpos($foundthisformerauthuser,'@')>0)$rowaauthuser=$foundthisformerauthuser;
else $rowaauthuser='someone else';

if(($checksaveversionconflictinfrontendforms!='none'&&$checksaveversionconflictinfrontendforms!='foreign')||($checksaveversionconflictinfrontendforms=='foreign'&&trim(u5flatidnlower($foundthisformerauthuser))!=trim(u5flatidnlower($_SERVER['PHP_AUTH_USER'])))) {
if(isset($_POST['firstsaverwins']) && ((!empty($u5FormDataSubmittedVersion) && $u5FormDataSubmittedVersion!==($u5FormDataCurrentVersion ?? '')) || (empty($u5FormDataSubmittedVersion) && $foundthisformerlastmut>$_POST['firstsaverwins'])))die('<script>alert("You cannot save your data because '.$rowaauthuser.' has saved another version with timestamp '.date('Y-m-d H:i:s',$foundthisformerlastmut).' since the version loaded when you opened this form (opening timestamp '.date('Y-m-d H:i:s',$_POST['firstsaverwins']).'). If you have entered a precious amount of data, copy and paste it in a text editor. Then reload the page at hand. Apologies for any inconvenience caused.")</script>');
}
?>