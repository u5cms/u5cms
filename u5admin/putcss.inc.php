<?php
require_once dirname(__DIR__).'/codeassets.inc.php';
try {
    u5CodeAssetExport(dirname(__DIR__));
} catch (RuntimeException $error) {
    http_response_code(500);
    echo '<p>'.htmlspecialchars($error->getMessage(),ENT_QUOTES,'Windows-1252').'</p>';
    throw $error;
}
?>
<script>
if (parent) if (parent.opener) if (parent.opener.parent) if (parent.opener.parent.i1) if (parent.opener.parent.i1.pframe2) parent.opener.parent.i1.pframe2.location.reload();
if (parent) if (parent.opener) if (parent.opener.parent) if (parent.opener.parent.i2) if (parent.opener.parent.i2.pframe2) parent.opener.parent.i2.pframe2.location.reload();
</script>
