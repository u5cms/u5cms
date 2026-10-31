<?php
/* HTTP query/cookie values consumed as text must not be arrays. Do not invent
 * missing keys: isset/absence semantics and the search parser remain intact. */
function u5ValidateTextRequest(array $values,array $exceptions=array()) {
    foreach ($values as $key=>$value) {
        if (in_array($key,$exceptions,true)) continue;
        if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value) && $value!==null) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=UTF-8');
            exit('Invalid request parameter.');
        }
    }
}
u5ValidateTextRequest($_GET,array('q'));
u5ValidateTextRequest($_COOKIE);
