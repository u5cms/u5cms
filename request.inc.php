<?php
/* Loading CMS helpers must not reserve application-owned request names.
 * Validate text at the route, language or authentication consumer instead. */
function u5ValidateTextRequest(array $values,array $keys) {
    foreach ($keys as $key) {
        if (!array_key_exists($key,$values)) continue;
        $value=$values[$key];
        if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value) && $value!==null) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=UTF-8');
            exit('Invalid request parameter.');
        }
    }
}
