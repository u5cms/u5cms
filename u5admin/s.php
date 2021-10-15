<?php

if (isset($_COOKIE['subs'])) {
    if ($_COOKIE['subs']=='s2') {
        header("Location: s2.php");
    } else {
        header("Location: c.php");
    }
} else {
    header("Location: s1.php");
}
