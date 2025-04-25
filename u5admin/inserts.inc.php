<style>
#ins a {
text-decoration:none;
}
#ins a:hover 
{
font-decoration:underline;
background:white;
}
</style>
<span id="ins">
<?php 
$sql_b="SELECT * FROM inserts ORDER by cat, human";
$result_b=mysql_query($sql_b);

if ($result_b==false) {
echo 'SQL_b-Query failed!...!<p>';
}

$num = mysql_num_rows($result_b)+1;


?>
	
	<a style="cursor:pointer;color:blue" onclick="f1=window.open('new.php?typ=p','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">Create new Page</a>,
 &nbsp; <a style="cursor:pointer;color:blue" onclick="pubctrlonoff()">Publicity status</a> (Public, Offline, Logins)
 <?php 
$sql_b="SELECT * FROM inserts ORDER by cat, human";
$result_b=mysql_query($sql_b);

if ($result_b==false) {
echo 'SQL_b-Query failed!...!<p>';
}

$num_b = mysql_num_rows($result_b);

$oldcat='';
for ($i_b=0; $i_b<$num_b; $i_b++) {
   $row_b = mysql_fetch_array($result_b);

if ($i_b<$num){
$row_b['cat']=$row_b['cat'].': ';
if ($row_b['cat']=='X: ') $row_b['cat']='';
if ($oldcat!=$row_b['cat'] && $row_b['cat']!=': ') {
if(strpos($row_b['cat'],'et')>0) echo '<br>'.str_replace(': ','',$row_b['cat']).': '
.'<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.rawurlencode('[_mymail_]').'\')">'.'&#120236;&#120248;&#120236;'.'</a> <span style="color:white;font-weight:bold">|</span> '
.'<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.rawurlencode('[_myurl_]').'\')">'.'&#120236;&#120248;&#120244;'.'</a> <span style="color:white;font-weight:bold">|</span> '
.'<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.rawurlencode('[_language_]').'\')">'.'&#120235;&#120224;&#120237;'.'</a> <span style="color:white;font-weight:bold">|</span> '
.'<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.rawurlencode('[_pagename_]').'\')">'.'&#120239;&#120224;&#120230;'.'</a> <span style="color:white;font-weight:bold">|</span> '
.'<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.rawurlencode('[_pagename!_]').'\')">'.'&#120239;&#120224;<small>!</small>'.'</a> <span style="color:white;font-weight:bold">|</span> ';
else echo '<br>'.$row_b['cat'];
}
 echo '<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.rawurlencode($row_b['insstring']).'\')">'.$row_b['human'].'</a>';
 if ($row_b['human']!='') echo ' <span style="color:white;font-weight:bold">|</span> ';      
}
$oldcat=$row_b['cat'];
if($i_b==46)echo'<a href="javascript:void(0)" onclick="u1=window.open(\'tools.php?w=\'+window.outerWidth+\'&h=\'+window.outerHeight,\'_blank\',\'toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=640,height=640\');" title="tools">&#128736;</a>';
}
include('more.inc.php');
?>
</span>
