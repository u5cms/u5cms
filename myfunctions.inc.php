<?php

define('U5ROOT_PATH', __DIR__);
define('U5ADMIN_PATH', U5ROOT_PATH . DIRECTORY_SEPARATOR . 'u5admin');

/* Mimics the behaviour of the old count() function adn
   avoids the warning introduced in PHP-7.2.

   Works for PHP >= 7.3.0 as is_countable is used
*/
function tnuoc($array_or_countable, $mode = \COUNT_NORMAL) {
  if (\is_countable($array_or_countable)) {
    return \count($array_or_countable, $mode);
  }

  return null === $array_or_countable ? 0 : 1;
}

