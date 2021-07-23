<?php
if ($ignoremanualfullpaths != 'yes') {
$sql_a = "UPDATE loginglobals SET loginintro_d=REPLACE(loginintro_d,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginintro_e=REPLACE(loginintro_e,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginintro_f=REPLACE(loginintro_f,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginoutro_d=REPLACE(loginoutro_d,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginoutro_e=REPLACE(loginoutro_e,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginoutro_f=REPLACE(loginoutro_f,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources_log SET content_d=REPLACE(content_d,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources_log SET content_e=REPLACE(content_e,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources_log SET content_f=REPLACE(content_f,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginintro_d=REPLACE(loginintro_d,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginintro_e=REPLACE(loginintro_e,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginintro_f=REPLACE(loginintro_f,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginoutro_d=REPLACE(loginoutro_d,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginoutro_e=REPLACE(loginoutro_e,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE loginglobals SET loginoutro_f=REPLACE(loginoutro_f,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources_log SET content_d=REPLACE(content_d,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources_log SET content_e=REPLACE(content_e,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

$sql_a = "UPDATE resources_log SET content_f=REPLACE(content_f,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';

if($alsorenamelinksinformdatadatacsv=='yes') {
$sql_a = "UPDATE formdata SET datacsv=REPLACE(datacsv,'c=".$search."\\\\','c=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
$sql_a = "UPDATE formdata SET datacsv=REPLACE(datacsv,'n=".$search."\\\\','n=".$replace."\\\\')";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}
}
?>