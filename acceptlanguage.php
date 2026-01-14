<?php
echo $_SERVER['HTTP_ACCEPT_LANGUAGE'];
echo '<hr>';
echo htmlspecialchars($_COOKIE['aclan']) ?? 'Cookie "aclan" is not set';

