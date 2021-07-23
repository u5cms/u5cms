<?php require_once('connect.inc.php'); require_once('u5idn.inc.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {

})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<?php 
if ($viewbackenduserlistRqHIADRI!='no') require_once('accadmin.inc.php'); 
?>
<h2>Invite a backend user (send password)</h2>
e-mail:<input onkeyup="if(this.value!=this.value.replace(/\s/g,''))this.value=this.value.replace(/\s/g,'')" onchange="this.value=this.value.replace(/\s/g,'')" type="email" required="required" size="40" id="invite">&nbsp;<a href="javascript:void(0)" onclick="parent.saver.location.href='invite.php?e='+escape(document.getElementById('invite').value)">send</a>
<br /><br />
<h2>Current backend users</h2>
<?php 
$sql_a="SELECT * FROM accounts ORDER BY email";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

echo "<table cellspacing=12><tr><th>User</th><th>Higher Admin Rights*</th><th>Last Activity</th><th></th></tr>";

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

$Lchecked='';
$Rchecked='';

if ($row_a['accadmin']==0) $Lchecked='checked="checked"';
if ($row_a['accadmin']==1) $Rchecked='checked="checked"';

echo '<tr bgcolor="#eeeeee"><td style="word-break:break-all">'.$row_a['email'].'</td><td nowrap>
n<input onmousedown="if (\''.u5flatidnlower($_SERVER['PHP_AUTH_USER']).'\'==\''.u5flatidnlower($row_a['email']).'\') alert(\'You cannot downgrade yourself.\');else if (document.getElementById(\'iR'.$row_a['id'].'\').checked==true) parent.saver.location.href=\'accacc.php?a=0&id='.$row_a['id'].'&y='.$row_a['email'].'\'" id="iL'.$row_a['id'].'" name="n'.$row_a['id'].'" type="radio" value="0" '.$Lchecked.'>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

y<input onmousedown="if (document.getElementById(\'iL'.$row_a['id'].'\').checked==true) parent.saver.location.href=\'accacc.php?a=1&id='.$row_a['id'].'&y='.$row_a['email'].'\'" id="iR'.$row_a['id'].'" name="n'.$row_a['id'].'" type="radio" value="1" '.$Rchecked.'>

</td><td>'.($row_a['lastused']>0 ? date('Y/m/d H:i',$row_a['lastused']) : 'none').'</td><td><a href="javascript:void(0)" onclick="if (\''.u5flatidnlower($_SERVER['PHP_AUTH_USER']).'\'==\''.u5flatidnlower($row_a['email']).'\') alert(\'You cannot delete yourself.\');else parent.saver.location.href=\'accacc.php?a=2&id='.$row_a['id'].'&y='.$row_a['email'].'\'">delete</a></td></tr>';      
}
echo "<table>";	
?>
<br /><br />
<span style="font-size:80%">*Higher Admin Rights are needed for restricted areas according to the settings in <a href="javascript:void(0)" onclick="if(confirm('Do you want to display the initial config.php template now?\n\nNote: This is the initial template and not the config.php in use by your u5CMS installation. To change your config.php, you need to access the file system of your server. Consult the template if there are missing entries in your config.php'))window.open('http://yuba.ch/cp')">config.php</a>.</span>
</script>
<script>
window.onload=function readScroll()
{
 var hscroll2 = readCookie('scrollpos3h');
 var vscroll2 = readCookie('scrollpos3v');
 window.scrollTo(hscroll2,vscroll2);
}

function createCookie(name,value,days) {
 if (days) {
  var date = new Date();
  date.setTime(date.getTime()+(days*24*60*60*1000));
  var expires = "; expires="+date.toGMTString();
 }
 else var expires = "";
 document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
 var nameEQ = name + "=";
 var ca = document.cookie.split(';');
 for(var i=0;i < ca.length;i++) {
  var c = ca[i];
  while (c.charAt(0)==' ') c = c.substring(1,c.length);
  if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
 }
 return null;
}

function eraseCookie(name) {
 createCookie(name,"",-1);
}

function f_scrollLeft() {
 return f_filterResults (
  window.pageXOffset ? window.pageXOffset : 0,
  document.documentElement ? document.documentElement.scrollLeft : 0,
  document.body ? document.body.scrollLeft : 0
 );
}
function f_scrollTop() {
 return f_filterResults (
  window.pageYOffset ? window.pageYOffset : 0,
  document.documentElement ? document.documentElement.scrollTop : 0,
  document.body ? document.body.scrollTop : 0
 );
}
function f_filterResults(n_win, n_docel, n_body) {
 var n_result = n_win ? n_win : 0;
 if (n_docel && (!n_result || (n_result > n_docel)))
  n_result = n_docel;
 return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

window.onscroll=function saveScroll()
{
    var hscroll = 0, vscroll = 0;
   
 if( typeof( window.pageYOffset ) == 'number' ) {
     //Netscape compliant
     vscroll = window.pageYOffset;
     hscroll = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
     //DOM compliant
     vscroll = document.body.scrollTop;
     hscroll = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
     //IE6 standards compliant mode
     vscroll = document.documentElement.scrollTop;
     hscroll = document.documentElement.scrollLeft;
 }
 createCookie('scrollpos3h',hscroll,1);
 createCookie('scrollpos3v',vscroll,1);
}
</script>
<iframe src="htaccess.php" widht="1" height="1" frameborder="0"></iframe>
</body>
</html>