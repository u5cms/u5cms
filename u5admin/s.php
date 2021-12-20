<?php

if (isset($_COOKIE['subs']) && $_COOKIE['subs']=='s2') {
    header("Location: s2.php");
} elseif (isset($_COOKIE['subs']) && $_COOKIE['subs']=='s3') {
    header("Location: c.php");
} else {
    header("Location: s1.php");
}
