<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="css/story.css" type="text/css" title="base" />
</head>
<body>
<?php 
require_once('connect.inc.php');
$sql_a="SELECT * FROM resources WHERE name='".mysql_real_escape_string($_GET['y'])."'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

   $row_a = mysql_fetch_array($result_a);
?>

<div id="story_main">

<h1  id="story_title">
<?php echo ehtml(def($row_a['title_d'],$row_a['title_e'],$row_a['title_f'])) ?></span>
</h1>

<div  id="story_text">
<?php echo ehtml(def($row_a['content_d'],$row_a['content_e'],$row_a['content_f'])) ?>
</div>

<div id="story_image">
<img src="<?php echo 'r/t'.$row_a['name'].'btm/t'.$row_a['name'].'btm.png' ?>" />
</div>

</div>
</body>
</html>
