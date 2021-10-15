<?php

if (isset($_COOKIE['subp']) && $_COOKIE['subp']=='p2') {
    header("Location: p2.php");
} else {
    header("Location: p1.php");
}
