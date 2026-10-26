<?php
require_once('connect.inc.php');
if (!empty($u5CodeMigrationComplete) && is_file('../fileversions/codeassets-12.11.1.done.php')) @unlink('../r/runonce.php');
?>