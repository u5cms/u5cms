<?php

require('connect.inc.php');

$convmap = array(0x0100, 0xFFFF, 0, 0xFFFF);
$encoded_utf8 = mb_encode_numericentity($_GET['y'], $convmap, 'UTF-8');
$encoded_latin1 = utf8_decode($encoded_utf8);
$y=trim(mysql_real_escape_string($encoded_latin1));

if ($y=='') {
    echo'<script>alert("ERROR: No new mailjob started because subject empty.");location.href="mailinglist.php"</script>';
} else {
    $sql_a="INSERT INTO mailing (mailsubject,mailsavedop,mailto,mailcc,mailbcc,mailtext,mailsaved,mailsent,mailsentop,maildeleted,mailsentto,mailsentts,mailtested) VALUES ('$y','".mysql_real_escape_string($_SERVER['PHP_AUTH_USER'].' '.$_SERVER['REMOTE_ADDR'])."','','','','',0,0,'',0,'','',0)";
    $result_a=mysql_query($sql_a);

    if ($result_a==false) die('SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>');
    $id = mysql_insert_id();

    trxlog("new mj $id");

    echo '<script>
          parent.me.location.href="mailingeditor.php?n='.$_GET['n'].'&id='.$id.'&t='.$_GET['t'].'";
          location.href="mailinglist.php?n='.$_GET['n'].'&t='.$_GET['t'].'";
          </script>';
}
