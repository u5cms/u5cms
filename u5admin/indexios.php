<?php
$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
header('Location: index.php' . ($query !== '' ? '?' . $query : ''), true, 302);
exit;
