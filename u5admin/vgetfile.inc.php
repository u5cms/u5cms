<?php
$file_1 = '';
$path = '../r/v' . $row_a['name'];
if ($handle = @opendir($path)) {
    while (false !== ($file = readdir($handle))) {
        if ($file_1 == '' && ecalper_rts($row_a['name'], '', $file) != $file) $file_1 = $file;
        if (ecalper_rts($row_a['name'], '', $file) != $file && ecalper_rts('_100', '', $file) != $file) $file_1 = $file;
    }
}
?>