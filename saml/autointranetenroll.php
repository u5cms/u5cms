<?php

require_once __DIR__ . '/connect.inc.php';

if (isset($_GET['truncate']) && $_GET['truncate'] == 'please') {
    $sql_a="UPDATE intranetmembers SET members = ''";
    echo 'Intranet members flushed';
    $result_a=mysql_query($sql_a);
    if ($result_a==false) {
        mysql_error();
    }
    exit;
}

if (!isset($samlattribs['emailaddress'])) {
    exit;
}

$safe_mail = strtolower(mysql_real_escape_string(str_replace(',&#0;@&#0;','',$samlattribs['emailaddress'])));

$sql_a="UPDATE intranetmembers SET members = CONCAT(members, '$safe_mail', ',') WHERE CONCAT(',', members) NOT LIKE CONCAT('%,', '$safe_mail', ',%')";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
    die('Login failed. Unable to add user to intranet members list');
}

// recalculate login field of intranet resources
$scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
$scriptFolder .= $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI']));
file_get_contents($scriptFolder . 'u5admin/htaccess.php');
