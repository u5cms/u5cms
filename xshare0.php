<?php require('connect.inc.php')?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<?php
$url0=st($_GET['link']);
$url=$scripturi.'?'.$_SERVER['QUERY_STRING'];
$title=st($_GET['name']);
$description=st($_GET['description']);
$image=st($_GET['picture']);

function st($s) {
global $quotehandling;
//if($quotehandling!='on')$s=stripslashes($s);
return $s;
}


?>
<meta property="og:image" content="<?php echo htmlY($image) ?>" />
<meta property="og:url" content="<?php echo htmlY($url) ?>" />
<meta property="og:title" content="<?php echo htmlY($title) ?>" />
<meta property="og:description" content="<?php echo htmlY($description) ?>" />
<meta name="description" content="<?php echo htmlY($description) ?>" />
<title><?php echo htmlY($title) ?></title>
</head>
<body>
<img src="<?php echo htmlY($image) ?>" />
<?php echo htmlY($description); ?>
<script>
if(window.name!='xshareWIN')location.href='<?php echo htmlY($url0)?>';
else location.href="https://www.facebook.com/sharer/sharer.php?s=100&p[title]=<?php echo rawurlencode($title) ?>&p[summary]=<?php echo rawurlencode($description) ?>&p[url]=<?php echo rawurlencode($url) ?>&p[images][0]=<?php echo rawurlencode($image) ?>";
</script>
<?php
function htmlY($s) {
$s=htmlXspecialchars(utf8_decode($s));
$s=str_replace('&amp;#','&#',$s);
return $s;
}
?>
</body>
</html>