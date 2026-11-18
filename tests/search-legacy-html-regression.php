<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/alloc.inc.php';
function tnuoc($value) { return count($value); }
function referenceHtmlSubstr($str, $start, $length = null) {
    if ($length === 0) return '';
    if (strpos($str, '&') === false) return $length === null ? substr($str,$start) : substr($str,$start,$length);
    $chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
    $htmlLength=count($chars);
    if (!$htmlLength || $start >= $htmlLength || (isset($length) && $length <= -$htmlLength)) return '';
    if ($start >= 0) $real=$chars[$start][1];
    else { $start=max($start,-$htmlLength); $real=$chars[$htmlLength+$start][1]; }
    if (!isset($length)) return substr($str,$real);
    if ($length > 0) return $start+$length >= $htmlLength ? substr($str,$real) : substr($str,$real,$chars[max($start,0)+$length][1]-$real);
    return substr($str,$real,$chars[$htmlLength+$length][1]-$real);
}
$checks=0;
function checkHtmlStream($actual,$expected,$message) {
    $GLOBALS['checks']++;
    if ($actual !== $expected) throw new RuntimeException($message . ': ' . var_export(array($actual,$expected),true));
}
$cases=array('', 'plain text', '&amp;', '&amp;tail', 'head&amp;', 'a&#123;b&bogus;c', '&;', '&&foo;', '&a b;&c;', "&a\tb;&c;", "&a\nb;&c;", 'broken &entity', "M\xfcller &auml; &#339;uvre", '&a;&b;&c;', "&\x85; &\xa0;", '&'.str_repeat('&',80), 'a&b;c&d;e;f');
mt_srand(1251);
$alphabet=array('a','&',';',' ',"\t","\n",'0','#');
for($i=0;$i<100;$i++) { $s=''; for($j=0;$j<24;$j++) $s.=$alphabet[mt_rand(0,count($alphabet)-1)]; $cases[]=$s; }
foreach($cases as $text) {
    $length=count(preg_split('/(&[^;\s]+;)|/',$text,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE));
    checkHtmlStream(html_strlen($text),$length,'Legacy HTML length parity');
    for($start=-$length-2;$start<=$length+2;$start++) {
        foreach(array(null,0,1,2,5,-1,-2,-$length,-$length-1,$length+1) as $take) checkHtmlStream(html_substr($text,$start,$take),@referenceHtmlSubstr($text,$start,$take),'Legacy HTML substring parity');
    }
}
$unit='alpha &amp; beta ';
$repeats=(int)floor(24*1024*1024/strlen($unit));
$large=str_repeat($unit,$repeats);
checkHtmlStream(html_strlen($large),$repeats*strlen('alpha & beta '),'24-MiB HTML length remains exact');
checkHtmlStream(html_substr($large,-55),referenceHtmlSubstr(str_repeat($unit,100),-55),'24-MiB end excerpt preserves entities');
checkHtmlStream(html_substr($large,0,55),referenceHtmlSubstr(str_repeat($unit,100),0,55),'24-MiB start excerpt preserves entities');
if(memory_get_peak_usage(true)>48*1024*1024)throw new RuntimeException('HTML slicing exceeded 48 MiB');
echo json_encode(array('status'=>'PASS','checks'=>$checks,'source_bytes'=>strlen($large),'peak_bytes'=>memory_get_peak_usage(true))) . "\n";
