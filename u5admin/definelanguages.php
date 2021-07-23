<?php require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>define languages</title>
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
	cchanges=0;document.form1.submit();
})
</script>
<?php require('backendcss.php'); ?></head>

<body>
<?php
$sql_a="SELECT * FROM languages";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);

?>
<h1>Define languages</h1>
<form onsubmit="cchanges=0" name="form1" method="post" action="definelanguages2.php">
<script>
function aaz(string) {
    for (var i=0, output='', valid="abcdefghijklmnopqrstuvwxyz"; i<string.length; i++)
           if (valid.indexOf(string.charAt(i)) != -1)
                     output += string.charAt(i)
                         return output;
                         }

</script>
  <p>Define up to 3 languages for your website. The first language entered is the fallback language.</p>
  <h3>&nbsp;</h3>
  <h3>Two letter language code, must be according to <a href="languagecodes.php" target="_blank">this list</a> (e. g. fr for french).</h3>
  <p> 1st language (is fallback):
    <input name="lan1na" type="text" id="lan1na" onchange="if (this.value!=aaz(this.value)) this.value=aaz(this.value);if(this.value.length!=2){this.value='';alert('Must be 2 letters long!')}" onkeyup="if (this.value!=aaz(this.value)) this.value=aaz(this.value)" value="<?php echo ehtml($row_a['lan1na'])?>" size="2" maxlength="2" />
/ 2nd :
<input name="lan2na" type="text" id="lan2na" onchange="if (this.value!=aaz(this.value)) this.value=aaz(this.value);if(this.value.length!=2 && this.value.length!=0){this.value='';alert('Must be 2 letters long!')}" onkeyup="if (this.value!=aaz(this.value)) this.value=aaz(this.value)" value="<?php echo ehtml($row_a['lan2na'])?>" size="2" maxlength="2" />
/ 3rd :
<input name="lan3na" type="text" id="lan3na" onchange="if (this.value!=aaz(this.value)) this.value=aaz(this.value);if(this.value.length!=2 && this.value.length!=0){this.value='';alert('Must be 2 letters long!')}" onkeyup="if (this.value!=aaz(this.value)) this.value=aaz(this.value)" value="<?php echo ehtml($row_a['lan3na'])?>" size="2" maxlength="2" /> 
&nbsp;&nbsp;&nbsp;<span style="font-size:60%">swapping languages does not swap content!</span>
</p>
  <h3>Name of the language in the language itself (e. g. fran&ccedil;ais for french). <br />
   </h3>
     This string is echoed in the language selection menu offered to your site visitors.</p>
  <p>1st language:
    <input name="lan1name" type="text" id="lan1name" value="<?php echo ehtml($row_a['lan1name'])?>" />
/ 2nd :
<input name="lan2name" type="text" id="lan2name" value="<?php echo ehtml($row_a['lan2name'])?>" />
/ 3rd :
<input name="lan3name" type="text" id="lan3name" value="<?php echo ehtml($row_a['lan3name'])?>" />
</p>
<input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>

<br /><br /><br />

<h3>Define some system messages <small>(goto <a href="defineloginglobals.php">login instructions</a>)</small></h3>
<h4>Search</h4>
1<input name="recherche_d" type="text" size="90" value="<?php echo ehtml($row_a['recherche_d']) ?>"><br>
2<input name="recherche_e" type="text" size="90" value="<?php echo ehtml($row_a['recherche_e']) ?>"><br>
3<input name="recherche_f" type="text" size="90" value="<?php echo ehtml($row_a['recherche_f']) ?>"><br>
<h4>Please enter search term</h4>
1<input name="term_d" type="text" size="90" value="<?php echo ehtml($row_a['term_d']) ?>"><br>
2<input name="term_e" type="text" size="90" value="<?php echo ehtml($row_a['term_e']) ?>"><br>
3<input name="term_f" type="text" size="90" value="<?php echo ehtml($row_a['term_f']) ?>"><br>
<h4>hit containing all of the term</h4>
1<input name="andhit_d" type="text" size="90" value="<?php echo ehtml($row_a['andhit_d']) ?>"><br>
2<input name="andhit_e" type="text" size="90" value="<?php echo ehtml($row_a['andhit_e']) ?>"><br>
3<input name="andhit_f" type="text" size="90" value="<?php echo ehtml($row_a['andhit_f']) ?>"><br>
<h4>hits containing all of the term</h4>
1<input name="andhits_d" type="text" size="90" value="<?php echo ehtml($row_a['andhits_d']) ?>"><br>
2<input name="andhits_e" type="text" size="90" value="<?php echo ehtml($row_a['andhits_e']) ?>"><br>
3<input name="andhits_f" type="text" size="90" value="<?php echo ehtml($row_a['andhits_f']) ?>"><br>
<h4>hit containing parts of the term</h4>
1<input name="orhit_d" type="text" size="90" value="<?php echo ehtml($row_a['orhit_d']) ?>"><br>
2<input name="orhit_e" type="text" size="90" value="<?php echo ehtml($row_a['orhit_e']) ?>"><br>
3<input name="orhit_f" type="text" size="90" value="<?php echo ehtml($row_a['orhit_f']) ?>"><br>
<h4>hits containing parts of the term</h4>
1<input name="orhits_d" type="text" size="90" value="<?php echo ehtml($row_a['orhits_d']) ?>"><br>
2<input name="orhits_e" type="text" size="90" value="<?php echo ehtml($row_a['orhits_e']) ?>"><br>
3<input name="orhits_f" type="text" size="90" value="<?php echo ehtml($row_a['orhits_f']) ?>"><br>
<h4>No hits with your search. Suggestion:</h4>
1<input name="nohit_d" type="text" size="90" value="<?php echo ehtml($row_a['nohit_d']) ?>" /><br>
2<input name="nohit_e" type="text" size="90" value="<?php echo ehtml($row_a['nohit_e']) ?>" /><br>
3<input name="nohit_f" type="text" size="90" value="<?php echo ehtml($row_a['nohit_f']) ?>" /><br>
<br />
<h4>This page is currently not published. Please select from the menu</h4>
1<input name="notpub_d" type="text" size="90" value="<?php echo ehtml($row_a['notpub_d']) ?>" /><br>
2<input name="notpub_e" type="text" size="90" value="<?php echo ehtml($row_a['notpub_e']) ?>" /><br>
3<input name="notpub_f" type="text" size="90" value="<?php echo ehtml($row_a['notpub_f']) ?>" /><br>
<br />
<h4>Click to zoom</h4>
1<input name="czoom_d" type="text" size="90" value="<?php echo ehtml($row_a['czoom_d']) ?>" /><br>
2<input name="czoom_e" type="text" size="90" value="<?php echo ehtml($row_a['czoom_e']) ?>" /><br>
3<input name="czoom_f" type="text" size="90" value="<?php echo ehtml($row_a['czoom_f']) ?>" /><br>
<br />
<h4>images (click image to see more)</h4>
1<input name="picsfound_d" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_d']) ?>" /><br>
2<input name="picsfound_e" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_e']) ?>" /><br>
3<input name="picsfound_f" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_f']) ?>" /><br>
<br />
<h4>There are more images, click to view</h4>
1<input name="morepics_d" type="text" size="90" value="<?php echo ehtml($row_a['morepics_d']) ?>" /><br>
2<input name="morepics_e" type="text" size="90" value="<?php echo ehtml($row_a['morepics_e']) ?>" /><br>
3<input name="morepics_f" type="text" size="90" value="<?php echo ehtml($row_a['morepics_f']) ?>" /><br>

<p></p>
<input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
        </p>
</form>
<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
