<?php
require_once 'connect.inc.php';
require_once dirname(__DIR__).'/provision-changes.inc.php';
$root=dirname(__DIR__);
try {$release=u5ResourceActivityLock($root);}
catch (U5ResourceBusyException $error) {http_response_code(409);header('Retry-After: 2');echo 'This request did not change the resource.';exit;}
try {u5ProvisionIdentityChange($root,false);} finally {$release();}
