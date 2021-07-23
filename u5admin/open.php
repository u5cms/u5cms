<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>

<body style="background-color: #E6FFC4;">
<h1>open...</h1>
Do you want to open
<br />
<br />
<?php echo $_GET['typ']?>: <b><?php echo $_GET['name']?></b> ?
<br />
<br />
<a href="pidvesa.php">no/back</a>
&nbsp;
<?php if ($_GET['typ']!='c'&&$_GET['typ']!='x') { ?>
<a title="metadata" href="javascript:void(0)" onclick="f1=window.open('meta<?php echo $_GET['typ']?>.php?typ=<?php echo $_GET['typ']?>&name=<?php echo $_GET['name']?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">M</a>

<?php if ($_GET['typ']=='a') { ?>
<a title="upload" href="javascript:void(0)" onclick="f1=window.open('uploada.php?typ=<?php echo $_GET['typ']?>&name=<?php echo $_GET['name']?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">U</a>
<?php } ?>

<?php if ($_GET['typ']=='i' || $_GET['typ']=='f' || $_GET['typ']=='d' || $_GET['typ']=='v') { ?>
<a title="upload" href="javascript:void(0)" onclick="f1=window.open('upload.php?typ=<?php echo $_GET['typ']?>&name=<?php echo $_GET['name']?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">U</a>
<?php } ?>
<?php } ?>


<?php if ($_GET['typ']=='c') { ?>
<a href="javascript:void(0)" onclick="f1=window.open('coding.php?name=<?php echo $_GET['name']?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=1200,height=900');">yes</a>
<?php } ?>

<?php if ($_GET['typ']=='x') { ?>
<a title="edit login instructions" href="javascript:void(0)" onclick="f1=window.open('meta<?php echo $_GET['typ']?>.php?typ=<?php echo $_GET['typ']?>&name=<?php echo $_GET['name']?>','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">yes</a>
<?php } ?>

&nbsp;
</body>
</html>
