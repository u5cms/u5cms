# u5CMS 12.10.11

Legacy search links encode non-ASCII characters as `%uXXXX`. On PHP 8.4, the stable decoder passed an unnecessary non-hex prefix to `hexdec()`. PHP still returned the intended number but emitted a deprecation for every basic multilingual-plane character. Search pages could therefore pollute error logs or expose warning text when diagnostic output was enabled.

The decoder now passes the four hexadecimal digits directly. Search semantics and stored data are unchanged. The same correction applies to public and intranet search in Stable, V13 and V14. V15–V17 already use their branch-specific UTF-8 search decoder and require no code change.

The metadata renderer also inherited a numeric comparison intended for old PHP coercion rules. PHP 8 does not treat ordinary language codes such as `de`, `fr`, `it`, `rm` or `en` as zero in that comparison, so otherwise valid `rel="alternate"` links were omitted. The renderer now checks only that a language slot is non-empty. Canonical URLs, selected languages and additional query parameters are unchanged. This correction applies to Stable and V13–V17.

## Verification

- executes the decoder extracted from the two shipped search pages
- checks ordinary umlauts, `œ`, CJK text and supplementary characters
- covers 1,536 generated Unicode code points per search page
- treats every PHP warning or deprecation as a test failure
- confirms V15–V17 decode equivalent legacy links with their UTF-8 search core
- executes the canonical and alternate-link block extracted from the shipped metadata renderer
- checks five-language and three-language configurations, empty slots and encoded query data
- reproduces the PHP 8 comparison that previously suppressed every alternate-language link

There is no database, configuration or indexing migration.
