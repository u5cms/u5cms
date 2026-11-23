<?php
function u5HomepageEligible()
{
    return "typ='p' AND deleted!=1 AND hidden=0 AND name NOT IN ('', '-')";
}
