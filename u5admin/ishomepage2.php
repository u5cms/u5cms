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
$update = "UPDATE resources AS page JOIN resources AS target
           ON target.name='$escapedName' AND BINARY target.name=BINARY '$escapedName'
           AND target.deleted=$state AND target.typ='p' AND target.hidden=0
           SET page.ishomepage=(page.name=target.name AND page.deleted=target.deleted)
           WHERE page.ishomepage=1 OR (page.name=target.name AND page.deleted=target.deleted)";
if (mysql_query($update) === false) {
    http_response_code(500);
    die('Could not save the selected homepage.');
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
