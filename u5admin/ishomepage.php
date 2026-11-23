<?php
require_once('connect.inc.php');
require_once('homepage-eligible.inc.php');
$pages = mysql_query("SELECT name, ishomepage FROM resources WHERE ".u5HomepageEligible()." ORDER BY name");
if ($pages === false) {
    http_response_code(500);
    die('Could not load the homepage list.');
}
$hasPages = mysql_num_rows($pages) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>define homepage</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	if (!document.form1.button.disabled) { cchanges=0;document.form1.submit(); }
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<h1>Define homepage</h1>
<form onsubmit="cchanges=0;document.querySelectorAll('.asterisk').forEach(e=>e.classList.add('blink_me'));" name="form1" method="post" action="ishomepage2.php">
<p>Define which page shall be displayed as your homepage (mainpage).</p>

<?php if ($hasPages) { ?>
<select onchange="changes()" name="name">
<?php while ($page = mysql_fetch_assoc($pages)) {
    $name = htmlXspecialchars($page['name']);
?>
<option value="<?php echo $name ?>"<?php if ($page['ishomepage']==1) echo ' selected' ?>><?php echo $name ?></option>
<?php } ?>
</select>
<?php } else { ?>
<p>No visible pages are available to select as the homepage.</p>
<?php } ?>
<input title="[Ctrl+s]" type="submit" <?php if (!$hasPages) echo 'disabled' ?> name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
<?php require('t1.php') ?></form>
<?php include('selfclose.inc.php')?>
</body>
</html>
