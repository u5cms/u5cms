<?php
ignore_user_abort(true);
set_time_limit(36000);
require_once('connect.inc.php');
require_once('render.inc.php');
require_once('search-index.inc.php');

function sanitize_content($content) {
    return u5SearchIndexEncodeLegacy(u5SearchIndexVisibleText($content));
}

function takeSomeWords(string $input): string {
	global $maxwordsindocumenttitle;
	if($maxwordsindocumenttitle<1)$maxwordsindocumenttitle=7;
    $words = explode(' ', $input);
    $firstSevenWords = array_slice($words, 0, $maxwordsindocumenttitle);
    return implode(' ', $firstSevenWords);
}

function dblltgt($lg) {
$lg=str_replace('&lt;','&amp;lt;',$lg);
$lg=str_replace('&gt;','&amp;gt;',$lg);
return $lg;
}

function idef($l1='',$l2='',$l3='',$l4='',$l5='',$l='') {
    $l1 = $l1 ?? '';
    $l3 = $l3 ?? '';
    $l2 = $l2 ?? '';
    $l4 = $l4 ?? '';
    $l5 = $l5 ?? '';

       if ($l == '1' && trim($l1) != '') return $l1;
  else if ($l == '2' && trim($l2) != '') return $l2;
  else if ($l == '3' && trim($l3) != '') return $l3;
  else if ($l == '4' && trim($l4) != '') return $l4;
  else if ($l == '5' && trim($l5) != '') return $l5;
  else {
       if (trim($l1) != '') return $l1;
  else if (trim($l2) != '') return $l2;
  else if (trim($l3) != '') return $l3;
  else if (trim($l4) != '') return $l4;
  else if (trim($l5) != '') return $l5;
  else return $l1;
  }
}

$u5IndexRun = null;
$u5IndexReportMode = null;
$u5IndexFullRun = !isset($_GET['n']);
$u5IndexUpgrade = false;
register_shutdown_function(function () use (&$u5IndexRun) {
    // Also releases the lock after a fatal rendering error or an included exit.
    u5SearchIndexRelease($u5IndexRun);
});

try {
    if ($u5IndexFullRun) {
        $u5IndexRun = u5SearchIndexAcquire(__DIR__ . '/fileversions');
        if ($u5IndexRun === false) {
            echo '<script>top.document.title="_"+top.document.title</script>';
            return;
        }
        $u5IndexUpgrade = !u5SearchIndexFormatCurrent(__DIR__ . '/fileversions');
        u5SearchIndexMarkPending($u5IndexRun);
    }
    // SQL errors while rendering must abort this index run, not publish partial text.
    if (function_exists('mysqli_report')) {
        $u5IndexDriver = new mysqli_driver();
        $u5IndexReportMode = $u5IndexDriver->report_mode;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
    require_once('getinserts.inc.php');
    if ($result_b === false) throw new RuntimeException('Cannot read inserts for indexing.');

if(isset($_GET['n'])) {
	$oneitemonly=" name='".mysql_real_escape_string(htmlspecialchars(($_GET['n'] ?? '')))."' AND";
}	
else $oneitemonly='';

$sql_ii = "SELECT * FROM resources WHERE $oneitemonly deleted!=1 ORDER BY lastmut DESC";
$result_ii = mysql_query($sql_ii);
if ($result_ii === false) throw new RuntimeException('Cannot read resources for indexing.');
$num_ii = mysql_num_rows($result_ii);

for ($i_ii = 0;$i_ii < $num_ii;$i_ii++) {
usleep(100000);
$row_ii = mysql_fetch_array($result_ii);
if (!is_array($row_ii)) throw new RuntimeException('Cannot read an index resource.');

$_GET['c']=$row_ii['name'];

$_GET['l']=$lan1na;$content_1=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'1'));
$_GET['l']=$lan2na;$content_2=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'2'));
$_GET['l']=$lan3na;$content_3=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'3'));
$_GET['l']=$lan4na;$content_4=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'4'));
$_GET['l']=$lan5na;$content_5=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'5'));

$content_1 = u5SearchIndexVisibleText($content_1);
$content_2 = u5SearchIndexVisibleText($content_2);
$content_3 = u5SearchIndexVisibleText($content_3);
$content_4 = u5SearchIndexVisibleText($content_4);
$content_5 = u5SearchIndexVisibleText($content_5);

$autotitle_1=u5SearchIndexEncodeLegacy(takeSomeWords($content_1), false);
$autotitle_2=u5SearchIndexEncodeLegacy(takeSomeWords($content_2), false);
$autotitle_3=u5SearchIndexEncodeLegacy(takeSomeWords($content_3), false);
$autotitle_4=u5SearchIndexEncodeLegacy(takeSomeWords($content_4), false);
$autotitle_5=u5SearchIndexEncodeLegacy(takeSomeWords($content_5), false);

$content_1 = u5SearchIndexEncodeLegacy($content_1);
$content_2 = u5SearchIndexEncodeLegacy($content_2);
$content_3 = u5SearchIndexEncodeLegacy($content_3);
$content_4 = u5SearchIndexEncodeLegacy($content_4);
$content_5 = u5SearchIndexEncodeLegacy($content_5);

if (trim(($row_ii['title_1'] ?? ''))=='' || str_replace(' . . .','',($row_ii['title_1'] ?? ''))!=$row_ii['title_1']) {
$title_1=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'1').' . . .';
}
else {
$title_1=$row_ii['title_1'];
}

if (trim(($row_ii['title_2'] ?? ''))=='' || str_replace(' . . .','',($row_ii['title_2'] ?? ''))!=$row_ii['title_2']) {
$title_2=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'2').' . . .';
}
else {
$title_2=$row_ii['title_2'];
}

if (trim(($row_ii['title_3'] ?? ''))=='' || str_replace(' . . .','',($row_ii['title_3'] ?? ''))!=$row_ii['title_3']) {
$title_3=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'3').' . . .';
}
else {
$title_3=$row_ii['title_3'];
}

if (trim(($row_ii['title_4'] ?? ''))=='' || str_replace(' . . .','',($row_ii['title_4'] ?? ''))!=$row_ii['title_4']) {
$title_4=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'4').' . . .';
}
else {
$title_4=$row_ii['title_4'];
}

if (trim(($row_ii['title_5'] ?? ''))=='' || str_replace(' . . .','',($row_ii['title_5'] ?? ''))!=$row_ii['title_5']) {
$title_5=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'5').' . . .';
}
else {
$title_5=$row_ii['title_5'];
}

if(!isset($donotautoindexifthisstringisfoundinfinalpagecontent))$donotautoindexifthisstringisfoundinfinalpagecontent='DoNotAutoIndexIfThisStringIsFoundInFinalPageContentLiterally';
if(strpos('x'.render($row_ii['content_1'].$row_ii['content_2'].$row_ii['content_3'].$row_ii['content_4'].$row_ii['content_5']),$donotautoindexifthisstringisfoundinfinalpagecontent)<1) {

if($row_ii['typ']=='p') {
$sql_i = "UPDATE resources
SET 
title_1='" . mysql_real_escape_string($title_1) ."',
title_2='" . mysql_real_escape_string($title_2) ."',
title_3='" . mysql_real_escape_string($title_3) ."',
title_4='" . mysql_real_escape_string($title_4) ."',
title_5='" . mysql_real_escape_string($title_5) ."',
search_1='" . mysql_real_escape_string($content_1) . "',
search_2='" . mysql_real_escape_string($content_2) . "',
search_3='" . mysql_real_escape_string($content_3) . "',
search_4='" . mysql_real_escape_string($content_4) . "',
search_5='" . mysql_real_escape_string($content_5) . "' 
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i === false) throw new RuntimeException('Cannot update a search index resource.');
}
}
else {
if($row_ii['typ']=='p') {
$retainedSearch = '';
if ($u5IndexUpgrade) {
    for ($indexLanguage = 1; $indexLanguage <= 5; $indexLanguage++) {
        $field = 'search_' . $indexLanguage;
        $stored = u5SearchIndexEncodeLegacy(u5SearchFromLegacy($row_ii[$field] ?? ''));
        $retainedSearch .= ", " . $field . "='" . mysql_real_escape_string($stored) . "'";
    }
}
$sql_i = "UPDATE resources
SET 
title_1='" . mysql_real_escape_string($title_1) ."',
title_2='" . mysql_real_escape_string($title_2) ."',
title_3='" . mysql_real_escape_string($title_3) ."',
title_4='" . mysql_real_escape_string($title_4) ."',
title_5='" . mysql_real_escape_string($title_5) ."'" . $retainedSearch . "
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i === false) throw new RuntimeException('Cannot update a search index resource.');
}
}
if($row_ii['typ']!='p') {
$sql_i = "UPDATE resources
SET 
search_1='" . mysql_real_escape_string($content_1) . "',
search_2='" . mysql_real_escape_string($content_2) . "',
search_3='" . mysql_real_escape_string($content_3) . "',
search_4='" . mysql_real_escape_string($content_4) . "',
search_5='" . mysql_real_escape_string($content_5) . "' 
WHERE deleted!=1 AND name='" . (mysql_real_escape_string($row_ii['name'])) . "'";
$result_i = mysql_query($sql_i);
if ($result_i === false) throw new RuntimeException('Cannot update a search index resource.');
}
}

    if ($u5IndexFullRun) {
        u5SearchIndexPublish($u5IndexRun);
        echo '<audio id="doneaudio" src="u5admin/indexingdone.mp3" autoplay />';
        echo '<script>var audio=document.getElementById("doneaudio");audio.volume=0.05;top.document.title=";"+top.document.title;</script>';
    }
} catch (Throwable $error) {
    http_response_code(500);
    error_log('u5CMS search indexing failed: ' . $error->getMessage());
    echo '<p>The search index could not be updated.</p>';
} finally {
    if ($u5IndexReportMode !== null) mysqli_report($u5IndexReportMode);
    u5SearchIndexRelease($u5IndexRun);
}
