<?php
// History is part of the authenticated, CSRF-protected rename2 transaction.
require_once('connect.inc.php');
http_response_code(410);
header('Content-Type: text/plain; charset=Windows-1252');
echo 'The separate history rename has been retired. Use the Rename form; it now completes resources, files, references and history together.';
