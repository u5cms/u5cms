<?php
// Identify the exact row loaded into a versioned form, including legacy rows.
function u5FormDataVersion($row) {
    $values = array();
    foreach (array('id','formname','headcsv','datacsv','time','ip','authuser','humantime','status','notes','lastmut') as $field) {
        $values[] = (string)($row[$field] ?? '');
    }
    return hash('sha256', serialize($values));
}

