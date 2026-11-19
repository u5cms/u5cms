<?php require_once('connect.inc.php');
$sql_a="SELECT * FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}


$row_a = mysql_fetch_array($result_a);


$u5MetaIsCode=$row_a && strtolower((string)$row_a['typ'])==='c';$u5MetaUtf8=array();
if ($u5MetaIsCode) {
    $u5MetaFields=array();
    foreach (range(1,5) as $language) foreach (array('content','title','desc','key') as $prefix) {
        $field=$prefix.'_'.$language;$u5MetaFields[]='CAST(CONVERT(`'.$field.'` USING utf8mb4) AS BINARY) AS `'.$field.'`';
    }
    $u5MetaResult=mysql_query('SELECT '.implode(',',$u5MetaFields)." FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($_GET['name'])."' LIMIT 1");
    if (!$u5MetaResult) die('Cannot read code metadata.');
    $u5MetaUtf8=mysql_fetch_array($u5MetaResult,MYSQL_ASSOC);
}
$u5MetaEscape=function($field) use($row_a,$u5MetaIsCode,$u5MetaUtf8) {
    if (!$u5MetaIsCode) return ehtml($row_a[$field]);
    return mb_encode_numericentity(htmlspecialchars($u5MetaUtf8[$field] ?? '',ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'),array(128,0x10ffff,0,0x1fffff),'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>metadata <?php echo $_GET['name']?></title>
<?php require('metachg.inc.php'); ?>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function(){u5ResourceSave.submit();});
shortcut.add("Meta+S",function(){u5ResourceSave.submit();});
</script>
<?php require('backendcss.php'); ?><script src="resourcesave.js"></script>
</head>
<body>
<form onsubmit="return u5ResourceSave.begin(event)" action="meta2.php?typ=<?php echo $_GET['typ']?>&uri=meta.php" method="post" <?php if ($u5MetaIsCode) echo 'accept-charset="UTF-8"'; ?> name="form1" id="form1"><?php if ($u5MetaIsCode) echo '<input type="hidden" name="_u5_code_encoding" value="utf8" />'; ?>
  <h1>Metadata for <i><?php echo $_GET['name']?></i></h1>
  <p>
    <input title="[Ctrl+s]" type="submit" name="button2" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  </p>
  <h2 style="margin-bottom:5px"><?php echo $lan1name ?><br />
  </h2>
  <table id="lan1name" bgcolor="#FFFF99" width="100%"><?php require('checktyp.inc.php'); ?>
    <tr>
      <td>alt&nbsp;text</td>
      <td width="99%"><input type="hidden" name="name" value="<?php echo $_GET['name']?>">
      <input onchange="changes()" name="title_1" lang="<?php echo $lan1na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('title_1')?>" /></td>
    </tr>
    <tr>
      <td>short&nbsp;caption</td>
      <td><input onchange="changes()" name="desc_1" lang="<?php echo $lan1na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('desc_1')?>" /></td>
    </tr>
    <tr>
      <td>long&nbsp;caption</td>
      <td><textarea onchange="changes()" name="content_1" lang="<?php echo $lan1na ?>" style="width:98%" ><?php echo $u5MetaEscape('content_1')?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan2name ?><br />
  </h2>
  <table id="lan2name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>alt&nbsp;text</td>
      <td width="99%"><input onchange="changes()" name="title_2" lang="<?php echo $lan2na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('title_2')?>" /></td>
    </tr>
    <tr>
      <td>short&nbsp;caption</td>
      <td><input onchange="changes()" name="desc_2" lang="<?php echo $lan2na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('desc_2')?>" /></td>
    </tr>
    <tr>
      <td>long&nbsp;caption</td>
      <td><textarea onchange="changes()" name="content_2" lang="<?php echo $lan2na ?>" style="width:98%" ><?php echo $u5MetaEscape('content_2')?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan3name ?><br />
  </h2>
  <table id="lan3name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>alt&nbsp;text</td>
      <td width="99%"><input onchange="changes()" name="title_3" lang="<?php echo $lan3na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('title_3')?>" /></td>
    </tr>
    <tr>
      <td>short&nbsp;caption</td>
      <td><input onchange="changes()" name="desc_3" lang="<?php echo $lan3na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('desc_3')?>" /></td>
    </tr>
    <tr>
      <td>long&nbsp;caption</td>
      <td><textarea onchange="changes()" name="content_3" lang="<?php echo $lan3na ?>" style="width:98%" ><?php echo $u5MetaEscape('content_3')?></textarea></td>
    </tr>
  </table>
    <h2 style="margin-bottom:5px"><?php echo $lan4name ?><br />
    </h2>
    <table id="lan4name" bgcolor="#FFFF99" width="100%">
        <tr>
            <td>alt&nbsp;text</td>
            <td width="99%"><input onchange="changes()" name="title_4" lang="<?php echo $lan4na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('title_4')?>" /></td>
        </tr>
        <tr>
            <td>short&nbsp;caption</td>
            <td><input onchange="changes()" name="desc_4" lang="<?php echo $lan4na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('desc_4')?>" /></td>
        </tr>
        <tr>
            <td>long&nbsp;caption</td>
            <td><textarea onchange="changes()" name="content_4" lang="<?php echo $lan4na ?>" style="width:98%" ><?php echo $u5MetaEscape('content_4')?></textarea></td>
        </tr>
    </table>
    <h2 style="margin-bottom:5px"><?php echo $lan5name ?><br />
    </h2>
    <table id="lan5name" bgcolor="#FFFF99" width="100%">
        <tr>
            <td>alt&nbsp;text</td>
            <td width="99%"><input onchange="changes()" name="title_5" lang="<?php echo $lan5na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('title_5')?>" /></td>
        </tr>
        <tr>
            <td>short&nbsp;caption</td>
            <td><input onchange="changes()" name="desc_5" lang="<?php echo $lan5na ?>" type="text" style="width:98%" value="<?php echo $u5MetaEscape('desc_5')?>" /></td>
        </tr>
        <tr>
            <td>long&nbsp;caption</td>
            <td><textarea onchange="changes()" name="content_5" lang="<?php echo $lan5na ?>" style="width:98%" ><?php echo $u5MetaEscape('content_5')?></textarea></td>
        </tr>
    </table>
  <p>
    <input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>

  </p>
  <p></p>
<input type="hidden" name="coco" value="<?php echo isset($row_a['lastmut']) ? (int)$row_a['lastmut'] : 0 ?>" />
<?php require('t1.php') ?></form>
<script>
initchanges();
</script>
<script>document.form1.title_1.focus()</script>
<script src="emptylanhide.js.php"></script>
</body>
</html>
