<?php
require_once('connect.inc.php');
require_once('chglang.inc.php');
require_once('aclan.inc.php');
require_once ('render.inc.php');
require_once('addreadrights.inc.php');

if ($_GET['p']=='1' && $executephp=='inarchiveonly') {
$sql_a="SELECT deleted FROM resources WHERE name='".mysql_real_escape_string($_GET['c'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);
$delstatgetc=$row_a['deleted'];
}

$row_a['name']='logo';
include('getfile.inc.php');
$template=str_replace('[_logo_]','r/logo/'.def($file_d,$file_e,$file_f).'?t='.filemtime('r/logo/'.def($file_d,$file_e,$file_f)),file_get_contents('r/htmltemplate.css'));

if ($_GET['p']=='1') $template=str_replace('{{{content}}}',render($_POST['r']),$template);

else $template = str_replace('</html>','',str_replace('</body>','<div style="width:30px;height:30px;position:absolute;top:0;left:0;z-index:999;" onclick="if (typeof clickycorner === \'undefined\') clickycorner=0;clickycorner++;if(clickycorner>1){window.open(\'edit.php?' . ehtml($_SERVER['QUERY_STRING']) . '\');clickycorner=0}"><img src="clickycorner.gif" /></div>',$template)).'</body>
</html>';

$i_i_item=explode('{{{',$template);

for ($i_i_i=0;$i_i_i<tnuoc($i_i_item);$i_i_i++) {

   if (strpos($i_i_item[$i_i_i],'}}}')>1) {

      $i_i_part=explode('}}}',$i_i_item[$i_i_i]);

if ($i_i_part[0]=='content') {
if ($_GET['c']=='_search') $i_i_part[0]='_search';
else if ($_GET['c']=='_searchsi') $i_i_part[0]='_searchsi';
else if ($_GET['c']=='_sitemap') $i_i_part[0]='_sitemap';
}

      if (file_exists('u5sys.'.$i_i_part[0].'.php')) include('u5sys.'.$i_i_part[0].'.php');
	  else {
	  $anyname=$i_i_part[0];
	  include('u5sys.any.php');
	  }

if ($_GET['p']=='1') {
if ($executephp=='onallpages' || ($executephp=='inarchiveonly' && $delstatgetc==2)) echo eval('?>'.$i_i_part[1]);
else echo $i_i_part[1];
}
else echo $i_i_part[1];
	  }

   else echo $i_i_item[$i_i_i];
}

?>
<div id="genericformdataeditorlinkdiv" style="display:none;position:absolute;top:0px;margin-left:40%;height:100px;width:200px;background:lightgreen;font-family:Arial, Helvetica, sans-serif;font-size:12px;font-weight:bold">
<div style="float:right;color:white;background:black;cursor:pointer" onclick="this.parentNode.style.display='none'">×</div>
<div style="padding:5px"><span style="color:red">Read carefully:</span> If this page does not display an appropriate form to edit your data (no form, missing fields, wrong fields), go to the <a style="color:blue;text-decoration:underline" href="formdataeditgen.php?<?php echo str_replace('c=','n=',$_SERVER['QUERY_STRING'])?>">generic formdata editor</a>.</div>
</div>
<script>if(location.href.indexOf('a=1')>0)document.getElementById('genericformdataeditorlinkdiv').style.display='block';</script>