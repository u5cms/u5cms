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
echo 'SQL_a-Query failed!...!<p>';
}

   $row_a = mysql_fetch_array($result_a);
?>

<div id="story_main">

<h1  id="story_title">
<?php echo ehtml(def($row_a['title_1'],$row_a['title_2'],$row_a['title_3'],$row_a['title_4'],$row_a['title_5'])) ?></span>
</h1>

<div  id="story_text">
<?php echo ehtml(def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5'])) ?>
</div>

<div id="story_image">
<img src="<?php echo 'r/t'.$row_a['name'].'btm/t'.$row_a['name'].'btm.png' ?>" />
</div>

</div>
</body>
</html>
