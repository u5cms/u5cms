<?php require_once('connect.inc.php'); require_once('h1.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>saving...</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
    document.getElementById('clicksend').click();
});
</script>
<?php require('backendcss.php'); ?>
</head>

<body>
<?php 
if ($viewbackenduserlistRqHIADRI!='no') require_once('accadmin.inc.php'); 
?>
<h2>Invite a backend user (<?php
if (!(isset($u5samlsalt) && $u5samlsalt!='')) {
    echo 'send password';
} else {
    echo '<small><a href="javascript:void(0)" onclick="alert(\'The u5CMS installation at hand is federated via SAML with an external IAM system. Therefore, your target user must log in using an account from that IAM system that has exactly the email address you entered below as its email attribute. The invited user does not receive any automatic invitation email at all and must be informed manually if necessary.\')">does NOT send a password. Read!</a></small>';
}
?>)</h2>

e-mail:<input
    onkeyup="if(this.value!=this.value.replace(/\s/g,''))this.value=this.value.replace(/\s/g,'')"
    onchange="this.value=((this.value.match(/@/g)||[]).length>1 && !/[;,]/.test(this.value)) ? '' : ((this.value.match(/[\p{L}0-9._%+'-]+@[\p{L}0-9.-]+\.[\p{L}0-9.-]{2,}/u)||[''])[0])"
    type="email"
    required
    size="40"
    id="invite"
>&nbsp;<a id="clicksend" href="javascript:void(0)" onclick="parent.saver.location.href='invite.php?e='+escape(document.getElementById('invite').value)+'&h=<?php echo $u5cmsscrttkngt ?>'">send</a>

<br /><br />
<h2>Current backend users</h2>
<?php 
$sql_a="SELECT * FROM accounts ORDER BY email";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
    echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

echo "<table cellspacing=\"12\"><tr><th>User</th><th>Higher Admin Rights*</th><th>Last Activity</th><th></th></tr>";

$self_js = str_replace("'", "\\'", u5flatidnlower($_SERVER['PHP_AUTH_USER']));

for ($i_a=0; $i_a<$num_a; $i_a++) {
    $row_a = mysql_fetch_array($result_a);

    $Lchecked='';
    $Rchecked='';

    if ($row_a['accadmin']==0) $Lchecked='checked="checked"';
    if ($row_a['accadmin']==1) $Rchecked='checked="checked"';

    $email_js = str_replace("'", "\\'", u5flatidnlower($row_a['email']));

    echo '<tr style="background:#eeeeee" onmouseover="this.style.background=\'lightyellow\'"  onmouseout="this.style.background=\'#eeeeee\'">'
        . '<td style="word-break:break-all">'.$row_a['email'].'</td>'
        . '<td nowrap>'
        . 'n<input onmousedown="if (\''.$self_js.'\'==\''.$email_js.'\') alert(\'You cannot downgrade yourself.\');'
        . 'else if (document.getElementById(\'iR'.$row_a['id'].'\').checked==true) parent.saver.location.href=\'accacc.php?a=0&id='.$row_a['id'].'&y='.$row_a['email'].'\'"'
        . ' id="iL'.$row_a['id'].'" name="n'.$row_a['id'].'" type="radio" value="0" '.$Lchecked.'>'

        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'

        . 'y<input onmousedown="if (document.getElementById(\'iL'.$row_a['id'].'\').checked==true) parent.saver.location.href=\'accacc.php?a=1&id='.$row_a['id'].'&y='.$row_a['email'].'\'"'
        . ' id="iR'.$row_a['id'].'" name="n'.$row_a['id'].'" type="radio" value="1" '.$Rchecked.'>'

        . '</td><td>'.($row_a['lastused']>0 ? date('Y/m/d H:i',$row_a['lastused']) : 'none').'</td>'
        . '<td><a href="javascript:void(0)" onclick="if (\''.$self_js.'\'==\''.$email_js.'\') alert(\'You cannot delete yourself.\');'
        . 'else parent.saver.location.href=\'accacc.php?a=2&id='.$row_a['id'].'&y='.$row_a['email'].'\'">delete</a></td></tr>';
}
echo "</table>";	
?>
<br /><br />
<span style="font-size:80%">*Higher Admin Rights are needed for restricted areas according to the settings in <a href="javascript:void(0)" onclick="if(confirm('Do you want to display the initial config.php template now?\n\nNote: This is the initial template and not the config.php in use by your u5CMS installation. To change your config.php, you need to access the file system of your server. Consult the template if there are missing entries in your config.php'))window.open('http://yuba.ch/cp')">config.php</a>.</span>

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
   
    if (typeof(window.pageYOffset) == 'number') {
        vscroll = window.pageYOffset;
        hscroll = window.pageXOffset;
    } else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
        vscroll = document.body.scrollTop;
        hscroll = document.body.scrollLeft;
    } else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
        vscroll = document.documentElement.scrollTop;
        hscroll = document.documentElement.scrollLeft;
    }
    createCookie('scrollpos3h',hscroll,1);
    createCookie('scrollpos3v',vscroll,1);
}
</script>
<iframe src="htaccess.php" width="1" height="1" frameborder="0"></iframe>
</body>
</html>