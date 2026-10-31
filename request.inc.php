<?php
/* Validate the CMS's shared routing/identity fields, not application-owned
 * query parameters or cookies. P objects may consume arrays of their own.
 * Missing keys and the search parser's separate policy remain intact. */
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
u5ValidateTextRequest($_GET,array('c','n','l','p','u5prvldd'));
u5ValidateTextRequest($_COOKIE,array('u','p','hp'));
