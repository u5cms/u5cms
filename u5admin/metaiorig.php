<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>metadata <?php echo $_GET['name']?></title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	if(window.name!='files')document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>
<body>
<form action="meta2.php?typ=<?php echo $_GET['typ']?>&uri=metai.php" method="post" name="form1" id="form1">
  <p>
    <?php 

$sql_a="SELECT * FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}


$row_a = mysql_fetch_array($result_a);

?>
  </p>
  <h1>Metadata for <i><?php echo $_GET['name']?></i></h1>
  <p>
    <input type="submit" name="button2" value="save" />
  </p>
  <h2 style="margin-bottom:5px"><?php echo $lan1name ?><br />
  </h2>
  <table id="lan1name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>alt-text<font color="#FF0000">*</font></td>
      <td width="99%"><input type="hidden" name="name" value="<?php echo $_GET['name']?>">
      <input name="title_d" lang="<?php echo $lan1na ?>" type="text" id="t11" style="width:100%" value="<?php echo ehtml($row_a['title_d'])?>" /></td>
    </tr>
    <tr>
      <td>short&nbsp;caption<font color="#FF0000">*</font></td>
      <td><textarea name="desc_d" lang="<?php echo $lan1na ?>" id="t12" style="width:100%"><?php echo ehtml($row_a['desc_d'])?></textarea></td>
    </tr>
    <tr>
      <td>long&nbsp;caption<font color="#FF0000">*</font></td>
      <td><textarea name="content_d" lang="<?php echo $lan1na ?>" id="t13" style="width:100%" ><?php echo ehtml($row_a['content_d'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan2name ?><br />
  </h2>
  <table id="lan2name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>alt-text<font color="#FF0000">*</font></td>
      <td width="99%"><input name="title_e" lang="<?php echo $lan2na ?>" type="text" id="title_e" style="width:100%" value="<?php echo ehtml($row_a['title_e'])?>" /></td>
    </tr>
    <tr>
      <td>short&nbsp;caption<font color="#FF0000">*</font></td>
      <td><textarea name="desc_e" lang="<?php echo $lan2na ?>" id="t2" style="width:100%"><?php echo ehtml($row_a['desc_e'])?></textarea></td>
    </tr>
    <tr>
      <td>long&nbsp;caption<font color="#FF0000">*</font></td>
      <td><textarea name="content_e" lang="<?php echo $lan2na ?>" id="t3" style="width:100%" ><?php echo ehtml($row_a['content_e'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan3name ?><br />
  </h2>
  <table id="lan3name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>alt-text<font color="#FF0000">*</font></td>
      <td width="99%"><input name="title_f" lang="<?php echo $lan3na ?>" type="text" id="t4" style="width:100%" value="<?php echo ehtml($row_a['title_f'])?>" /></td>
    </tr>
    <tr>
      <td>short&nbsp;caption<font color="#FF0000">*</font></td>
      <td><textarea name="desc_f" lang="<?php echo $lan3na ?>" id="t5" style="width:100%"><?php echo ehtml($row_a['desc_f'])?></textarea></td>
    </tr>
    <tr>
      <td>long&nbsp;caption<font color="#FF0000">*</font></td>
      <td><textarea name="content_f" lang="<?php echo $lan3na ?>" id="t6" style="width:100%" ><?php echo ehtml($row_a['content_f'])?></textarea></td>
    </tr>
  </table>
  <p>
    <input type="submit" name="button" value="save" />
  </p>
  <p></p>
<input type="hidden" name="coco" value="<?php echo time() ?>" />
</form>
<script>document.form1.title_d.focus()</script>
<script src="emptylanhide.js.php"></script>
<?php include('focuslinktitle.inc.php') ?>
</body>
</html>
