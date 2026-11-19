<?php
require_once('connect.inc.php');
require_once('resource-rename.inc.php');
$old=is_string($_GET['n'] ?? null) ? $_GET['n'] : '';
$new=is_string($_GET['nn'] ?? null) ? $_GET['nn'] : '';
$pending=false;$complete=false;$recoveries=array();$recoveryError='';
$active=u5ResourceRenameStatus(dirname(__DIR__));
try {
    if ($old==='' && $new==='') {
        foreach (glob(dirname(__DIR__).'/fileversions/resource-rename-*.pending.php') as $path) {
            $saved=u5RenameStorageReadGuarded($path);
            u5RenameName($saved['old'] ?? '',true);u5RenameName($saved['new'] ?? '');
            if (($saved['version'] ?? '')!=='12.9.5.2'
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
<?php if ($active) { ?>
<h1>Rename in progress</h1>
<p><?php echo htmlXspecialchars(u5ResourceRenameMessage($active)); ?></p>
<script>setTimeout(function(){location.reload();},2000);</script>
<?php } ?>
<?php if ($old==='' && $new==='') { ?>
<h1>Unfinished resource renames</h1>
<?php if ($recoveryError!=='') { ?><p><?php echo htmlXspecialchars($recoveryError); ?></p>
<?php } elseif (!$recoveries) { ?><p>No unfinished resource rename is recorded.</p>
<?php } else { ?>
<p>These are recovery plans, not locks. Ordinary saves remain available when no Rename is running. Resume an interrupted operation after resolving its reported error. Retry checks your current permissions and keeps the original Rename policy.</p>
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
<p><?php echo $active ? 'A Rename is running; the saved plan is not yet complete.' : 'This Rename is interrupted. No active Rename owns the gate. Ordinary saves are available. Use Retry rename in the recovery overview to resume the saved operation.'; ?></p>
<p><a href="renaming.php">Open recovery overview</a></p>
<?php } elseif ($complete) { ?>
<p>The rename has completed, including files, references and history.</p>
<?php } else { ?>
<p>No resource rename completion checkpoint is available. Check the result in the original rename window.</p>
<?php } } ?></body></html>
