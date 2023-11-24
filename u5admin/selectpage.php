<?php require_once('connect.inc.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <link rel="stylesheet" href="../r/csshot.css" type="text/css" title="base"/>
    <?php require 'backendcss.php' ?>
</head>
<style>
    body {

        font-size: 11px;
    }

    a {
        text-decoration: none;
    }

    textarea {
    <?php echo $cssbackendtextarea ?>
    }

    input {
    <?php echo $cssbackendinput ?>
    }
</style>

<body bgcolor="#D1EBF8" id="idbody" onload="loader()">
<form name="form1" method="post" action="savepage.php" target="save"
      onsubmit="changes=0;resetchanges();skipone();checkother()">
    <input type="hidden" name="skon" value="0"/>
    <table width="100%" bgcolor="#FFD6A8">
        <tr>
            <td>
<span title="select page to edit">
  <select name="page" onchange="gotopage(this.value)">
      <option value="_">&nbsp;</option>
      <?php
      $allnames = '';
      $sql_a = "SELECT name, deleted FROM resources WHERE typ='p' AND deleted!=1 AND name!='-' ORDER by deleted, name";
      $result_a = mysql_query($sql_a);

      if ($result_a == false) {
          echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
      }

      $num_a = mysql_num_rows($result_a);

      for ($i_a = 0; $i_a < $num_a; $i_a++) {
          $row_a = mysql_fetch_array($result_a);
          $selected = '';
          if ($_GET['c'] == $row_a['name']) $selected = 'selected="selected"';

          if ($row_a['deleted'] == 2) $isarchived = '(a) ';
          else $isarchived = '';

          if ($hidearchivedpagesindropdown != 'no') {
              if ($isarchived == '' || $_COOKIE['shrchv'] == 1 || $row_a['name'] == $_GET['c']) echo '<option value="' . $row_a['name'] . '" ' . $selected . '>' . $isarchived . $row_a['name'] . '</option>';
          } else echo '<option value="' . $row_a['name'] . '" ' . $selected . '>' . $isarchived . $row_a['name'] . '</option>';
          $allnames .= ',' . $row_a['name'] . ',';
      }
      ?>
  </select>
</span>
<span style="display:none">
<input onclick="manlview=1;lview('1')" name="view" type="radio" value="1"/>
<input onclick="manlview=1;lview('2')" name="view" type="radio" value="2"/>
<input onclick="manlview=1;lview('3')" name="view" type="radio" value="3"/>
<input onclick="manlview=1;lview('4')" name="view" type="radio" value="4"/>
<input onclick="manlview=1;lview('5')" name="view" type="radio" value="5"/>
</span>
<?php require('t1.php') ?></form>
</td>

</tr></table>
<script>
    function lgotopage(that) {
        gotopage(that);
    }
	function gotopage(that) {
        parent.i2.location.href = 'editor.php?c=' + that;
        parent.i1.location.href = 'editor.php?c=' + that;
    }
    function doteleins() {
        alert('You have to select a page in the editor(s) before inserting objects.');
    }
    function loader() {

    }
</script>
</body>
</html>