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
  
  : <b>vBfn2PifzC8 </b>or<b> vBfn2PifzC8<span style="color:red">?</span>rel=0 </b>or<b> vBfn2PifzC8<span style="color:red">?</span>rel=0<span style="color:red">&amp;</span>autoplay=1</b></div><br />
  </h2>
  <table id="lan1name" bgcolor="#FFFF99" width="100%"><?php require('checktyp.inc.php'); ?>
    <tr>
      <td>title<font color="#FF0000">*</font></td>
      <td width="99%"><input type="hidden" name="name" value="<?php echo $_GET['name']?>">
      <input onchange="changes()" name="title_1" lang="<?php echo $lan1na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['title_1'])?>" /></td>
    </tr>
    <tr>
      <td>hash<font color="#FF0000">*</font></td>
      <td><input onchange="changes()" name="desc_1" lang="<?php echo $lan1na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['desc_1'])?>" /></td>
    </tr>
    <tr>
      <td>caption</td>
      <td><textarea onchange="changes()" name="content_1" lang="<?php echo $lan1na ?>"short&nbsp;caption style="width:98%" ><?php echo ehtml($row_a['content_1'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan2name ?><br />
  </h2>
  <table id="lan2name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>title<font color="#FF0000">*</font></td>
      <td width="99%"><input onchange="changes()" name="title_2" lang="<?php echo $lan2na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['title_2'])?>" /></td>
    </tr>
    <tr>
      <td>hash<font color="#FF0000">*</font></td>
      <td><input onchange="changes()" name="desc_2" lang="<?php echo $lan2na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['desc_2'])?>" /></td>
    </tr>
    <tr>
      <td>caption</td>
      <td><textarea onchange="changes()" name="content_2" lang="<?php echo $lan2na ?>"short&nbsp;caption style="width:98%" ><?php echo ehtml($row_a['content_2'])?></textarea></td>
    </tr>
  </table>
  <h2 style="margin-bottom:5px"><?php echo $lan3name ?><br />
  </h2>
  <table id="lan3name" bgcolor="#FFFF99" width="100%">
    <tr>
      <td>title<font color="#FF0000">*</font></td>
      <td width="99%"><input onchange="changes()" name="title_3" lang="<?php echo $lan3na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['title_3'])?>" /></td>
    </tr>
    <tr>
      <td>hash<font color="#FF0000">*</font></td>
      <td><input onchange="changes()" name="desc_3" lang="<?php echo $lan3na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['desc_3'])?>" /></td>
    </tr>
    <tr>
      <td>caption</td>
      <td><textarea onchange="changes()" name="content_3" lang="<?php echo $lan3na ?>"short&nbsp;caption style="width:98%" ><?php echo ehtml($row_a['content_3'])?></textarea></td>
    </tr>
  </table>
    <h2 style="margin-bottom:5px"><?php echo $lan4name ?><br />
    </h2>
    <table id="lan4name" bgcolor="#FFFF99" width="100%">
        <tr>
            <td>title<font color="#FF0000">*</font></td>
            <td width="99%"><input onchange="changes()" name="title_4" lang="<?php echo $lan4na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['title_4'])?>" /></td>
        </tr>
        <tr>
            <td>hash<font color="#FF0000">*</font></td>
            <td><input onchange="changes()" name="desc_4" lang="<?php echo $lan4na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['desc_4'])?>" /></td>
        </tr>
        <tr>
            <td>caption</td>
            <td><textarea onchange="changes()" name="content_4" lang="<?php echo $lan4na ?>"short&nbsp;caption style="width:98%" ><?php echo ehtml($row_a['content_4'])?></textarea></td>
        </tr>
    </table>
    <h2 style="margin-bottom:5px"><?php echo $lan5name ?><br />
    </h2>
    <table id="lan5name" bgcolor="#FFFF99" width="100%">
        <tr>
            <td>title<font color="#FF0000">*</font></td>
            <td width="99%"><input onchange="changes()" name="title_5" lang="<?php echo $lan5na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['title_5'])?>" /></td>
        </tr>
        <tr>
            <td>hash<font color="#FF0000">*</font></td>
            <td><input onchange="changes()" name="desc_5" lang="<?php echo $lan5na ?>" type="text"short&nbsp;caption style="width:98%" value="<?php echo ehtml($row_a['desc_5'])?>" /></td>
        </tr>
        <tr>
            <td>caption</td>
            <td><textarea onchange="changes()" name="content_5" lang="<?php echo $lan5na ?>"short&nbsp;caption style="width:98%" ><?php echo ehtml($row_a['content_5'])?></textarea></td>
        </tr>
    </table>
  <p>
    <input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>

  </p>
  <p></p>
<input type="hidden" name="coco" value="<?php echo time() ?>" />
<?php require('t1.php') ?></form>
<script>
initchanges();
</script>
<script>document.form1.title_1.focus()</script>
<script src="emptylanhide.js.php"></script>
<?php include('focuslinktitle.inc.php') ?>
<?php if($videoportalegyoutubeembedurl=='')$videoportalegyoutubeembedurl='//www.youtube-nocookie.com/embed/';?>
<script>

oldypre='';
document.getElementById('ypreview').innerHTML='';

function ypre() {

if(document.form1.desc_1.value.trim()+document.form1.desc_2.value.trim()+document.form1.desc_3.value.trim()+document.form1.desc_4.value.trim()+document.form1.desc_5.value.trim()!=oldypre){
document.getElementById('ypreview').innerHTML='';

if(document.form1.desc_1.value.replace(/\s/g,'')!=''){

if(document.form1.desc_1.value.indexOf('v=')>-1)document.form1.desc_1.value=document.form1.desc_1.value.split('v=')[document.form1.desc_1.value.split('v=').length-1];
if(document.form1.desc_1.value.indexOf('/')>-1)document.form1.desc_1.value=document.form1.desc_1.value.split('/')[document.form1.desc_1.value.split('/').length-1];
if(document.form1.desc_1.value.indexOf('&')>-1&&document.form1.desc_1.value.indexOf('?')<0)document.form1.desc_1.value=document.form1.desc_1.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_1.value.trim()+'" frameborder="0" allowfullscreen></iframe>';
}

if(document.form1.desc_2.value.replace(/\s/g,'')!=''){

if(document.form1.desc_2.value.indexOf('v=')>-1)document.form1.desc_2.value=document.form1.desc_2.value.split('v=')[document.form1.desc_2.value.split('v=').length-1];
if(document.form1.desc_2.value.indexOf('/')>-1)document.form1.desc_2.value=document.form1.desc_2.value.split('/')[document.form1.desc_2.value.split('/').length-1];
if(document.form1.desc_2.value.indexOf('&')>-1&&document.form1.desc_2.value.indexOf('?')<0)document.form1.desc_2.value=document.form1.desc_2.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_2.value.trim()+'" frameborder="0" allowfullscreen></iframe>';
}

if(document.form1.desc_3.value.replace(/\s/g,'')!=''){

if(document.form1.desc_3.value.indexOf('v=')>-1)document.form1.desc_3.value=document.form1.desc_3.value.split('v=')[document.form1.desc_3.value.split('v=').length-1];
if(document.form1.desc_3.value.indexOf('/')>-1)document.form1.desc_3.value=document.form1.desc_3.value.split('/')[document.form1.desc_3.value.split('/').length-1];
if(document.form1.desc_3.value.indexOf('&')>-1&&document.form1.desc_3.value.indexOf('?')<0)document.form1.desc_3.value=document.form1.desc_3.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_3.value.trim()+'" frameborder="0" allowfullscreen></iframe>';
}

if(document.form1.desc_4.value.replace(/\s/g,'')!=''){

if(document.form1.desc_4.value.indexOf('v=')>-1)document.form1.desc_4.value=document.form1.desc_4.value.split('v=')[document.form1.desc_4.value.split('v=').length-1];
if(document.form1.desc_4.value.indexOf('/')>-1)document.form1.desc_4.value=document.form1.desc_4.value.split('/')[document.form1.desc_4.value.split('/').length-1];
if(document.form1.desc_4.value.indexOf('&')>-1&&document.form1.desc_4.value.indexOf('?')<0)document.form1.desc_4.value=document.form1.desc_4.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_4.value.trim()+'" frameborder="0" allowfullscreen></iframe>';
}

if(document.form1.desc_5.value.replace(/\s/g,'')!=''){

if(document.form1.desc_5.value.indexOf('v=')>-1)document.form1.desc_5.value=document.form1.desc_5.value.split('v=')[document.form1.desc_5.value.split('v=').length-1];
if(document.form1.desc_5.value.indexOf('/')>-1)document.form1.desc_5.value=document.form1.desc_5.value.split('/')[document.form1.desc_5.value.split('/').length-1];
if(document.form1.desc_5.value.indexOf('&')>-1&&document.form1.desc_5.value.indexOf('?')<0)document.form1.desc_5.value=document.form1.desc_5.value.split('&')[0];

	document.getElementById('ypreview').innerHTML+='<iframe width="150" height="80" src="<?php echo ehtml($videoportalegyoutubeembedurl) ?>'+document.form1.desc_5.value.trim()+'" frameborder="0" allowfullscreen></iframe>';
}

oldypre=document.form1.desc_1.value.trim()+document.form1.desc_2.value.trim()+document.form1.desc_3.value.trim()+document.form1.desc_4.value.trim()+document.form1.desc_5.value.trim();
}

setTimeout("ypre()",777);

}
ypre();
</script>
</body>
</html>