<?php
require_once('config.php');
require_once('connect.inc.php');

// Retrieve all relevant records
$query = "SELECT name, content_1, content_2, content_3, content_4, content_5 FROM resources WHERE 
          content_1 LIKE '%[fo:]%' OR content_2 LIKE '%[fo:]%' OR 
          content_3 LIKE '%[fo:]%' OR content_4 LIKE '%[fo:]%' OR 
          content_5 LIKE '%[fo:]%'";

$result = mysql_query($query);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

while ($row = mysql_fetch_assoc($result)) {
    $contents = [
        'content_1' => trim((string) $row['content_1']),
        'content_2' => trim((string) $row['content_2']),
        'content_3' => trim((string) $row['content_3']),
        'content_4' => trim((string) $row['content_4']),
        'content_5' => trim((string) $row['content_5'])
    ];
    
    // Consider only non-empty contents
    $validContents = array_filter($contents, function($c) { return $c !== ''; });
    
    if (count($validContents) > 1) {
        $nameLists = [];
        
        foreach ($validContents as $col => $content) {
            preg_match_all('/name\s*=\s*(["\'])(.*?)\1|name\s*=\s*([^\s>]+)/i', $content, $matches);
            
            // Korrektur: Beide Matches zusammenführen
            $nameLists[$col] = array_merge($matches[2], $matches[3]);
        }
        
        // Compare all combinations
        $keys = array_keys($nameLists);
        for ($i = 0; $i < count($keys) - 1; $i++) {
            for ($j = $i + 1; $j < count($keys); $j++) {
                $list1 = $nameLists[$keys[$i]];
                $list2 = $nameLists[$keys[$j]];
                
                if (count($list1) !== count($list2) || $list1 !== $list2) {
                    echo '<script>if(parent.parent.location.href.indexOf("c='.$row['name'].'")>0)parent.werror("FORM ERROR: In the page object '.$row['name'].', the form tag [fo:] exists, meaning that the name attributes of all HTML elements must be fully identical across language versions (same count and naming order).")</script>';
                }
            }
        }
    }
}
?>