<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>metadata <?php echo $_GET['name']?></title>
<?php require('metachg.inc.php'); ?>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	cchanges=0;document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>
<body>
<form onsubmit="cchanges=0" action="meta2.php?typ=<?php echo $_GET['typ']?>&uri=metad.php" method="post" name="form1" id="form1">
  <p>
    <?php 

$sql_a="SELECT * FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}


$row_a = mysql_fetch_array($result_a);

?>

  <h1>Metadata for <i><?php echo $_GET['name']?></i></h1>
  Add the document content to the search index by copying &amp; pasting it to the description field.
  <p>
    <input title="[Ctrl+s]" type="submit" name="button2" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  </p>
  <h2 style="margin-bottom:5px"><?php echo $lan1name ?><br />
  </h2>
  <table id="lan1name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>link-text</td>
      <td width="99%"><input type="hidden" name="name" value="<?php echo $_GET['name']?>">
      <input onchange="changes()" name="title_1" lang="<?php echo $lan1na ?>" type="text" id="t11" style="width:100%" value="<?php echo ehtml($row_a['title_1'])?>" /></td>
    </tr>
    <tr>
      <td>alt-text</td>
      <td><input onchange="changes()" name="desc_1" lang="<?php echo $lan1na ?>" type="text" id="t12" style="width:100%" value="<?php echo ehtml($row_a['desc_1'])?>" /></td>
    </tr>
    <tr>
      <td>description<br /></td>
      <td><textarea onchange="changes()" name="content_1" lang="<?php echo $lan1na ?>" id="t13" style="width:100%" ><?php echo ehtml($row_a['content_1'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan2name ?><br />
  </h2>
  <table id="lan2name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>link-text</td>
      <td width="99%"><input onchange="changes()" name="title_2" lang="<?php echo $lan2na ?>" type="text" id="t21" style="width:100%" value="<?php echo ehtml($row_a['title_2'])?>" /></td>
    </tr>
    <tr>
      <td>alt-text</td>
      <td><input onchange="changes()" name="desc_2" lang="<?php echo $lan2na ?>" type="text" id="t22" style="width:100%" value="<?php echo ehtml($row_a['desc_2'])?>" /></td>
    </tr>
    <tr>
      <td>description</td>
      <td><textarea onchange="changes()" name="content_2" lang="<?php echo $lan2na ?>" id="t23" style="width:100%" ><?php echo ehtml($row_a['content_2'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan3name ?><br />
  </h2>
  <table id="lan3name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>link-text</td>
      <td width="99%"><input onchange="changes()" name="title_3" lang="<?php echo $lan3na ?>" type="text" id="t31" style="width:100%" value="<?php echo ehtml($row_a['title_3'])?>" /></td>
    </tr>
    <tr>
      <td>alt-text</td>
      <td><input onchange="changes()" name="desc_3" lang="<?php echo $lan3na ?>" type="text" id="t32" style="width:100%" value="<?php echo ehtml($row_a['desc_3'])?>" /></td>
    </tr>
    <tr>
      <td>description</td>
      <td><textarea onchange="changes()" name="content_3" lang="<?php echo $lan3na ?>" id="t33" style="width:100%" ><?php echo ehtml($row_a['content_3'])?></textarea></td>
    </tr>
  </table>
    <h2 style="margin-bottom:5px"><?php echo $lan4name ?><br />
    </h2>
    <table id="lan4name" bgcolor="#FFFF99" width="100%">
        <tr>
            <td>link-text</td>
            <td width="99%"><input onchange="changes()" name="title_4" lang="<?php echo $lan4na ?>" type="text" id="t41" style="width:100%" value="<?php echo ehtml($row_a['title_4'])?>" /></td>
        </tr>
        <tr>
            <td>alt-text</td>
            <td><input onchange="changes()" name="desc_4" lang="<?php echo $lan4na ?>" type="text" id="t42" style="width:100%" value="<?php echo ehtml($row_a['desc_4'])?>" /></td>
        </tr>
        <tr>
            <td>description</td>
            <td><textarea onchange="changes()" name="content_4" lang="<?php echo $lan4na ?>" id="t43" style="width:100%" ><?php echo ehtml($row_a['content_4'])?></textarea></td>
        </tr>
    </table>
    <h2 style="margin-bottom:5px"><?php echo $lan5name ?><br />
    </h2>
    <table id="lan5name" bgcolor="#FFFF99" width="100%">
        <tr>
            <td>link-text</td>
            <td width="99%"><input onchange="changes()" name="title_5" lang="<?php echo $lan5na ?>" type="text" id="t51" style="width:100%" value="<?php echo ehtml($row_a['title_5'])?>" /></td>
        </tr>
        <tr>
            <td>alt-text</td>
            <td><input onchange="changes()" name="desc_5" lang="<?php echo $lan5na ?>" type="text" id="t52" style="width:100%" value="<?php echo ehtml($row_a['desc_5'])?>" /></td>
        </tr>
        <tr>
            <td>description</td>
            <td><textarea onchange="changes()" name="content_5" lang="<?php echo $lan5na ?>" id="t53" style="width:100%" ><?php echo ehtml($row_a['content_5'])?></textarea></td>
        </tr>
    </table>
  <p>
    <input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>

  </p>
  <p></p>
<input type="hidden" name="coco" value="<?php echo time() ?>" />
</form>
<script>
initchanges();
</script>
<script>document.form1.title_1.focus()</script>
<script src="emptylanhide.js.php"></script>
<?php include('focuslinktitle.inc.php') ?>
</body>
</html>
