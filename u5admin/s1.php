<?php require_once('connect.inc.php'); ?>
<?php $pidvesa = 's'; $donotshowtogglearchive=1;
require_once('pidvesacookie.inc.php');
eikooctes('pidvesa', $pidvesa, time() + 3600 * 24 * 365 * 10, '/'); 
eikooctes('subs', 's1', time() + 3600 * 24 * 365 * 10, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <script src="shortcut.js"></script>
    <?php include('repoctrls.inc.php'); ?>
    <?php require('backendcss.php'); ?></head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<?php include('pidvesadiv.inc.php'); include('pidvesanavi.inc.php'); ?>
<?php include('pidvesadiv.end.inc.php'); include('savehistory.inc.php'); ?>
<h1 style="margin-top:-55px">Special&nbsp;functions</h1>

<p>
    <!--  <button onClick="f1=window.open('formdata.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">form data</button> -->
    <button onClick="f1=window.open('formdata.php','_blank');">form data</button>
    <br/>
    View form-data as table, charts, CSV to Excel/SPSS etc.
    </p>
<p>
    <button
        onClick="f1=window.open('backup.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        backup content
    </button>
    <br/>
    Backup your content (database and uploaded files) on a regular basis. </p>
<p id="htmlandcode">
    <button onclick="location.href='c.php'">code (html template &amp; css)</button>
    <br/>
    Manage the htmltemplate and its css-files.</p>
<p>
    <button
        onClick="f1=window.open('titlefixum.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        define title fixum
    </button>
    <br/>
    The last part of the page titles for search engines is this fixum.</p>
<p>
    <button
        onclick="f1=window.open('ishomepage.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        define homepage
    </button>
    <br/>
    Define which page shall be displayed as your homepage.</p>
<p>
    <button
        onclick="f1=window.open('definelanguages.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        define languages
    </button>
    <br/>
    Define up to 3 languages for your website. The first language entered is the fallback language.</p>
<p>
    <button
        onclick="f1=window.open('definesizes.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999,top=0,left='+(screen.availWidth-800));">
        define sizes
    </button>
    <br/>
    Define the sizes of players and images in pixels.</p>
<p>
<?php if($fastdeleteforbidden=='yes') echo '<!--';?>
    <button onclick="document.cookie='u5cmsfastdelete=<?php if($_COOKIE['u5cmsfastdelete']==1)echo '0';else echo '1' ?>;path=/';location.reload()"><?php if($_COOKIE['u5cmsfastdelete']==1)echo 'disable';else echo 'enable' ?> fastdelete</button>
    <br/>
    Deleting objects without entering their names manually.</p>
<p><?php if($fastdeleteforbidden=='yes') echo '-->';?>
    <button onclick="location.href='pviscroll.php'">preview init scroll</button>
    <br/>
    Define the initial scroll position in the preview frame.</p>
<p>
    <button
        onclick="f1=window.open('kill.php','_blank','toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');">
        kill item
    </button>
    <br/>
    Deleted items are recoverable. Kill makes them disappear physically.</p>
<p>&nbsp;</p>
<p>
<?php include('fileandtextversions.inc.php') ?>
</body>
</html>
