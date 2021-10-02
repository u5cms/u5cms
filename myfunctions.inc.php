<?php

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
