<?php

if (isset($_COOKIE['fodanh']) && $_COOKIE['fodanh']=='n') {
   header("Location: formdata_n.php");
} else {
    header("Location: formdata_h.php");
}
