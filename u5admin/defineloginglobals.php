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
1<input name="logintitle_1" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_1']) ?>"><br>
2<input name="logintitle_2" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_2']) ?>"><br>
3<input name="logintitle_3" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_3']) ?>"><br>
4<input name="logintitle_4" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_4']) ?>"><br>
5<input name="logintitle_5" type="text" size="90" value="<?php echo ehtml($row_a['logintitle_5']) ?>"><br>
<h4>Intro <span style="font-size:70%"><a href="javascript:alert(format)">format</a></span></h4>
1<textarea name="loginintro_1" cols="90"><?php echo ehtml($row_a['loginintro_1']) ?></textarea><br>
2<textarea name="loginintro_2" cols="90"><?php echo ehtml($row_a['loginintro_2']) ?></textarea><br>
3<textarea name="loginintro_3" cols="90"><?php echo ehtml($row_a['loginintro_3']) ?></textarea><br>
4<textarea name="loginintro_4" cols="90"><?php echo ehtml($row_a['loginintro_4']) ?></textarea><br>
5<textarea name="loginintro_5" cols="90"><?php echo ehtml($row_a['loginintro_5']) ?></textarea><br>
<h4>Username</h4>
1<input name="username_1" type="text" size="90" value="<?php echo ehtml($row_a['username_1']) ?>"><br>
2<input name="username_2" type="text" size="90" value="<?php echo ehtml($row_a['username_2']) ?>"><br>
3<input name="username_3" type="text" size="90" value="<?php echo ehtml($row_a['username_3']) ?>"><br>
4<input name="username_4" type="text" size="90" value="<?php echo ehtml($row_a['username_4']) ?>"><br>
5<input name="username_5" type="text" size="90" value="<?php echo ehtml($row_a['username_5']) ?>"><br>
<h4>Password</h4>
1<input name="password_1" type="text" size="90" value="<?php echo ehtml($row_a['password_1']) ?>"><br>
2<input name="password_2" type="text" size="90" value="<?php echo ehtml($row_a['password_2']) ?>"><br>
3<input name="password_3" type="text" size="90" value="<?php echo ehtml($row_a['password_3']) ?>"><br>
4<input name="password_4" type="text" size="90" value="<?php echo ehtml($row_a['password_4']) ?>"><br>
5<input name="password_5" type="text" size="90" value="<?php echo ehtml($row_a['password_5']) ?>"><br>
<h4>Login-Button</h4>
1<input name="loginbutton_1" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_1']) ?>"><br>
2<input name="loginbutton_2" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_2']) ?>"><br>
3<input name="loginbutton_3" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_3']) ?>"><br>
4<input name="loginbutton_4" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_4']) ?>"><br>
5<input name="loginbutton_5" type="text" size="90" value="<?php echo ehtml($row_a['loginbutton_5']) ?>"><br>
<h4>Outro <span style="font-size:70%"><a href="javascript:alert(format)">format</a></span></h4>
1<textarea name="loginoutro_1" cols="90"><?php echo ehtml($row_a['loginoutro_1']) ?></textarea><br>
2<textarea name="loginoutro_2" cols="90"><?php echo ehtml($row_a['loginoutro_2']) ?></textarea><br>
3<textarea name="loginoutro_3" cols="90"><?php echo ehtml($row_a['loginoutro_3']) ?></textarea><br>
4<textarea name="loginoutro_4" cols="90"><?php echo ehtml($row_a['loginoutro_4']) ?></textarea><br>
5<textarea name="loginoutro_5" cols="90"><?php echo ehtml($row_a['loginoutro_5']) ?></textarea><br>
<h4>Logout-Button</h4>
1<input name="logout_1" type="text" size="90" value="<?php echo ehtml($row_a['logout_1']) ?>"><br>
2<input name="logout_2" type="text" size="90" value="<?php echo ehtml($row_a['logout_2']) ?>"><br>
3<input name="logout_3" type="text" size="90" value="<?php echo ehtml($row_a['logout_3']) ?>"><br>
4<input name="logout_4" type="text" size="90" value="<?php echo ehtml($row_a['logout_4']) ?>"><br>
5<input name="logout_5" type="text" size="90" value="<?php echo ehtml($row_a['logout_5']) ?>"><br>
<h4>Too many login attempts. Try again in</h4>
1<input name="wait_1" type="text" size="90" value="<?php echo ehtml($row_a['wait_1']) ?>"><br>
2<input name="wait_2" type="text" size="90" value="<?php echo ehtml($row_a['wait_2']) ?>"><br>
3<input name="wait_3" type="text" size="90" value="<?php echo ehtml($row_a['wait_3']) ?>"><br>
4<input name="wait_4" type="text" size="90" value="<?php echo ehtml($row_a['wait_4']) ?>"><br>
5<input name="wait_5" type="text" size="90" value="<?php echo ehtml($row_a['wait_5']) ?>"><br>
<p></p>
<input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
        </p>
<?php require('t1.php') ?></form>
<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>