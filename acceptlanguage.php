<?php

echo $_SERVER['HTTP_ACCEPT_LANGUAGE'];
echo '<hr>';
echo $_COOKIE['aclan'] ?? 'Cookie "aclan" is not set';

