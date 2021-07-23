<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>define languages</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	cchanges=0;document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<h1>Login instructions</h1>
<?php
$sql_a="SELECT * FROM loginglobals";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);

?>
<script>
format='Here you may use u5CMS-syntax.\n\nExamples:\n[b]my text[/]\n[h:]<b>my text</b>[:h]\n\nOr more convenient and even necessary for very long content refer to a page like this:\n[$$$:mypage]';
</script>

<h3>Define login instructions <small>(goto <a href="definelanguages.php">languages</a>)</small></h3>
<form onsubmit="cchanges=0" name="form1" method="post" action="defineloginglobals2.php">

<h4>Title</h4>
1<input name="logintitle_d" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_d']) ?>"><br>
2<input name="logintitle_e" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_e']) ?>"><br>
3<input name="logintitle_f" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_f']) ?>"><br>
<h4>Intro <span style="font-size:70%"><a href="javascript:alert(format)">format</a></span></h4>
1<textarea name="loginintro_d" cols="90"><?php echo ehtml($row_a['loginintro_d']) ?></textarea><br>
2<textarea name="loginintro_e" cols="90"><?php echo ehtml($row_a['loginintro_e']) ?></textarea><br>
3<textarea name="loginintro_f" cols="90"><?php echo ehtml($row_a['loginintro_f']) ?></textarea><br>
<h4>Username</h4>
1<input name="username_d" type="text" size="90" value="<?php echo ehtml($row_a['username_d']) ?>"><br>
2<input name="username_e" type="text" size="90" value="<?php echo ehtml($row_a['username_e']) ?>"><br>
3<input name="username_f" type="text" size="90" value="<?php echo ehtml($row_a['username_f']) ?>"><br>
<h4>Password</h4>
1<input name="password_d" type="text" size="90" value="<?php echo ehtml($row_a['password_d']) ?>"><br>
2<input name="password_e" type="text" size="90" value="<?php echo ehtml($row_a['password_e']) ?>"><br>
3<input name="password_f" type="text" size="90" value="<?php echo ehtml($row_a['password_f']) ?>"><br>
<h4>Login-Button</h4>
1<input name="loginbutton_d" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_d']) ?>"><br>
2<input name="loginbutton_e" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_e']) ?>"><br>
3<input name="loginbutton_f" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_f']) ?>"><br>
<h4>Outro <span style="font-size:70%"><a href="javascript:alert(format)">format</a></span></h4>
1<textarea name="loginoutro_d" cols="90"><?php echo ehtml($row_a['loginoutro_d']) ?></textarea><br>
2<textarea name="loginoutro_e" cols="90"><?php echo ehtml($row_a['loginoutro_e']) ?></textarea><br>
3<textarea name="loginoutro_f" cols="90"><?php echo ehtml($row_a['loginoutro_f']) ?></textarea><br>
<h4>Logout-Button</h4>
1<input name="logout_d" type="text" size="90" value="<?php echo ehtml($row_a['logout_d']) ?>"><br>
2<input name="logout_e" type="text" size="90" value="<?php echo ehtml($row_a['logout_e']) ?>"><br>
3<input name="logout_f" type="text" size="90" value="<?php echo ehtml($row_a['logout_f']) ?>"><br>
<h4>Too many login attempts. Try again in</h4>
1<input name="wait_d" type="text" size="90" value="<?php echo ehtml($row_a['wait_d']) ?>"><br>
2<input name="wait_e" type="text" size="90" value="<?php echo ehtml($row_a['wait_e']) ?>"><br>
3<input name="wait_f" type="text" size="90" value="<?php echo ehtml($row_a['wait_f']) ?>"><br>
<p></p>
<input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
        </p>
</form>
<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>