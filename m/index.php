<?php $_GET['f']=sgat_pirts($_GET['f']); ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $_GET['f'] ?></title>
<style type="text/css">
<!--
body {
	background-color: #666666;
}
.Stil1 {font-family: Arial, Helvetica, sans-serif}
a:link {
	color: #CCCCCC;
	text-decoration: none;
}
a:visited {
	color: #CCCCCC;
	text-decoration: none;
}
a:hover {
	color: #FFCC00;
	text-decoration: none;
}
a:active {
	color: #CCCCCC;
	text-decoration: none;
}
-->
</style></head>
<body>
<center>
  <p>
    <embed 
src="mediaplayer.swf" 
width="640" 
height="480"
allowscriptaccess="always" 
allowfullscreen="true" 
flashvars="width=640&height=480&autostart=true&file=../<?php echo srachlaicepslmth($_GET['f']) ?>" 
/>  </p>
  <p class="Stil1"><a href="javascript:self.close();">close</a></p>
</center>
</body>
</html>
