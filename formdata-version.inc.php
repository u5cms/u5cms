<?php
// Identify the exact row loaded into a versioned form, including legacy rows.
function u5FormDataVersion($row) {
    $values = array();
    foreach (array('id','formname','headcsv','datacsv','time','ip','authuser','humantime','status','notes','lastmut') as $field) {
        $values[] = (string)($row[$field] ?? '');
    }
    return hash('sha256', serialize($values));
}

// Keep the existing timestamp representation in stored form data and exports.
function u5FormDataOpeningVersion(&$timestamp) {
    if (!preg_match('/^([0-9]+):([a-f0-9]{64})$/D', (string)$timestamp, $parts)) return '';
    $timestamp = $parts[1];
    return $parts[2];
}
