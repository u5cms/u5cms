<?php
require_once('connect.inc.php');
require_once('chglang.inc.php');
require_once('aclan.inc.php');
require_once ('render.inc.php');
if(isset($_GET['q'])) $_GET['q']=str_replace('</script',' /script',str_replace(' ',',',trim(str_replace(',',' ',$_GET['q']))));
if ($_COOKIE['hp']=='1' && $_GET['p']!='') require_once('hilite.inc.php');

if ($_GET['p']=='1' || $_GET['p']=='2' ) {
$donotloadu5adminconfig=1;
require_once('u5admin/usercheck.inc.php');
require_once('getinserts.inc.php');
}
else require_once('login.inc.php');

if ($_GET['p']=='1') {
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ERROR | E_PARSE | E_DEPRECATED);
   }

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

if ($_GET['p']=='1') {
    echo'<script>u5prvldd=0;setTimeout("if(u5prvldd==0&&location.href.indexOf(\'u5prvldd=1\')<0&&parent.window.name==\'i1\')parent.parent.i2.phperror();if(u5prvldd==0&&location.href.indexOf(\'u5prvldd=1\')<0&&parent.window.name==\'i2\')parent.parent.i1.phperror();if(u5prvldd==0){location.href=location.href.replace(/\\\?u5prvldd=1&/,\'?\').replace(/\\\?/,\'?u5prvldd=1&\')}",1111)</script>';

if ($_GET['u5prvldd']==1)echo'<script>document.cookie=\'aclan=; expires=Thu, 31 Dec 2037 12:00:00 GMT\';</script><iframe scrolling="no" id="fu5prvldd" name="fu5prvldd" frameborder="0" style="width:150%;height:100%;overflow:visible;position:absolute;top:0;left:0;z-index:9999999"></iframe>
<script>
if(parent.window.name==\'i1\')document.getElementById(\'fu5prvldd\').src=\'index.php?c=-&l=\'+parent.parent.parent.i2.form1.view.value;
if(parent.window.name==\'i2\')document.getElementById(\'fu5prvldd\').src=\'index.php?c=-&l=\'+parent.parent.parent.i1.form1.view.value;
</script>';

	$template=str_replace('{{{content}}}',render($_POST['r']),$template);
    $template=preg_replace('/<!--(.*)-->/Uis', '', $template);
   }

else $template = str_replace('</html>','',str_replace('</body>','<div id="u5clkycrnr" style="width:30px;height:30px;position:absolute;top:0;left:0;z-index:999;" onclick="if (typeof clickycorner === \'undefined\') clickycorner=0;clickycorner++;if(clickycorner>1){window.open(\'edit.php?n='.htmlspecialchars($_GET['n']).'&c='.htmlspecialchars($_GET['c']).'&l='.htmlspecialchars($_GET['l']).'\');clickycorner=0}"><img src="clickycorner.gif" /></div>',$template)).'</body>
<!-- This site runs in u5CMS V8.3.5. Get u5CMS for free at http://www.yuba.ch/u5cms -->
</html>';

$i_i_item=explode('{{{',$template);

for ($i_i_i=0;$i_i_i<tnuoc($i_i_item);$i_i_i++) {

   if (strpos($i_i_item[$i_i_i],'}}}')>1) {

      $i_i_part=explode('}}}',$i_i_item[$i_i_i]);

if ($i_i_part[0]=='content') {
if ($_GET['c']=='_search') $i_i_part[0]='_search';
else if ($_GET['c']=='_searchsi') $i_i_part[0]='_searchsi';
else if ($_GET['c']=='_sitemap') $i_i_part[0]='_sitemap';
else if ($_GET['c'] == '_login') $i_i_part[0] = '_login';
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

if ($_GET['p']=='1') {	echo'</textarea>';	include('preview.inc.php');	}
if ($_GET['p']=='2') echo '<script type="text/javascript" src="r/pviscroll.js?t='.filemtime('r/pviscroll.js').'"></script>';
?>