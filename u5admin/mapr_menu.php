<?php 
setcookie('fd2p', $_GET['p'], time()+3600*24*365*10,'/');
require_once('connect.inc.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body id="body" style="font-size:14px">
&nbsp;&nbsp;&nbsp;&nbsp;&#9993;&nbsp;&nbsp;&nbsp;&nbsp;
<button onclick="location.replace('?p=<?php echo (htmlspecialchars($_GET['p'])-1) ?>')">&lt;</button>
<input onchange="location.replace('?p='+this.value)" type="text" size="4" id="pos" value="<?php echo htmlspecialchars($_GET['p']) ?>" /> / <span id="num"></span>
<button onclick="location.replace('?p=<?php echo (htmlspecialchars($_GET['p'])+1) ?>')">&gt;</button>
<script>
if(parent && parent.parent && parent.parent.me && parent.parent.me.document.getElementById('loaded')) parent.parent.me.evokepreview();
</script>
</body>
</html>
