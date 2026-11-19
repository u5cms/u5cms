<?php
require_once('connect.inc.php');
require_once('resource-rename.inc.php');
$old=is_string($_GET['n'] ?? null) ? $_GET['n'] : '';
$new=is_string($_GET['nn'] ?? null) ? $_GET['nn'] : '';
$pending=false;$complete=false;$recoveries=array();$recoveryError='';
try {
    if ($old==='' && $new==='') {
        foreach (glob(dirname(__DIR__).'/fileversions/resource-rename-*.pending.php') as $path) {
            $saved=u5CodeMigrationReadGuarded($path);
            u5RenameName($saved['old'] ?? '',true);u5RenameName($saved['new'] ?? '');
            if (($saved['version'] ?? '')!=='12.12.1'
                || u5RenameBase(dirname(__DIR__),$saved['old'],$saved['new']).'.pending.php'!==$path
                || !isset($saved['options']['links'])) throw new RuntimeException('Invalid resource rename checkpoint.');
            $recoveries[]=$saved;
        }
    } else {
    u5RenameName($old,true);u5RenameName($new,true);
    $base=u5RenameBase(dirname(__DIR__),$old,$new);
    $pending=is_file($base.'.pending.php') || is_file('../fileversions/codeasset-rename-'.sha1($old."\0".$new).'.pending.php');
    $complete=!$pending && is_file($base.'.done.php');
    }
} catch (Throwable $error) {http_response_code(400);$recoveryError=$error->getMessage();}
?><!doctype html><html lang="en"><head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252" />
<title>Resource rename status</title>
<?php require('backendcss.php'); ?></head><body>
<?php if ($old==='' && $new==='') { ?>
<h1>Unfinished resource renames</h1>
<?php if ($recoveryError!=='') { ?><p><?php echo htmlXspecialchars($recoveryError); ?></p>
<?php } elseif (!$recoveries) { ?><p>No unfinished resource rename is recorded.</p>
<?php } else { ?>
<p>Resume the same saved operation after resolving its reported error. The request checks your current permissions and keeps the original Rename policy. If an operation is still running, wait for its result.</p>
<?php foreach ($recoveries as $saved) { ?>
<h2><?php echo htmlXspecialchars($saved['old']).' to '.htmlXspecialchars($saved['new']); ?></h2>
<form method="post" action="rename2.php?name=<?php echo rawurlencode($saved['old']); ?>">
<input type="hidden" name="name" value="<?php echo htmlXspecialchars($saved['new']); ?>" />
<input type="hidden" name="ulinks" value="<?php echo $saved['options']['links'] ? 'yes' : 'no'; ?>" />
<?php require('t1.php'); ?><button type="submit">Retry rename</button>
</form>
<?php } } ?>
<?php } else { ?>
<p><?php echo htmlXspecialchars($old).' to '.htmlXspecialchars($new); ?></p>
<?php if ($pending) { ?>
<p>The rename has not completed. Keep the original request window open. After a failed request, use its Retry rename button to resume.</p>
<script>setTimeout(function(){location.reload();},3000);</script>
<?php } elseif ($complete) { ?>
<p>The rename has completed, including files, references and history.</p>
<?php } else { ?>
<p>No resource rename completion checkpoint is available. Check the result in the original rename window.</p>
<?php } } ?></body></html>
