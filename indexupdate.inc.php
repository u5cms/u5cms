<?php
$u5IndexNoticeSave = (int)@file_get_contents('fileversions/lastsave.txt');
$u5IndexNoticeRaw = trim((string)@file_get_contents('fileversions/lastindex.txt'));
$u5IndexNoticeTime = preg_match('/\A[0-9]+\z/D', $u5IndexNoticeRaw) ? (int)$u5IndexNoticeRaw : 0;
if ($u5IndexNoticeTime > time()) $u5IndexNoticeTime = 0;
if ($u5IndexNoticeSave > $u5IndexNoticeTime) {
    echo '<div lang="en" style="font-size:10px;line-height:177%;margin-top:12px">Search index &amp; sitemap are not yet fully up to date. ';
    if ($u5IndexNoticeTime > 0) echo 'Index state of the last completed full rebuild: '.date('Y-m-d H:i T', $u5IndexNoticeTime).'.';
    else echo 'No completed full index rebuild is recorded yet.';
    echo '</div>';
}
?>
