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
<form onsubmit="cchanges=0" action="meta2.php?typ=<?php echo $_GET['typ']?>&uri=metay.php" method="post" name="form1" id="form1">
  <p>
    <?php 

$sql_a="SELECT * FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}


$row_a = mysql_fetch_array($result_a);

?>
<div id="ypreview" style="float:right"></div>
  </p>
  <h1>Youtube video <i><?php echo $_GET['name']?></i></h1>
  <p>
    <input title="[Ctrl+s]" type="submit" name="button2" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  </p>
  <h2 style="margin-bottom:5px"><?php echo $lan1name ?><div style="float:right;font-size:40%">Hash examples
  
  <b><a href="javascript:void(0)" onclick="alert('About the GET-parameters following the questionmark:\n\nrel=0 means do not show related videos at the end of my video.\nautoplay=1 means switch autoplay on.\nBrowsers may overrule autoplay always or heuristically.')">?</a></b>
  
  : <b>vBfn2PifzC8 </b>or<b> vBfn2PifzC8<span style="color:red">?</span>rel=0 </b>or<b> vBfn2PifzC8<span style="color:red">?</span>rel=0<span style="color:red">&amp;</span>autoplay=1</div><br />
  </h2>
  <table id="lan1name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>title<font color="#FF0000">*</font></td>
      <td width="99%"><input type="hidden" name="name" value="<?php echo $_GET['name']?>">
      <input onchange="changes()" name="title_d" lang="<?php echo $lan1na ?>" type="text" id="t11" style="width:100%" value="<?php echo ehtml($row_a['title_d'])?>" /></td>
    </tr>
    <tr>
      <td>hash<font color="#FF0000">*</font></td>
      <td><input onchange="changes()" name="desc_d" lang="<?php echo $lan1na ?>" type="text" id="t12" style="width:100%" value="<?php echo ehtml($row_a['desc_d'])?>" /></td>
    </tr>
    <tr>
      <td>caption</td>
      <td><textarea onchange="changes()" name="content_d" lang="<?php echo $lan1na ?>" id="t13" style="width:100%" ><?php echo ehtml($row_a['content_d'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan2name ?><br />
  </h2>
  <table id="lan2name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>title<font color="#FF0000">*</font></td>
      <td width="99%"><input onchange="changes()" name="title_e" lang="<?php echo $lan2na ?>" type="text" id="title_e" style="width:100%" value="<?php echo ehtml($row_a['title_e'])?>" /></td>
    </tr>
    <tr>
      <td>hash<font color="#FF0000">*</font></td>
      <td><input onchange="changes()" name="desc_e" lang="<?php echo $lan2na ?>" type="text" id="t2" style="width:100%" value="<?php echo ehtml($row_a['desc_e'])?>" /></td>
    </tr>
    <tr>
      <td>caption</td>
      <td><textarea onchange="changes()" name="content_e" lang="<?php echo $lan2na ?>" id="t3" style="width:100%" ><?php echo ehtml($row_a['content_e'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan3name ?><br />
  </h2>
  <table id="lan3name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>title<font color="#FF0000">*</font></td>
      <td width="99%"><input onchange="changes()" name="title_f" lang="<?php echo $lan3na ?>" type="text" id="t4" style="width:100%" value="<?php echo ehtml($row_a['title_f'])?>" /></td>
    </tr>
    <tr>
      <td>hash<font color="#FF0000">*</font></td>
      <td><input onchange="changes()" name="desc_f" lang="<?php echo $lan3na ?>" type="text" id="t5" style="width:100%" value="<?php echo ehtml($row_a['desc_f'])?>" /></td>
    </tr>
    <tr>
      <td>caption</td>
      <td><textarea onchange="changes()" name="content_f" lang="<?php echo $lan3na ?>" id="t6" style="width:100%" ><?php echo ehtml($row_a['content_f'])?></textarea></td>
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
<script>document.form1.title_d.focus()</script>
<script src="emptylanhide.js.php"></script>
<?php include('focuslinktitle.inc.php') ?>
<?php if($videoportalegyoutubeembedurl=='')$videoportalegyoutubeembedurl='//www.youtube-nocookie.com/embed/';?>
<script>

oldypre='';
document.getElementById('ypreview').innerHTML='';

function ypre() {

if(document.form1.desc_d.value.trim()+document.form1.desc_e.value.trim()+document.form1.desc_f.value.trim()!=oldypre){
document.getElementById('ypreview').innerHTML='';

if(document.form1.desc_d.value.replace(/\s/g,'')!=''){

if(document.form1.desc_d.value.indexOf('v=')>-1)document.form1.desc_d.value=document.form1.desc_d.value.split('v=')[document.form1.desc_d.value.split('v=').length-1];
if(document.form1.desc_d.value.indexOf('/')>-1)document.form1.desc_d.value=document.form1.desc_d.value.split('/')[document.form1.desc_d.value.split('/').length-1];
if(document.form1.desc_d.value.indexOf('&')>-1&&document.form1.desc_d.value.indexOf('?')<0)document.form1.desc_d.value=document.form1.desc_d.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_d.value.trim()+'" frameborder="0" allowfullscreen></iframe>';	
}

if(document.form1.desc_e.value.replace(/\s/g,'')!=''){

if(document.form1.desc_e.value.indexOf('v=')>-1)document.form1.desc_e.value=document.form1.desc_e.value.split('v=')[document.form1.desc_e.value.split('v=').length-1];
if(document.form1.desc_e.value.indexOf('/')>-1)document.form1.desc_e.value=document.form1.desc_e.value.split('/')[document.form1.desc_e.value.split('/').length-1];
if(document.form1.desc_e.value.indexOf('&')>-1&&document.form1.desc_e.value.indexOf('?')<0)document.form1.desc_e.value=document.form1.desc_e.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_e.value.trim()+'" frameborder="0" allowfullscreen></iframe>';	
}

if(document.form1.desc_f.value.replace(/\s/g,'')!=''){

if(document.form1.desc_f.value.indexOf('v=')>-1)document.form1.desc_f.value=document.form1.desc_f.value.split('v=')[document.form1.desc_f.value.split('v=').length-1];
if(document.form1.desc_f.value.indexOf('/')>-1)document.form1.desc_f.value=document.form1.desc_f.value.split('/')[document.form1.desc_f.value.split('/').length-1];
if(document.form1.desc_f.value.indexOf('&')>-1&&document.form1.desc_f.value.indexOf('?')<0)document.form1.desc_f.value=document.form1.desc_f.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_f.value.trim()+'" frameborder="0" allowfullscreen></iframe>';	
}

oldypre=document.form1.desc_d.value.trim()+document.form1.desc_e.value.trim()+document.form1.desc_f.value.trim();
}

setTimeout("ypre()",777);

}
ypre();
</script>
</body>
</html>