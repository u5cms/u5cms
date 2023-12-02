<?php require_once('connect.inc.php'); if($_GET['c']=='_')$_GET['c']=''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <link rel="stylesheet" href="../r/csshot.css" type="text/css" title="base"/>
    <script src="shortcut.js"></script>
    <script>
        ires = 0;
        mres = 0;
        pres = 0;
		manlview = 0;
        shortcut.add("Ctrl+S", function () {
			onsubmitfunction();
            document.form1.submit();            
        })

        shortcut.add("Ctrl+I", function () {
            insertonoff();
        })

        shortcut.add("Ctrl+M", function () {
            u1 = window.open('characters.php?w=' + window.outerWidth + '&h=' + window.outerHeight, '_blank', 'toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=640,height=640');
        })

        shortcut.add("Ctrl+F12", function () {
            if (lansel != 'P') window.open('../?c=' + document.form1.page.value + '&l=' + lansel); else {
                if (window.name == 'i2') window.open('../?c=' + document.form1.page.value + '&l=' + parent.i1.lansel); else  window.open('../?c=' + document.form1.page.value + '&l=' + parent.i2.lansel)
            }
            ;
        })

        shortcut.add("Ctrl+F11", function () {
            window.open('rename.php?name=<?php echo $_GET['c']?>', '_blank', 'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');
        })

        shortcut.add("Ctrl+F10", function () {
            window.open('localize.php?name=<?php echo $_GET['c']?>', '_blank', 'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999');
        })

        shortcut.add("Ctrl+F9", function () {
            parent.i3.location.href = 'focus.php?c=<?php echo $_GET['c']?>';
        })
iOS=(/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream)
    </script>
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
    -webkit-overflow-scrolling: touch;
	}

    input {
    <?php echo $cssbackendinput ?>
    }
</style>

<body style="background:#D1EBF8;padding:1px;margin:1px" id="idbody" onload="loader()">
<script>
    changes = 0;
    function skipone() {
        if (parent && parent.i2 && parent.i2.form1.skon) parent.i2.form1.skon.value = '1';
        if (parent && parent.i1 && parent.i1.form1.skon) parent.i1.form1.skon.value = '1';
    }

    function checkother() {
        if (parent.i1.changes) {
            if (parent.i2.changes) {
                if (parent.i1.form1.page.value == parent.i2.form1.page.value) {
                    if (window.name == 'i1') {
                        parent.i2.changes = 0;
                        parent.i2.resetchanges();
                    }
                    if (window.name == 'i2') {
                        parent.i1.changes = 0;
                        parent.i1.resetchanges();
                    }
                }
            }
        }
    }

    function checkotherchanges() {
        if (parent.i1.document.getElementById('savebutton')) {
            if (parent.i2.document.getElementById('savebutton')) {
                if (parent.i1.form1.page.value == parent.i2.form1.page.value) {
                    if (parent.i1.document.getElementById('savebutton').innerHTML.indexOf('*') > 0) parent.i2.document.getElementById('savebutton').innerHTML = 's<span id="star" style="color:red">*</span>';
                    if (parent.i2.document.getElementById('savebutton').innerHTML.indexOf('*') > 0) parent.i1.document.getElementById('savebutton').innerHTML = 's<span id="star" style="color:red">*</span>';
                }
            }
        }
    }
isphperror=0;
function phperror() {
isphperror=1;
document.form1.action='phperror.php';
document.form1.submit();
isphperror=0;
document.form1.action='savepage.php';
}
function onsubmitfunction() {
document.form1.action=document.form1.action.split('?')[0]+'?i='+window.name;
}
function postsubmitfunction() {
if(isphperror!=1){changes=0;resetchanges();skipone();checkother()}	
}
</script>

<form name="form1" method="post" action="savepage.php" target="save"
      onsubmit="onsubmitfunction()">
    <input type="hidden" name="skon" value="0"/>
    <table width="100%" bgcolor="#FFD6A8">
        <tr>


            <td>
<span id="nosync" title="no sync of page selectors">
ns
<input
    onchange="if (window.name=='i2') {parent.i4e.location.replace('cookie.php?a=ns&b='+document.form1.ns.checked);if(document.form1.ns.checked) alert('WARNING: You have activated the ns checkbox. As long as this option is checked, the left and the right page selector are no longer automatically synchronized!')}"
    type="checkbox" name="ns" <?php if ($_COOKIE['ns'] == 'true') echo 'checked="checked"' ?> />
</span>
                <script>if (window.name == 'i1') document.getElementById('nosync').style.display = 'none'</script>


            </td>

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
</span><script>tasyerr=''</script><span title="NOT GOOD: tag asymmetry!" id="coblel" class="blink_me" style="color:red;font-weight:bold;cursor:pointer" onclick="alert(tasyerr)"></span>
                <?php include('editor.hic.inc.php'); ?>
            </td>

            <td>
                <?php $onechar = false;
                $unique_lans = array_unique(array($lan1na[0], $lan2na[0], $lan3na[0], $lan4na[0], $lan5na[0]));
                if (sizeof($unique_lans) == 5) $onechar = true; ?>
                <span
                    title="Click a radio (<?php echo $onechar ? $lan1na[0] : $lan1na ?>, <?php echo $onechar ? $lan2na[0] : $lan2na ?>, <?php echo $onechar ? $lan3na[0] : $lan3na ?>, <?php echo $onechar ? $lan4na[0] : $lan4na ?> or <?php echo $onechar ? $lan5na[0] : $lan5na ?>) to choose a language or click the P-radio to wysiwyg preview this page in the nighbor editor area.">
<input onclick="manlview=1;lview('1')" name="view" type="radio" value="1"/><span
                        id="sl01"><?php echo $onechar ? $lan1na[0] : $lan1na ?></span>
<input onclick="manlview=1;lview('2')" name="view" type="radio" value="2"/><span
                        id="sl02"><?php echo $onechar ? $lan2na[0] : $lan2na ?></span>
<input onclick="manlview=1;lview('3')" name="view" type="radio" value="3"/><span
                        id="sl03"><?php echo $onechar ? $lan3na[0] : $lan3na ?></span>
<input onclick="manlview=1;lview('4')" name="view" type="radio" value="4"/><span
                        id="sl04"><?php echo $onechar ? $lan4na[0] : $lan4na ?></span>
<input onclick="manlview=1;lview('5')" name="view" type="radio" value="5"/><span
                        id="sl05"><?php echo $onechar ? $lan5na[0] : $lan5na ?></span>
<input
    title="Click the radio for wysiwyg preview. Doubleclick the radio to highlight paragraphs. Click the letter P to go to the live web page"
    ondblclick="hilitp()" onclick="lview('P');preload()" name="view" type="radio" value="P" id="pvradio"/><script>if (parent.location.href.indexOf('?i') > 1)document.getElementById('pvradio').style.display = 'none'</script><a
                        title="[Ctrl+F12] open real website" href="javascript:void(0)"
                        onclick="if (lansel!='P') window.open('../?c='+document.form1.page.value+'&l='+lansel); else {if (window.name=='i2') window.open('../?c='+document.form1.page.value+'&l='+parent.i1.lansel); else  window.open('../?c='+document.form1.page.value+'&l='+parent.i2.lansel)}">P</a>
</span>
                <span id="frlcd"><?php require('frlcd.inc.php') ?></span>
            </td>

            <td>
<span title="[Ctrl+i] insert page, formats and special characters">
<button id="insertbutton" type="button" onclick="insertonoff()">i</button>
</span>
                <span id="previewtext" style="display:none">Preview only, no edit</span>
            </td>


            <td>
<span title="edit metadata (title, description, keywords)">
<button id="metabutton" type="button" onclick="metaonoff()">m</button>
</span>
            </td>

            <td>
<span title="[Ctrl+s] save changes (does save all three languages of the page in this editor area)">
<button id="savebutton" type="submit">s&nbsp;</button>
</span>
                </span>
            </td>

            <td>

<span id="msync" title="re-sync wysiwyg preview and editor (if you have been surfing in the wysiwyg preview)">
<a href="javascript:void(0)" onclick="mansync()">R</a>
</span>
                <script>if (window.name == 'i2') document.getElementById('msync').style.display = 'none'</script>

            </td>

        </tr>
    </table>
    <?php
    $sql_a = "SELECT * FROM resources WHERE name='" . mysql_real_escape_string($_GET['c']) . "'";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
    $row_a = mysql_fetch_array($result_a);
    ?>
    <input type="hidden" name="ishomepage" value="<?php echo $row_a['ishomepage'] ?>">

    <div id="pubctrl" style="background:#F7DC73;display:none">
        <br>Publicity status of this page (concerns all languages)
        <select id="idhidden" name="hidden" onchange="dosync(window.name,this.id,this.value);mychanges();">
            <option value="0" <?php if ($row_a['hidden'] == 0) echo 'selected="selected"' ?>>public, indexing ok
            </option>
            <option value="1" <?php if ($row_a['hidden'] == 1) echo 'selected="selected"' ?>>offline</option>
            <option value="-1" <?php if ($row_a['hidden'] == -1) echo 'selected="selected"' ?>>public, no indexing
            </option>
            <option value="2" <?php if ($row_a['hidden'] == 2) echo 'selected="selected"' ?>>htaccess forcer only
            </option>
        </select>
        <script>help = "INFO ABOUT THE PUBLICITY STATUS\n\npublic, indexing ok\n\nThe page is surfable on the web and it will be indexed by the internal and the external (e.g. Google) search engines. If you set logins for this page, they apply independently of the publicity status, and indexing cannot be done by external search engines; the internal search engine will handle indexing of login-protected content according to the rules formulated in config.php.\n\n\n\noffline\n\nThe page is not surfable (and therefore, not indexed).\n\n\n\npublic, no indexing\n\nThe page is surfable on the web but not indexed by the internal search engine. For external search engines the no-index-meta-tag is presented.\n\n\n\nhtaccess forcer only\n\nThis one is very special and needs some clarification. First of all, a page with publicity status 'htaccess forcer only' is not surfable on the web and it will not be indexed. Its purpose is to protect your files (e.g. PDFs). Background: If you set up a page (e.g. page1) in your u5CMS and you protect it with one or several logins, the internal files (e.g. cookbook.pdf) represented as links on this page are protected with the same login(s). Let\'s now say that cookbook.pdf is represented (additionally) with a link on one of your other pages (e.g. page2) and this page is also protected with logins. Effect: cookbook.pdf is still ONLY surfable with a login, but either login of page1 and page2 will give access to cookbook.pdf. But what if cookbook.pdf is linked to on a third page (e.g. page3), but page3 has no login? This property will be inherited by cookbook.pdf, which means it is no longer protected by any login. Does this make sense? Yes, because if you link to a file with general content (e.g. conditions.pdf or intro.m4v) from a login-protected page, this file should not be blocked on non-login-protected pages that already might contain the link to it. If you need to change this mechanism in a way that a file (e.g. classified.pdf) is always login-protected, just set up a dummy page with publicity status 'htaccess forcer only' and link classified.pdf to this dummy page (of course it must be linked to 'real' pages, too; otherwise classified.pdf would be useless. You can link as many files to the dummy page as you like). What are its effects? classified.pdf can never be opened without login. Which logins will give access to classified.pdf? Firstly, the one(s) set on the dummy page, and secondly, any other login of a page containing a link to classified.pdf.";</script>
        <a href="javascript:alert(help)">Help</a>
        <br><br>

        <?php
        if (substr($_GET['c'], 0, 1) == '!') echo 'You cannot enter any logins here because this is an intranet page (indication: !-pagename) for which all intranet members are managed under the \'A\'-radiobutton of your PIDVESA-radiobutton-navigation in the upper right corner of the CMS-backend.';
        else echo 'Usernames &amp; passwords for all language versions of this page. One pair per line e.g. <font color=red><b>?</b></font>smith<font color=red><b>:</b></font>xy123<font color=red><b>;</b></font> (red delimiters are mandatory)';
        ?>

        <center><textarea id="idlogins" name="logins" rows="7" style="width:90%" onkeydown="tov=this.value"
                          onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                          onmouseover="tov=this.value"
                          onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"><?php echo ehtml($row_a['logins']) ?></textarea>
        </center>
        <?php
        if (substr($_GET['c'], 0, 1) == '!') echo '<script>document.getElementById("idlogins").style.display="none";</script>';
        ?>

        <br>
    </div>


    <div id="d_1" style="display:none">
        <div id="insert1" style="background:#D0F4B0;display:none">
            <?php $lanhere = '1';
            include('inserts.inc.php') ?>
        </div>
        <div id="meta1" style="display:none">
            <table bgcolor="#FFFF99" width="100%">
                <tr>
                    <td>Title</td>
                    <td width="99%"><input name="title_1" lang="<?php echo $lan1na ?>" type="text" id="t11" style="width:100%"
                                           onkeydown="tov=this.value"
                                           onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                                           onmouseover="tov=this.value"
                                           onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                                           value="<?php echo ehtml($row_a['title_1']) ?>"/></td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td><input name="desc_1" lang="<?php echo $lan1na ?>" type="text" id="t12" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['desc_1']) ?>"/></td>
                </tr>
                <tr>
                    <td>Keywords</td>
                    <td><input name="key_1" lang="<?php echo $lan1na ?>" type="text" id="t13" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['key_1']) ?>"/></td>
                </tr>
            </table>
        </div>
        <textarea onkeydown="tov=this.value;if(event.keyCode == 9){doins('1','\t');return false};"
                  onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                  onmouseover="tov=this.value"
                  onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                  name="content_1" lang="<?php echo $lan1na ?>" id="ta1"
                  style="width:100%;height:1000px"><?php echo ehtml($row_a['content_1']) ?></textarea>
    </div>

    <div id="d_2" style="display:none">
        <div id="insert2" style="background:#D0F4B0;display:none">
            <?php $lanhere = '2';
            include('inserts.inc.php') ?>
        </div>
        <div id="meta2" style="display:none">
            <table bgcolor="#FFFF99" width="100%">
                <tr>
                    <td>Title</td>
                    <td width="99%"><input name="title_2" lang="<?php echo $lan2na ?>" type="text" id="t21" style="width:100%"
                                           onkeydown="tov=this.value"
                                           onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                                           onmouseover="tov=this.value"
                                           onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                                           value="<?php echo ehtml($row_a['title_2']) ?>"/></td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td><input name="desc_2" lang="<?php echo $lan2na ?>" type="text" id="t22" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['desc_2']) ?>"/></td>
                </tr>
                <tr>
                    <td>Keywords</td>
                    <td><input name="key_2" lang="<?php echo $lan2na ?>" type="text" id="t23" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['key_2']) ?>"/></td>
                </tr>
            </table>
        </div>
        <textarea onkeydown="tov=this.value;if(event.keyCode == 9){doins('2','\t');return false};"
                  onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                  onmouseover="tov=this.value"
                  onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                  name="content_2" lang="<?php echo $lan2na ?>" id="ta2"
                  style="width:100%;height:1000px"><?php echo ehtml($row_a['content_2']) ?></textarea>
    </div>

    <div id="d_3" style="display:none">
        <div id="insert3" style="background:#D0F4B0;display:none">
            <?php $lanhere = '3';
            include('inserts.inc.php') ?>
        </div>
        <div id="meta3" style="display:none">
            <table bgcolor="#FFFF99" width="100%">
                <tr>
                    <td>Title</td>
                    <td width="99%"><input name="title_3" lang="<?php echo $lan3na ?>" type="text" id="t31" style="width:100%"
                                           onkeydown="tov=this.value"
                                           onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                                           onmouseover="tov=this.value"
                                           onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                                           value="<?php echo ehtml($row_a['title_3']) ?>"/></td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td><input name="desc_3" lang="<?php echo $lan3na ?>" type="text" id="t32" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['desc_3']) ?>"/></td>
                </tr>
                <tr>
                    <td>Keywords</td>
                    <td><input name="key_3" lang="<?php echo $lan3na ?>" type="text" id="t33" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['key_3']) ?>"/></td>
                </tr>
            </table>
        </div>
        <textarea onkeydown="tov=this.value;if(event.keyCode == 9){doins('3','\t');return false};"
                  onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                  onmouseover="tov=this.value"
                  onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                  name="content_3" lang="<?php echo $lan3na ?>" id="ta3"
                  style="width:100%;height:1000px"><?php echo ehtml($row_a['content_3']) ?></textarea>
    </div>

    <div id="d_4" style="display:none">
        <div id="insert4" style="background:#D0F4B0;display:none">
            <?php $lanhere = '4';
            include('inserts.inc.php') ?>
        </div>
        <div id="meta4" style="display:none">
            <table bgcolor="#FFFF99" width="100%">
                <tr>
                    <td>Title</td>
                    <td width="99%"><input name="title_4" lang="<?php echo $lan4na ?>" type="text" id="t41" style="width:100%"
                                           onkeydown="tov=this.value"
                                           onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                                           onmouseover="tov=this.value"
                                           onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                                           value="<?php echo ehtml($row_a['title_4']) ?>"/></td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td><input name="desc_4" lang="<?php echo $lan4na ?>" type="text" id="t42" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['desc_4']) ?>"/></td>
                </tr>
                <tr>
                    <td>Keywords</td>
                    <td><input name="key_4" lang="<?php echo $lan4na ?>" type="text" id="t43" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['key_4']) ?>"/></td>
                </tr>
            </table>
        </div>
        <textarea onkeydown="tov=this.value;if(event.keyCode == 9){doins('4','\t');return false};"
                  onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                  onmouseover="tov=this.value"
                  onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                  name="content_4" lang="<?php echo $lan4na ?>" id="ta4"
                  style="width:100%;height:1000px"><?php echo ehtml($row_a['content_4']) ?></textarea>
    </div>

    <div id="d_5" style="display:none">
        <div id="insert5" style="background:#D0F4B0;display:none">
            <?php $lanhere = '5';
            include('inserts.inc.php') ?>
        </div>
        <div id="meta5" style="display:none">
            <table bgcolor="#FFFF99" width="100%">
                <tr>
                    <td>Title</td>
                    <td width="99%"><input name="title_5" lang="<?php echo $lan5na ?>" type="text" id="t51" style="width:100%"
                                           onkeydown="tov=this.value"
                                           onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                                           onmouseover="tov=this.value"
                                           onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                                           value="<?php echo ehtml($row_a['title_5']) ?>"/></td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td><input name="desc_5" lang="<?php echo $lan5na ?>" type="text" id="t52" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['desc_5']) ?>"/></td>
                </tr>
                <tr>
                    <td>Keywords</td>
                    <td><input name="key_5" lang="<?php echo $lan5na ?>" type="text" id="t53" style="width:100%" onkeydown="tov=this.value"
                               onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                               onmouseover="tov=this.value"
                               onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                               value="<?php echo ehtml($row_a['key_5']) ?>"/></td>
                </tr>
            </table>
        </div>
        <textarea onkeydown="tov=this.value;if(event.keyCode == 9){doins('5','\t');return false};"
                  onkeyup="dosync(window.name,this.id,this.value);if (this.value!=tov) {mychanges()};"
                  onmouseover="tov=this.value"
                  onmouseout="if (this.value!=tov) {mychanges();dosync(window.name,this.id,this.value)};"
                  name="content_5" lang="<?php echo $lan5na ?>" id="ta5"
                  style="width:100%;height:1000px"><?php echo ehtml($row_a['content_5']) ?></textarea>
    </div>

    <div id="d_P" style="display:none">
        <?php
        if ($_COOKIE['i1_l'] != 'P') $lansel = $_COOKIE['i1_l'];
        else $lansel = $_COOKIE['i2_l'];
        ?>
        <iframe width="100%" height="1000" frameborder="0" id="pframe2" name="pframe2" src="../index.php?p=2"></iframe>
        <iframe width="100%" height="0" frameborder="0" id="pframe" name="pframe"
                src="../index.php?i=1&p=1&c=<?php echo $_GET['c'] ?>&l=<?php echo $lansel ?>"></iframe>
    </div>
    <input type="hidden" name="coco" value="<?php echo time() ?>"/>
<?php require('t1.php') ?></form>


<script>
    function mychanges() {
        changes++;
        if (changes > 0 && parent.save.location.href.indexOf('notsaved.php') < 0) parent.save.location.replace('notsaved.php');
        document.getElementById('savebutton').innerHTML = 's<span id="star" style="color:red">*</span>';
        checkotherchanges();
    }

    function doteleins(string) {
        doins(lansel, '[' + string + '] ');
    }

    function doins(lanhere, that) {
        if (lanhere == '1') insertAtCursor(document.form1.content_1, that);
        if (lanhere == '2') insertAtCursor(document.form1.content_2, that);
        if (lanhere == '3') insertAtCursor(document.form1.content_3, that);
        if (lanhere == '4') insertAtCursor(document.form1.content_4, that);
        if (lanhere == '5') insertAtCursor(document.form1.content_5, that);

        if (lanhere == '1') dosync2(window.name, 'ta1', that);
        if (lanhere == '2') dosync2(window.name, 'ta2', that);
        if (lanhere == '3') dosync2(window.name, 'ta3', that);
        if (lanhere == '4') dosync2(window.name, 'ta4', that);
        if (lanhere == '5') dosync2(window.name, 'ta5', that);
        pview();
        setTimeout("pview()", 999);
//window.scrollTo(0,0);
    }

    function insertAtCursor(myField, myValue) {
        keepscrolltop = myField.scrollTop;
        //IE support
        myValue = unescape(myValue);
        if (document.selection) {
            myField.focus();
            sel = document.selection.createRange();
            sel.text = myValue;
        }
        //MOZILLA/NETSCAPE support
        else if (myField.selectionStart || myField.selectionStart == '0') {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            myField.value = myField.value.substring(0, startPos)
            + myValue
            + myField.value.substring(endPos, myField.value.length);
        } else {
            myField.value += myValue;
        }
        myField.focus();
        if (myField.setSelectionRange) myField.setSelectionRange(startPos + myValue.length, startPos + myValue.length);
        myField.scrollTop = keepscrolltop;
    }


    function replace(string, text, by) {
// Replaces text with by in string
        var strLength = string.length, txtLength = text.length;
        if ((strLength == 0) || (txtLength == 0)) return string;
        var i = string.indexOf(text);
        if ((!i) && (text != string.substring(0, txtLength))) return string;
        if (i == -1) return string;
        var newstr = string.substring(0, i) + by;
        if (i + txtLength < strLength)
            newstr += replace(string.substring(i + txtLength, strLength), text, by);
        return newstr;
    }

    res = 55;

    function loader() {
        resizer();
        readcookies();
        starthide();
    }

    function sizer() {
        if (parent.location.href.indexOf('?i') < 1 && !iOS) {
            document.getElementById('ta1').style.height = document.documentElement.clientHeight - res - ires - mres - pres + 'px';
            document.getElementById('ta2').style.height = document.documentElement.clientHeight - res - ires - mres - pres + 'px';
            document.getElementById('ta3').style.height = document.documentElement.clientHeight - res - ires - mres - pres + 'px';
            document.getElementById('ta4').style.height = document.documentElement.clientHeight - res - ires - mres - pres + 'px';
            document.getElementById('ta5').style.height = document.documentElement.clientHeight - res - ires - mres - pres + 'px';
            document.getElementById('pframe2').style.height = document.documentElement.clientHeight - res + 10 + 'px';
        }
    }

    function resizer() {
        if (parent.location.href.indexOf('?i') < 1 && !iOS) {
            if (document.getElementById('ta1').style.height != document.documentElement.clientHeight - res - ires - mres - pres) sizer();
            if (document.getElementById('ta2').style.height != document.documentElement.clientHeight - res - ires - mres - pres) sizer();
            if (document.getElementById('ta3').style.height != document.documentElement.clientHeight - res - ires - mres - pres) sizer();
            if (document.getElementById('ta4').style.height != document.documentElement.clientHeight - res - ires - mres - pres) sizer();
            if (document.getElementById('ta5').style.height != document.documentElement.clientHeight - res - ires - mres - pres) sizer();
            if (document.getElementById('pframe2').style.height != document.documentElement.clientHeight - res) sizer();
        }
        if (parent.i3) if (parent.i3.document.getElementById('a_<?php echo $_GET['c']?>')) {
            parent.i3.document.getElementById('a_<?php echo $_GET['c']?>').style.fontSize = '120%';
            parent.i3.document.getElementById('a_<?php echo $_GET['c']?>').style.background = 'yellow'
        }
        ;
        fnhic();

        if (document.form1.content_1.value.replace(/ /, '') == '') document.getElementById('sl01').style.color = '#aaa';
        else document.getElementById('sl01').style.color = 'black';

        if (document.form1.content_2.value.replace(/ /, '') == '') document.getElementById('sl02').style.color = '#aaa';
        else document.getElementById('sl02').style.color = 'black';

        if (document.form1.content_3.value.replace(/ /, '') == '') document.getElementById('sl03').style.color = '#aaa';
        else document.getElementById('sl03').style.color = 'black';

        if (document.form1.content_4.value.replace(/ /, '') == '') document.getElementById('sl04').style.color = '#aaa';
        else document.getElementById('sl04').style.color = 'black';

        if (document.form1.content_5.value.replace(/ /, '') == '') document.getElementById('sl05').style.color = '#aaa';
        else document.getElementById('sl05').style.color = 'black';


        setTimeout("resizer()", 333);
    }

    function metaonoff() {
        if (document.getElementById('meta1').style.display != 'block') {
            mres = 77;
            document.getElementById('meta1').style.display = 'block';
            document.getElementById('meta2').style.display = 'block';
            document.getElementById('meta3').style.display = 'block';
            document.getElementById('meta4').style.display = 'block';
            document.getElementById('meta5').style.display = 'block';
            document.cookie = 'metaonoff=on;path=/';
        }

        else {
            mres = 0;
            document.getElementById('meta1').style.display = 'none';
            document.getElementById('meta2').style.display = 'none';
            document.getElementById('meta3').style.display = 'none';
            document.getElementById('meta4').style.display = 'none';
            document.getElementById('meta5').style.display = 'none';
            document.cookie = 'metaonoff=off;path=/';
        }
    }

    function pubctrlonoff() {
        if (document.getElementById('pubctrl').style.display != 'block') {
            if(document.form1.page.value.substring(0,1)!='!')pres=228;
			else pres = 95;
			document.getElementById('pubctrl').style.display = 'block';
            document.cookie = 'pubctrlonoff=on;path=/';
        }

        else {
            pres  = 0;
			document.getElementById('pubctrl').style.display = 'none';
            document.cookie = 'pubctrlonoff=off;path=/';
        }
    }
    function insertonoff() {
        if (document.getElementById('insert1').style.display != 'block') {
            ires = 90;
            document.getElementById('insert1').style.display = 'block';
            document.getElementById('insert2').style.display = 'block';
            document.getElementById('insert3').style.display = 'block';
            document.getElementById('insert4').style.display = 'block';
            document.getElementById('insert5').style.display = 'block';
            document.cookie = 'insertonoff=on;path=/';
        }

        else {
            ires = 0; pres = 0;
            document.getElementById('insert1').style.display = 'none';
            document.getElementById('insert2').style.display = 'none';
            document.getElementById('insert3').style.display = 'none';
            document.getElementById('insert4').style.display = 'none';
            document.getElementById('insert5').style.display = 'none';
            document.getElementById('pubctrl').style.display = 'none';
            document.cookie = 'insertonoff=off;path=/';
        }
    }

    function pview() {
//if (parent.i1 && window.name=='i2' && parent.i1.document.form1.view[5].checked==true && parent.i1.pframe.pviewit) parent.i1.pframe.pviewit();
//if (parent.i2 && window.name=='i1' && parent.i2.document.form1.view[5].checked==true && parent.i2.pframe.pviewit) parent.i2.pframe.pviewit();
        lagp = 0;
    }

    lagp = -3;
    function lagpview() {
        lagp++;
        if (lagp == 2) {
            if (parent.i1 && window.name == 'i2' && parent.i1.document.form1.view[5].checked == true && parent.i1.pframe.pviewit) parent.i1.pframe.pviewit();
            if (parent.i2 && window.name == 'i1' && parent.i2.document.form1.view[5].checked == true && parent.i2.pframe.pviewit) parent.i2.pframe.pviewit();
        }
        setTimeout("lagpview()", 300);
    }

    function preload() {
        if (window.name == 'i1' && parent.i1.document.form1.view[5].checked == true && parent.i1.pframe.pviewit) parent.i1.pframe.pviewit();
        if (window.name == 'i2' && parent.i2.document.form1.view[5].checked == true && parent.i2.pframe.pviewit) parent.i2.pframe.pviewit();
    }

    function lview(that) {
        lansel = that;
        document.getElementById('d_1').style.display = 'none';
        document.getElementById('d_2').style.display = 'none';
        document.getElementById('d_3').style.display = 'none';
        document.getElementById('d_4').style.display = 'none';
        document.getElementById('d_5').style.display = 'none';
        document.getElementById('d_P').style.display = 'none';


        document.getElementById('d_' + that).style.display = 'block';

        if (that == 'P') {
            document.getElementById('insertbutton').style.display = 'none';
            document.getElementById('metabutton').style.display = 'none';
            document.getElementById('savebutton').style.display = 'none';
            document.getElementById('previewtext').style.display = 'block';
            document.getElementById('frlcd').style.display = 'none';
			if(document.getElementById('pubctrl').style.display=='block')pubctrlonoff();
        }

        else {
            document.getElementById('insertbutton').style.display = 'block';
            document.getElementById('metabutton').style.display = 'block';
            document.getElementById('savebutton').style.display = 'block';
            document.getElementById('previewtext').style.display = 'none';
            document.getElementById('frlcd').style.display = 'inline';

            if (manlview == 0) {
                if (document.cookie.indexOf('metaonoff=on') > 0) metaonoff();
                if (document.cookie.indexOf('insertonoff=on') > 0) insertonoff();
                if (document.cookie.indexOf('pubctrlonoff=on') > 0 && document.cookie.indexOf('insertonoff=on') > 0) pubctrlonoff();
            }
        }

        if (window.name == 'i1') parent.i4a.location.replace('cookie.php?a=' + window.name + '_l&b=' + that);
        if (window.name == 'i2') parent.i4d.location.replace('cookie.php?a=' + window.name + '_l&b=' + that);

        pview();
        setTimeout("pview()", 333);
        setTimeout("pview()", 666);
        setTimeout("pview()", 999);
    }

    wascf = 0;
    window.onbeforeunload = function () {
        if (wascf == 0 && changes > 0) {
            self.focus();
			if (parent.i1)if (parent.i1.document.getElementById('star'))parent.i1.document.getElementById('star').style.fontSize = '30px';
            if (parent.i2)if (parent.i2.document.getElementById('star'))parent.i2.document.getElementById('star').style.fontSize = '30px';
            return ('You have unsaved changes which will be lost if you are proceeding.');
        }
    }

    function lgotopage(that) {
	document.cookie='ns=true;path=/';
	parent.i2.form1.ns.checked=true;
	if(parent.i1.form1.view[5].checked) document.cookie='i1_l=1;path=/';
	gotopage(that);
	}
	
    function gotopage(that) {

        if (changes > 0) {
            if (parent.i1)if (parent.i1.document.getElementById('star'))parent.i1.document.getElementById('star').style.fontSize = '30px';
            if (parent.i2)if (parent.i2.document.getElementById('star'))parent.i2.document.getElementById('star').style.fontSize = '30px';
            cf = confirm('You have unsaved changes which will be lost if you are proceeding. Proceed anyway?');
            if (cf) {
                wascf = 1;
                if (window.name == 'i1') parent.i4b.location.replace('cookie.php?a=' + window.name + '_p&b=' + that);
                if (window.name == 'i2') parent.i4c.location.replace('cookie.php?a=' + window.name + '_p&b=' + that);

                location.href = 'editor.php?c=' + that;
                if (parent.i2.document.form1.ns.checked == false) parent.i2.location.href = 'editor.php?c=' + that;
                if (parent.i2.document.form1.ns.checked == false) parent.i1.location.href = 'editor.php?c=' + that;
                parent.save.location.replace('lost.php');
            }

            else document.form1.page.value = '<?php echo $_GET['c']?>';

        }
        else {

            if (window.name == 'i1') parent.i4b.location.replace('cookie.php?a=' + window.name + '_p&b=' + that);
            if (window.name == 'i2') parent.i4c.location.replace('cookie.php?a=' + window.name + '_p&b=' + that);

            location.href = 'editor.php?c=' + that;
            if (parent.i2.document.form1.ns.checked == false) parent.i2.location.href = 'editor.php?c=' + that;
            if (parent.i2.document.form1.ns.checked == false) parent.i1.location.href = 'editor.php?c=' + that;
        }
    }

    function readcookies() {
        if (window.name == 'i1') {
            if ('<?php echo $_COOKIE['i1_l']?>' == '1') lview('1');
            if ('<?php echo $_COOKIE['i1_l']?>' == '2') lview('2');
            if ('<?php echo $_COOKIE['i1_l']?>' == '3') lview('3');
            if ('<?php echo $_COOKIE['i1_l']?>' == '4') lview('4');
            if ('<?php echo $_COOKIE['i1_l']?>' == '5') lview('5');
            if ('<?php echo $_COOKIE['i1_l']?>' == 'P') lview('P');

            if ('<?php echo $_COOKIE['i1_l']?>' == '1') document.form1.view[0].checked = true;
            if ('<?php echo $_COOKIE['i1_l']?>' == '2') document.form1.view[1].checked = true;
            if ('<?php echo $_COOKIE['i1_l']?>' == '3') document.form1.view[2].checked = true;
            if ('<?php echo $_COOKIE['i1_l']?>' == '4') document.form1.view[3].checked = true;
            if ('<?php echo $_COOKIE['i1_l']?>' == '5') document.form1.view[4].checked = true;
            if ('<?php echo $_COOKIE['i1_l']?>' == 'P') document.form1.view[5].checked = true;
        }
        if (window.name == 'i2') {
            if ('<?php echo $_COOKIE['i2_l']?>' == '1') lview('1');
            if ('<?php echo $_COOKIE['i2_l']?>' == '2') lview('2');
            if ('<?php echo $_COOKIE['i2_l']?>' == '3') lview('3');
            if ('<?php echo $_COOKIE['i2_l']?>' == '4') lview('4');
            if ('<?php echo $_COOKIE['i2_l']?>' == '5') lview('5');
            if ('<?php echo $_COOKIE['i2_l']?>' == 'P') lview('P');

            if ('<?php echo $_COOKIE['i2_l']?>' == '1') document.form1.view[0].checked = true;
            if ('<?php echo $_COOKIE['i2_l']?>' == '2') document.form1.view[1].checked = true;
            if ('<?php echo $_COOKIE['i2_l']?>' == '3') document.form1.view[2].checked = true;
            if ('<?php echo $_COOKIE['i2_l']?>' == '4') document.form1.view[3].checked = true;
            if ('<?php echo $_COOKIE['i2_l']?>' == '5') document.form1.view[4].checked = true;
            if ('<?php echo $_COOKIE['i2_l']?>' == 'P') document.form1.view[5].checked = true;
        }
    }

    if (window.name == 'i1') parent.i4b.location.replace('cookie.php?a=' + window.name + '_p&b=' + document.form1.page.value);
    if (window.name == 'i2') parent.i4c.location.replace('cookie.php?a=' + window.name + '_p&b=' + document.form1.page.value);

    function dosync(w, i, v) {
        console.log(w, i, v);
        if (parent.i1.document.form1.page.value == parent.i2.document.form1.page.value) {
            if (w == 'i1') {
                parent.i2.document.getElementById(i).value = v;
            }
            if (w == 'i2') {
                parent.i1.document.getElementById(i).value = v;
            }
        }
        pview();
    }

    function dosync2(w, i, v) {
        console.log(w, i, v, 'dosync 2');
        if (parent.i1.document.form1.page.value == parent.i2.document.form1.page.value) {
            if (w == 'i1') {
                parent.i2.document.getElementById(i).value = parent.i1.document.getElementById(i).value;
                if (window.name == 'i1') mychanges();
            }
            if (w == 'i2') {
                parent.i1.document.getElementById(i).value = parent.i2.document.getElementById(i).value;
                if (window.name == 'i2') mychanges();
            }
        }
        pview();
    }
    <?php
    echo 'cleft="'.$_COOKIE['i1_p'].'";';
    echo 'cright="'.$_COOKIE['i2_p'].'";';
    echo 'getc="'.$_GET['c'].'";';
    ?>
    if (window.name == 'i1' && document.form1.page.value == '_') document.form1.page.value = cleft;
    if (window.name == 'i2' && document.form1.page.value == '_') document.form1.page.value = cright;

    if (window.name == 'i1' && document.form1.page.value != '_' && cleft != '' && getc == '') location.href = 'editor.php?c=' + cleft;
    if (window.name == 'i2' && document.form1.page.value != '_' && cright != '' && getc == '') location.href = 'editor.php?c=' + cright;

    function resetchanges() {
        if (window.name == 'i1') {
            parent.i1.resetchanges2();
            if (parent.i2.document.form1.page.value == document.form1.page.value) parent.i2.resetchanges2();
        }

        if (window.name == 'i2') {
            parent.i2.resetchanges2();
            if (parent.i1.document.form1.page.value == document.form1.page.value) parent.i1.resetchanges2();
        }

    }

    function resetchanges2() {
        changes = 0;
        document.getElementById('savebutton').innerHTML = 's&nbsp;';
    }

    function mansync() {
        optdst1 = parent.i1.pframe2.location.href.split('?')[1].replace(/n=/,'c=');
        optdst2 = parent.i2.pframe2.location.href.split('?')[1].replace(/n=/,'c=');
        optdst = '';
        if (('x' + optdst1).indexOf('c=') > 0) optdst = optdst1;
        if (('x' + optdst2).indexOf('c=') > 0) optdst = optdst2;
        if (optdst != '') top.location.href = '../edit.php?' + optdst;
        else top.location.href = '../edit.php?c=' + document.form1.page.value;
    }


    function replace(string, text, by) {
// Replaces text with by in string
        var strLength = string.length, txtLength = text.length;
        if ((strLength == 0) || (txtLength == 0)) return string;
        var i = string.indexOf(text);
        if ((!i) && (text != string.substring(0, txtLength))) return string;
        if (i == -1) return string;
        var newstr = string.substring(0, i) + by;
        if (i + txtLength < strLength)
            newstr += replace(string.substring(i + txtLength, strLength), text, by);
        return newstr;
    }

    function hilitp() {
        parent.i4e.location.href = 'hp.php';
    }

    function starthide() {
        if (document.form1.page.value == '_') {
            document.getElementById('d_1').style.display = 'none';
            document.getElementById('d_2').style.display = 'none';
            document.getElementById('d_3').style.display = 'none';
            document.getElementById('d_4').style.display = 'none';
            document.getElementById('d_5').style.display = 'none';
            document.getElementById('d_P').style.display = 'none';
        }
    }

    if (parent.i3) {
        if (parent.i3.document.getElementById('isloaded')) {
            parent.i3.location.reload();
        }
    }

    setTimeout("lagpview()", 1000);

</script>
<?php
if (!isset($monitorprospectivesaveconflictseverynseconds)) $monitorprospectivesaveconflictseverynseconds = '10';
if ($monitorprospectivesaveconflictseverynseconds > 0) echo '<iframe style="display:none" widht="0" height="0" frameborder="0" name="checkco"></iframe>
<script>
function fcheckco() {
if(document.form1.skon.value==0)checkco.location.replace("checkco.php?name=' . $_GET['c'] . '&coco="+document.form1.coco.value+"&changes="+changes);
else document.form1.skon.value=0;
setTimeout("fcheckco()",1000*' . $monitorprospectivesaveconflictseverynseconds . ');
}
setTimeout("fcheckco()",1000*' . $monitorprospectivesaveconflictseverynseconds . ');
</script>'; ?>
<script>
if (document.form1.page.value == '' || document.form1.page.value == '_') setTimeout("location.href = 'selectpage.php'",777);
if(getCookie(window.name+'edfo')-0>0)tareatextfo();
function countblockels() {
if(document.getElementById('idcoblel'))document.getElementById('idcoblel').src='countblockelements.php?c=<?php echo htmlentities($_GET['c'])?>&l1=<?php echo $lan1na ?>&l2=<?php echo $lan2na?>&l3=<?php echo $lan3na ?>&l4=<?php echo $lan4na ?>&l5=<?php echo $lan5na ?>';
}
setTimeout("countblockels()",777);
</script>
</body>
</html>