<?php
// Keep the exact selection: the shared bootstrap normalises POST resource names.
$homepageName = $_POST['name'] ?? '';
if (!is_string($homepageName)) $homepageName = $_POST['name'] = '';
require_once('connect.inc.php');
require_once __DIR__.'/resource-mutation.inc.php';
require_once('t2.php');
if ($definehomepageRqHIADRI!='no') require_once('accadmin.inc.php');
require_once('homepage-eligible.inc.php');

$eligible = u5HomepageEligible();
$escapedName = mysql_real_escape_string($homepageName);
// Only homepage switches share this short-lived lock. A lost connection releases it.
$lock = mysql_query("SELECT GET_LOCK(CONCAT('u5-homepage-', SHA1(DATABASE())), 2)");
if (!$lock || (int)mysql_result($lock, 0) !== 1) {
    http_response_code(503);
    die('Homepage selection is busy. Please try again.');
}
$target = "SELECT name, deleted FROM resources WHERE name='$escapedName' AND BINARY name=BINARY '$escapedName'
           AND $eligible ORDER BY deleted LIMIT 1";
$result = mysql_query($target);
if ($result === false) {
    http_response_code(500);
    die('Could not check the selected homepage.');
}
$page = mysql_fetch_assoc($result);
if (!$page) {
    http_response_code(400);
    die('Please reopen the homepage list and select a visible page.');
}

// The live target guards the entire change, including on legacy MyISAM tables.
// If it disappeared or became ineligible since the lookup, nothing is cleared.
$state = (int)$page['deleted'];
// MyISAM cannot roll back rows already written if a later row fails.
$previous = mysql_query("SELECT name, deleted FROM resources WHERE ishomepage=1");
if ($previous === false) {
    http_response_code(500);
    die('Could not read the current homepage.');
}
$oldKeys = array();
$alreadySelected = false;
while ($row = mysql_fetch_assoc($previous)) {
    $oldKeys[] = "(name='".mysql_real_escape_string($row['name'])."' AND deleted=".(int)$row['deleted'].")";
    if ($row['name'] === $page['name'] && (int)$row['deleted'] === $state) $alreadySelected = true;
}
$update = "UPDATE resources AS page JOIN resources AS target
           ON target.name='$escapedName' AND BINARY target.name=BINARY '$escapedName'
           AND target.deleted=$state AND target.typ='p' AND target.hidden=0
           SET page.ishomepage=(page.name=target.name AND page.deleted=target.deleted)
           WHERE page.ishomepage=1 OR (page.name=target.name AND page.deleted=target.deleted)";
if (mysql_query($update) === false) {
    http_response_code(500);
    // Restore only flags the failed statement actually changed, before removing a new one.
    $restored = !$oldKeys || mysql_query("UPDATE resources SET ishomepage=1 WHERE
        (ishomepage IS NULL OR ishomepage<>1) AND (".implode(' OR ', $oldKeys).")");
    if ($restored && !$alreadySelected) {
        $restored = mysql_query("UPDATE resources SET ishomepage=0 WHERE name='$escapedName'
                                 AND deleted=$state AND ishomepage=1");
    }
    if (!$restored) error_log('u5CMS could not restore the homepage flags after a database write failure.');
    die($restored ? 'Could not save the selected homepage.' : 'Homepage recovery failed. Please check the database.');
}
if (mysql_affected_rows() == 0) {
    $current = mysql_query("SELECT 1 FROM resources WHERE name='$escapedName' AND BINARY name=BINARY '$escapedName'
                            AND deleted=$state AND $eligible AND ishomepage=1");
    if ($current === false || !mysql_fetch_row($current)) {
        http_response_code($current === false ? 500 : 409);
        die('Could not confirm the selected homepage. Please reopen the homepage list.');
    }
}
trxlog('sethpto '.$homepageName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1252" />
<title>saving...</title>
<?php require('backendcss.php'); ?></head>
<body id="body" bgcolor="#009900">
<script>
var homepage = <?php echo json_encode(mb_convert_encoding($homepageName, 'UTF-8', 'Windows-1252'), JSON_HEX_TAG) ?>;
if (opener && opener.parent) {
    ['i1', 'i2'].forEach(function(id) {
        var frame = opener.parent.frames[id];
        var form = frame && frame.document.form1;
        if (form && form.page && form.ishomepage) {
            form.ishomepage.value = form.page.value === homepage ? '1' : '0';
        }
    });
    if (opener.parent.save) opener.parent.save.location.href='done.php?n='+encodeURIComponent('set homepage to '+homepage);
}
self.close();
</script>
</body>
</html>
