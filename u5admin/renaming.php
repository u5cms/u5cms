<?php
require_once('connect.inc.php');
require_once('resource-rename.inc.php');
$old=is_string($_GET['n'] ?? null) ? $_GET['n'] : '';
$new=is_string($_GET['nn'] ?? null) ? $_GET['nn'] : '';
$pending=false;$complete=false;
try {
    u5RenameName($old,true);u5RenameName($new,true);
    $base=u5RenameBase(dirname(__DIR__),$old,$new);
    $pending=is_file($base.'.pending.php') || is_file('../fileversions/codeasset-rename-'.sha1($old."\0".$new).'.pending.php');
    $complete=!$pending && is_file($base.'.done.php');
} catch (Throwable $error) {http_response_code(400);}
?><!doctype html><html lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252" />
<title>Resource rename status</title>
<?php require('backendcss.php'); ?></head><body>
<p><?php echo htmlXspecialchars($old).' to '.htmlXspecialchars($new); ?></p>
<?php if ($pending) { ?>
<p>The rename has not completed. Keep the original request window open. After a failed request, use its Retry rename button to resume.</p>
<script>setTimeout(function(){location.reload();},3000);</script>
<?php } elseif ($complete) { ?>
<p>The rename has completed, including files, references and history.</p>
<?php } else { ?>
<p>No resource rename completion checkpoint is available. Check the result in the original rename window.</p>
<?php } ?></body></html>
