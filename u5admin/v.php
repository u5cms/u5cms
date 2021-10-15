<?php

if (isset($_COOKIE['subv']) && $_COOKIE['subv']=='y') {
    header("Location: y.php");
} else {
    header("Location: v1.php");
}
