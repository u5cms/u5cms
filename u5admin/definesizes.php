<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>define sizes</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body id="body">
<iframe name="save" frameborder="0" width="0" height="0"></iframe>
<?php
$sql_a="SELECT * FROM sizes";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);

?>
<h1>Define sizes </h1>
<form name="form1" method="post" action="definesizes2.php" target="save">
<script>
function nonly(string) {
    for (var i=0, output='', valid="0123456789"; i<string.length; i++)
           if (valid.indexOf(string.charAt(i)) != -1)
                     output += string.charAt(i)
                         return output;
                         }

</script>
  <p>Define the sizes of players and  images in pixels [px]</p>

  <h4>Local video player <!--vidllarge--></h4>

<font face="monospace">h</font><input name="videoplayer_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['videoplayer_h']) ?>"><br>
<font face="monospace">w</font><input name="videoplayer_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['videoplayer_w']) ?>"><br><br>
<h4>Local audio player <!--audllarge--></h4>
<font face="monospace">h</font><input name="audioplayer_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['audioplayer_h']) ?>"><br>
<font face="monospace">w</font><input name="audioplayer_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['audioplayer_w']) ?>"><br><br>
<h4>Embedded youtube player <!--yodllarge--></h4>
<font face="monospace">h</font><input name="youtube_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['youtube_h']) ?>"><br>
<font face="monospace">w</font><input name="youtube_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['youtube_w']) ?>"><br><br>
<h4>Thumbnails <!--thumbnailalbum-->
<select style="font-size:70%" name="tosquare"><option <?php if($row_a['tosquare']==1)echo'selected="selected"'?> value="1">square</option><option <?php if($row_a['tosquare']==0)echo'selected="selected"'?> value="0">ratio</option></select>
<small>crop</small><input style="font-size:70%" name="cropedge" type="text" size="4" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['cropedge']) ?>">
produced by the syntax [::::::::albumname]</h4>
<font face="monospace">h</font><input name="smallimgL_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['smallimgL_h']) ?>"><br><br>
<h4>Left aligned small inline image <!--ldlsmall--></h4>
<font face="monospace">w</font><input name="smallimgL_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['smallimgL_w']) ?>"><br><br>
<h4>Center aligned small inline image <!--cdlsmall--></h4>
<!--<font face="monospace">h</font><input name="smallimgL_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['smallimgC_h']) ?>"><br>-->
<font face="monospace">w</font><input name="smallimgC_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['smallimgC_w']) ?>"><br><br>
<h4>Right aligned small inline image <!--rdlsmall--></h4>
<!--<font face="monospace">h</font>
<input name="smallimgR_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['smallimgR_h']) ?>">
<br>-->
<font face="monospace">w</font><input name="smallimgR_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['smallimgR_w']) ?>"><br><br>
<h4>Large inline image <!--imdllarge--></h4>
<!--<font face="monospace">h</font><input name="largeimg_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['largeimg_h']) ?>"><br>-->
<font face="monospace">w</font><input name="largeimg_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['largeimg_w']) ?>"><br><br>
<h4>Image max. sizes when zoomed out after clicking</h4>
<font face="monospace">h</font><input name="zoomedimg_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['zoomedimg_h']) ?>" /><br>
<font face="monospace">w</font><input name="zoomedimg_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['zoomedimg_w']) ?>" /><br><br>
<h4>Size of horizontal scrollable album <!--aldllarge--></h4>
<font face="monospace">h</font><input name="scrollingalbum_h" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['scrollingalbum_h']) ?>" /><br>
<font face="monospace">w</font><input name="scrollingalbum_w" type="text" size="90" onchange="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" onkeyup="if (this.value!=nonly(this.value)) this.value=nonly(this.value)" value="<?php echo ehtml($row_a['scrollingalbum_w']) ?>" /><br>
<br />
<h4>
  <input type="submit" name="button" value="save" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
  </h4>
</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?php require('t1.php') ?></form>

<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>