<?php

if (isset($_COOKIE['subi']) && $_COOKIE['subi']=='f') {
    header("Location: f.php");
} else {
    header("Location: i1.php");
}
