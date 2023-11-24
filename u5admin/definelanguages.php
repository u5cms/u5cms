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
/ 4th :
<input name="lan4na" type="text" id="lan4na" onchange="if (this.value!=aaz(this.value)) this.value=aaz(this.value);if(this.value.length!=2 && this.value.length!=0){this.value='';alert('Must be 2 letters long!')}" onkeyup="if (this.value!=aaz(this.value)) this.value=aaz(this.value)" value="<?php echo ehtml($row_a['lan4na'])?>" size="2" maxlength="2" />
/ 5th :
<input name="lan5na" type="text" id="lan5na" onchange="if (this.value!=aaz(this.value)) this.value=aaz(this.value);if(this.value.length!=2 && this.value.length!=0){this.value='';alert('Must be 2 letters long!')}" onkeyup="if (this.value!=aaz(this.value)) this.value=aaz(this.value)" value="<?php echo ehtml($row_a['lan5na'])?>" size="2" maxlength="2" />
<div style="font-size:60%;margin-left:222px">swapping languages does not swap content!</div>
</p>
  <h3>Name of the language in the language itself (e. g. fran&ccedil;ais for french). <br />
   </h3>
     This string is echoed in the language selection menu offered to your site visitors.</p>
  <p>1st language:
       <input size="7" name="lan1name" type="text" id="lan1name" value="<?php echo ehtml($row_a['lan1name'])?>" />
 / 2nd:<input size="7" name="lan2name" type="text" id="lan2name" value="<?php echo ehtml($row_a['lan2name'])?>" />
 / 3rd:<input size="7" name="lan3name" type="text" id="lan3name" value="<?php echo ehtml($row_a['lan3name'])?>" />
 / 4th:<input size="7" name="lan4name" type="text" id="lan4name" value="<?php echo ehtml($row_a['lan4name'])?>" />
 / 5th:<input size="7" name="lan5name" type="text" id="lan5name" value="<?php echo ehtml($row_a['lan5name'])?>" />
</p>
<input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>

<br /><br /><br />

<h3>Define some system messages <small>(goto <a href="defineloginglobals.php">login instructions</a>)</small></h3>
<h4>Search</h4>
1<input name="recherche_1" type="text" size="90" value="<?php echo ehtml($row_a['recherche_1']) ?>"><br>
2<input name="recherche_2" type="text" size="90" value="<?php echo ehtml($row_a['recherche_2']) ?>"><br>
3<input name="recherche_3" type="text" size="90" value="<?php echo ehtml($row_a['recherche_3']) ?>"><br>
4<input name="recherche_4" type="text" size="90" value="<?php echo ehtml($row_a['recherche_4']) ?>"><br>
5<input name="recherche_5" type="text" size="90" value="<?php echo ehtml($row_a['recherche_5']) ?>"><br>
<h4>Please enter search term</h4>
1<input name="term_1" type="text" size="90" value="<?php echo ehtml($row_a['term_1']) ?>"><br>
2<input name="term_2" type="text" size="90" value="<?php echo ehtml($row_a['term_2']) ?>"><br>
3<input name="term_3" type="text" size="90" value="<?php echo ehtml($row_a['term_3']) ?>"><br>
4<input name="term_4" type="text" size="90" value="<?php echo ehtml($row_a['term_4']) ?>"><br>
5<input name="term_5" type="text" size="90" value="<?php echo ehtml($row_a['term_5']) ?>"><br>
<h4>hit containing all of the term</h4>
1<input name="andhit_1" type="text" size="90" value="<?php echo ehtml($row_a['andhit_1']) ?>"><br>
2<input name="andhit_2" type="text" size="90" value="<?php echo ehtml($row_a['andhit_2']) ?>"><br>
3<input name="andhit_3" type="text" size="90" value="<?php echo ehtml($row_a['andhit_3']) ?>"><br>
4<input name="andhit_4" type="text" size="90" value="<?php echo ehtml($row_a['andhit_4']) ?>"><br>
5<input name="andhit_5" type="text" size="90" value="<?php echo ehtml($row_a['andhit_5']) ?>"><br>
<h4>hits containing all of the term</h4>
1<input name="andhits_1" type="text" size="90" value="<?php echo ehtml($row_a['andhits_1']) ?>"><br>
2<input name="andhits_2" type="text" size="90" value="<?php echo ehtml($row_a['andhits_2']) ?>"><br>
3<input name="andhits_3" type="text" size="90" value="<?php echo ehtml($row_a['andhits_3']) ?>"><br>
4<input name="andhits_4" type="text" size="90" value="<?php echo ehtml($row_a['andhits_4']) ?>"><br>
5<input name="andhits_5" type="text" size="90" value="<?php echo ehtml($row_a['andhits_5']) ?>"><br>
<h4>hit containing parts of the term</h4>
1<input name="orhit_1" type="text" size="90" value="<?php echo ehtml($row_a['orhit_1']) ?>"><br>
2<input name="orhit_2" type="text" size="90" value="<?php echo ehtml($row_a['orhit_2']) ?>"><br>
3<input name="orhit_3" type="text" size="90" value="<?php echo ehtml($row_a['orhit_3']) ?>"><br>
4<input name="orhit_4" type="text" size="90" value="<?php echo ehtml($row_a['orhit_4']) ?>"><br>
5<input name="orhit_5" type="text" size="90" value="<?php echo ehtml($row_a['orhit_5']) ?>"><br>
<h4>hits containing parts of the term</h4>
1<input name="orhits_1" type="text" size="90" value="<?php echo ehtml($row_a['orhits_1']) ?>"><br>
2<input name="orhits_2" type="text" size="90" value="<?php echo ehtml($row_a['orhits_2']) ?>"><br>
3<input name="orhits_3" type="text" size="90" value="<?php echo ehtml($row_a['orhits_3']) ?>"><br>
4<input name="orhits_4" type="text" size="90" value="<?php echo ehtml($row_a['orhits_4']) ?>"><br>
5<input name="orhits_5" type="text" size="90" value="<?php echo ehtml($row_a['orhits_5']) ?>"><br>
<h4>No hits with your search. Suggestion:</h4>
1<input name="nohit_1" type="text" size="90" value="<?php echo ehtml($row_a['nohit_1']) ?>" /><br>
2<input name="nohit_2" type="text" size="90" value="<?php echo ehtml($row_a['nohit_2']) ?>" /><br>
3<input name="nohit_3" type="text" size="90" value="<?php echo ehtml($row_a['nohit_3']) ?>" /><br>
4<input name="nohit_4" type="text" size="90" value="<?php echo ehtml($row_a['nohit_4']) ?>" /><br>
5<input name="nohit_5" type="text" size="90" value="<?php echo ehtml($row_a['nohit_5']) ?>" /><br>
<br />
<h4>This page is currently not published. Please select from the menu</h4>
1<input name="notpub_1" type="text" size="90" value="<?php echo ehtml($row_a['notpub_1']) ?>" /><br>
2<input name="notpub_2" type="text" size="90" value="<?php echo ehtml($row_a['notpub_2']) ?>" /><br>
3<input name="notpub_3" type="text" size="90" value="<?php echo ehtml($row_a['notpub_3']) ?>" /><br>
4<input name="notpub_4" type="text" size="90" value="<?php echo ehtml($row_a['notpub_4']) ?>" /><br>
5<input name="notpub_5" type="text" size="90" value="<?php echo ehtml($row_a['notpub_5']) ?>" /><br>
<br />
<h4>Click to zoom</h4>
1<input name="czoom_1" type="text" size="90" value="<?php echo ehtml($row_a['czoom_1']) ?>" /><br>
2<input name="czoom_2" type="text" size="90" value="<?php echo ehtml($row_a['czoom_2']) ?>" /><br>
3<input name="czoom_3" type="text" size="90" value="<?php echo ehtml($row_a['czoom_3']) ?>" /><br>
4<input name="czoom_4" type="text" size="90" value="<?php echo ehtml($row_a['czoom_4']) ?>" /><br>
5<input name="czoom_5" type="text" size="90" value="<?php echo ehtml($row_a['czoom_5']) ?>" /><br>
<br />
<h4>images (click image to see more)</h4>
1<input name="picsfound_1" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_1']) ?>" /><br>
2<input name="picsfound_2" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_2']) ?>" /><br>
3<input name="picsfound_3" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_3']) ?>" /><br>
4<input name="picsfound_4" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_4']) ?>" /><br>
5<input name="picsfound_5" type="text" size="90" value="<?php echo ehtml($row_a['picsfound_5']) ?>" /><br>
<br />
<h4>There are more images, click to view</h4>
1<input name="morepics_1" type="text" size="90" value="<?php echo ehtml($row_a['morepics_1']) ?>" /><br>
2<input name="morepics_2" type="text" size="90" value="<?php echo ehtml($row_a['morepics_2']) ?>" /><br>
3<input name="morepics_3" type="text" size="90" value="<?php echo ehtml($row_a['morepics_3']) ?>" /><br>
4<input name="morepics_4" type="text" size="90" value="<?php echo ehtml($row_a['morepics_4']) ?>" /><br>
5<input name="morepics_5" type="text" size="90" value="<?php echo ehtml($row_a['morepics_5']) ?>" /><br>

<p></p>
<input title="[Ctrl+s]" type="submit" name="button" value="save &amp; close" /><span class="asterisk" style="display:none;color:red">*</span>
  <?php include('metachg.inc.php') ?><script>initchanges()</script>
        </p>
<?php require('t1.php') ?></form>
<script>
window.resizeTo(800, screen.availHeight);
</script>
<?php include('selfclose.inc.php')?>
</body>
</html>
