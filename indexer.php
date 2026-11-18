<?php
// Full rebuilds yield between bounded batches. A named edit indexes only its resource.
ignore_user_abort(false);
header('Cache-Control: no-store');
$u5CpuLimit=(int)ini_get('max_execution_time');
if ($u5CpuLimit===0 || $u5CpuLimit>30) set_time_limit(30);
$u5Slice = null;
try {
    if (!isset($_GET['n'])) {
        $u5Slice = new U5IndexSlice(__DIR__.'/fileversions',isset($_GET['u5indexcontinue']),isset($_GET['u5indexauto']),false);
        if (!$u5Slice->ready()) return;
        define('U5CMS_FULL_INDEX_BATCH', true);
        foreach (array('default_socket_timeout','mysqlnd.net_read_timeout') as $u5Timeout) {
            $u5Seconds = ini_get($u5Timeout);
            if ($u5Seconds!==false && ((int)$u5Seconds<=0 || (int)$u5Seconds>10)) ini_set($u5Timeout,'10');
        }
    }
    require_once('connect.inc.php');
    require_once('render.inc.php');
    require_once('getinserts.inc.php');
    if ($result_b===false) throw new RuntimeException('Cannot read inserts for indexing.');
$u5Columns = 'name,deleted,lastmut,typ,content_1,content_2,content_3,content_4,content_5,title_1,title_2,title_3,title_4,title_5';
if (!empty($u5IndexUpgrade)) $u5Columns .= ',search_1,search_2,search_3,search_4,search_5';
// Compare database bytes so a simultaneous save (including one within the same
// second) cannot replace the newer headings or searchable text.
$u5DigestParts = array();
foreach (explode(',',$u5Columns) as $u5Field) $u5DigestParts[]="COALESCE(SHA2($u5Field,256),'NULL')";
$u5SourceDigest = "SHA2(CONCAT_WS(':',".implode(',',$u5DigestParts)."),256)";
$u5Columns .= ','.$u5SourceDigest.' AS u5_source_digest';
if ($u5Slice) $u5Keys = $u5Slice->keys();
else {
    $u5Name = mysql_real_escape_string(htmlspecialchars($_GET['n']));
    $u5Result = mysql_query("SELECT HEX(CONVERT(name USING utf8mb4)) AS resource_key,deleted FROM resources WHERE name='$u5Name' AND deleted!=1");
    if (!$u5Result) throw new RuntimeException('Cannot read the resource to index.');
    $u5Keys = array();
    while ($u5Key=mysql_fetch_assoc($u5Result)) $u5Keys[] = $u5Key;
    mysql_free_result($u5Result);
}
$u5More = false;
foreach ($u5Keys as $u5Position=>$u5Key) {
    if ($u5Slice && $u5Position>0 && ($u5Position>=U5IndexSlice::PAGE_LIMIT || $u5Slice->timeUsed())) {
        $u5More=true;
        break;
    }
    $u5Where = "name=CONVERT(UNHEX('".$u5Key['resource_key']."') USING utf8mb4) AND deleted=".(int)$u5Key['deleted'];
    $u5Result = mysql_query("SELECT $u5Columns FROM resources WHERE $u5Where LIMIT 1");
    if (!$u5Result) throw new RuntimeException('Cannot read index resource.');
    $row_ii = mysql_fetch_assoc($u5Result);
    mysql_free_result($u5Result);
    if (!$row_ii) {
        if ($u5Slice) $u5Slice->processed($u5Key);
        continue;
    }
    $u5Where .= ' AND lastmut <=> '.($row_ii['lastmut']===null ? 'NULL' : (string)(int)$row_ii['lastmut']);
    $u5Where .= " AND ".$u5SourceDigest."='".$row_ii['u5_source_digest']."'";

$_GET['c']=$row_ii['name'];

$_GET['l']=$lan1na;$content_1=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'1'));
$_GET['l']=$lan2na;$content_2=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'2'));
$_GET['l']=$lan3na;$content_3=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'3'));
$_GET['l']=$lan4na;$content_4=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'4'));
$_GET['l']=$lan5na;$content_5=render(idef($row_ii['content_1'],$row_ii['content_2'],$row_ii['content_3'],$row_ii['content_4'],$row_ii['content_5'],'5'));

$content_1 = sanitize_content($content_1);
$content_2 = sanitize_content($content_2);
$content_3 = sanitize_content($content_3);
$content_4 = sanitize_content($content_4);
$content_5 = sanitize_content($content_5);

$autotitle_1=takeSomeWords($content_1);
$autotitle_2=takeSomeWords($content_2);
$autotitle_3=takeSomeWords($content_3);
$autotitle_4=takeSomeWords($content_4);
$autotitle_5=takeSomeWords($content_5);

if (trim($row_ii['title_1'])=='' || str_replace(' . . .','',$row_ii['title_1'])!=$row_ii['title_1']) {
$title_1=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'1').' . . .';
}
else {
$title_1=$row_ii['title_1'];
}

if (trim($row_ii['title_2'])=='' || str_replace(' . . .','',$row_ii['title_2'])!=$row_ii['title_2']) {
$title_2=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'2').' . . .';
}
else {
$title_2=$row_ii['title_2'];
}

if (trim($row_ii['title_3'])=='' || str_replace(' . . .','',$row_ii['title_3'])!=$row_ii['title_3']) {
$title_3=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'3').' . . .';
}
else {
$title_3=$row_ii['title_3'];
}

if (trim($row_ii['title_4'])=='' || str_replace(' . . .','',$row_ii['title_4'])!=$row_ii['title_4']) {
$title_4=idef($autotitle_1,$autotitle_2,$autotitle_3,$autotitle_4,$autotitle_5,'4').' . . .';
}
else {
$title_4=$row_ii['title_4'];
}

if (trim($row_ii['title_5'])=='' || str_replace(' . . .','',$row_ii['title_5'])!=$row_ii['title_5']) {
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
WHERE $u5Where";
$result_i = mysql_query($sql_i);
if (!$result_i) throw new RuntimeException('Cannot update a search index resource.');
}
}
else {
if($row_ii['typ']=='p') {
$sql_i = "UPDATE resources
SET
title_1='" . mysql_real_escape_string($title_1) ."',
title_2='" . mysql_real_escape_string($title_2) ."',
title_3='" . mysql_real_escape_string($title_3) ."',
title_4='" . mysql_real_escape_string($title_4) ."',
title_5='" . mysql_real_escape_string($title_5) ."'
WHERE $u5Where";
$result_i = mysql_query($sql_i);
if (!$result_i) throw new RuntimeException('Cannot update a search index resource.');
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
WHERE $u5Where";
$result_i = mysql_query($sql_i);
if (!$result_i) throw new RuntimeException('Cannot update a search index resource.');
}
    if ($u5Slice) $u5Slice->processed($u5Key);
}
if ($u5Slice) {
    if ($u5More) $u5Slice->pause();
    else {
        $u5Slice->complete();
        echo '<audio id="doneaudio" src="u5admin/indexingdone.mp3" autoplay></audio><script>top.document.title=";"+top.document.title</script>';
    }
}
} catch (Throwable $error) {
    http_response_code(500);
    error_log('u5CMS indexer: '.$error->getMessage());
    echo 'The search index could not be updated.';
} finally {
    if ($u5Slice) $u5Slice->release();
}

function sanitize_content($content) {
    $content = str_replace(['<nobr>', '</nobr>'], '', $content);
    $content = str_replace('&nbsp;', ' ', $content);
    $content = preg_replace('/>(\S)/', '> $1', $content);
    $content = preg_replace('/(\S)</', '$1 <', $content);
    $content = preg_replace([
        '/<script\b[^>]*>[\s\S]*?<\/script>/is',
        '/<\?(php)?[\s\S]*?\?>/is',
        '/<!--[\s\S]*?-->/',
        '/<style\b[^>]*>[\s\S]*?<\/style>/is'
    ], '', $content);
    $content = strip_tags($content);
	$content=htmlX_entity_decode($content);
    $content = preg_replace('/\s+/', ' ', $content);
    return trim($content);
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

// A request owns the kernel lock for one short batch. The PHP-guarded file
// stores progress between requests. The lock file itself is never replaced.
final class U5IndexSlice {
    const PAGE_LIMIT = 8;
    const WORK_SECONDS = 2;
    const REST_SECONDS = 10;
    private $lock;
    private $directory;
    private $state;
    private $deadline;
    private $ready = false;

    public function __construct($directory, $continuation, $automatic, $formatPending) {
        $this->directory = $directory;
        $this->lock = fopen($directory.'/indexerrunning.txt', 'c+');
        if (!$this->lock) throw new RuntimeException('Cannot open the index lock.');
        if (!flock($this->lock, LOCK_EX | LOCK_NB)) {
            fclose($this->lock);
            $this->lock = null;
            return;
        }
        register_shutdown_function(array($this, 'release'));
        $now = time();
        $path = $this->progressPath();
        $this->state = null;
        if (is_file($path)) {
            $raw = file_get_contents($path);
            $guard = "<?php exit; ?>\n";
            $state = is_string($raw) && strncmp($raw,$guard,strlen($guard))===0
                ? json_decode(substr($raw,strlen($guard)),true) : null;
            if (is_array($state) && isset($state['started'],$state['next'],$state['failures'],$state['deleted'])
                && is_int($state['started']) && is_int($state['next']) && is_int($state['failures'])
                && $state['started']>0 && $state['next']<=$now+86400 && $state['failures']>=0 && $state['failures']<=4
                && array_key_exists('key',$state) && ($state['key']===null || self::validKey($state['key']))
                && (!isset($state['upper']) || (self::validKey($state['upper']) && isset($state['upperDeleted'])))) {
                $this->state = $state;
            } else {
                error_log('u5CMS indexer: rebuilding an invalid progress marker.');
                $continuation = false;
            }
        }
        if ($this->state!==null) {
            if ($now < $this->state['next']) return;
        } else {
            // A previous release may have launched a full run without flock.
            $running = (int)stream_get_contents($this->lock);
            if ($continuation || ($running > $now - 900 && $running <= $now)) return;
            $lastIndex = is_file($directory.'/lastindex.txt') ? (int)file_get_contents($directory.'/lastindex.txt') : 0;
            $lastSave = is_file($directory.'/lastsave.txt') ? (int)file_get_contents($directory.'/lastsave.txt') : 0;
            if ($automatic && !$formatPending && $lastSave <= $lastIndex) return;
            $this->state = array('started'=>$now,'key'=>null,'deleted'=>0,'failures'=>0,'next'=>0);
        }
        $this->deadline = microtime(true) + self::WORK_SECONDS;
        $this->markAttempt();
        $this->marker($now);
        $this->ready = true;
    }

    private static function validKey($key) {
        return is_string($key) && strlen($key)<=2040 && preg_match('/\A(?:[0-9A-F]{2})*\z/D',$key);
    }
    private function progressPath() { return $this->directory.'/indexer-progress.php'; }
    private function marker($value) {
        if (!rewind($this->lock) || !ftruncate($this->lock, 0)
            || fwrite($this->lock, (string)$value) !== strlen((string)$value) || !fflush($this->lock)) {
            throw new RuntimeException('Cannot update the index activity marker.');
        }
    }
    private function writeFile($path, $bytes, $next = null) {
        $tmp = tempnam($this->directory, '.index-');
        if (!$tmp) throw new RuntimeException('Cannot prepare an index checkpoint.');
        try {
            if (file_put_contents($tmp, $bytes) !== strlen($bytes)) throw new RuntimeException('Cannot save an index checkpoint.');
            // The launcher can read the next eligible time without parsing the checkpoint.
            if ($next!==null && !touch($tmp,$next)) throw new RuntimeException('Cannot schedule the next index batch.');
            if (is_file($path)) chmod($tmp, fileperms($path) & 0777);
            if (!rename($tmp,$path)) throw new RuntimeException('Cannot publish an index checkpoint.');
        } finally {
            if (is_file($tmp)) unlink($tmp);
        }
    }
    private function save() {
        $this->writeFile($this->progressPath(), "<?php exit; ?>\n".json_encode($this->state, JSON_THROW_ON_ERROR), $this->state['next']);
    }
    private function markAttempt() {
        // Record the attempt before rendering. A fatal error or a killed PHP
        // worker cannot cause the same expensive resource to spin on every visit.
        $delays = array(900,3600,21600,86400);
        $i = min((int)$this->state['failures'],count($delays)-1);
        $this->state['next'] = time() + $delays[$i];
        $this->state['failures'] = $i + 1;
        $this->save();
    }
    public function ready() { return $this->ready; }
    public function started() { return $this->state['started']; }
    public function keys() {
        if (!isset($this->state['upper'])) {
            $rows = mysql_query("SELECT HEX(CONVERT(name USING utf8mb4)) AS resource_key,deleted FROM resources WHERE deleted!=1 ORDER BY name DESC,deleted DESC LIMIT 1");
            if (!$rows) throw new RuntimeException('Cannot read the upper index boundary.');
            $upper = mysql_fetch_assoc($rows);
            mysql_free_result($rows);
            if (!$upper) return array();
            $this->state['upper']=$upper['resource_key'];
            $this->state['upperDeleted']=(int)$upper['deleted'];
            $this->save();
        }
        $upper = "CONVERT(UNHEX('".$this->state['upper']."') USING utf8mb4)";
        $where = "deleted!=1 AND (name<$upper OR (name=$upper AND deleted<=".(int)$this->state['upperDeleted']."))";
        if ($this->state['key']!==null) {
            $name = "CONVERT(UNHEX('".$this->state['key']."') USING utf8mb4)";
            $where .= " AND (name>$name OR (name=$name AND deleted>".(int)$this->state['deleted']."))";
        }
        $rows = mysql_query("SELECT HEX(CONVERT(name USING utf8mb4)) AS resource_key,deleted FROM resources WHERE $where ORDER BY name,deleted LIMIT ".(self::PAGE_LIMIT+1));
        if (!$rows) throw new RuntimeException('Cannot read index batch.');
        $keys = array();
        while ($row=mysql_fetch_assoc($rows)) $keys[] = $row;
        mysql_free_result($rows);
        return $keys;
    }
    public function processed($key) {
        $this->state['key'] = $key['resource_key'];
        $this->state['deleted'] = (int)$key['deleted'];
        $this->state['failures'] = 0;
        // If another page in this request fails, the next attempt resumes there.
        $this->markAttempt();
    }
    public function timeUsed() { return microtime(true) >= $this->deadline; }
    public function pause() {
        $this->state['failures'] = 0;
        $this->state['next'] = time() + self::REST_SECONDS;
        $this->save();
        echo '<script>setTimeout(function(){location.replace("?u5indexcontinue=1")},'.((self::REST_SECONDS+1)*1000).')</script>';
    }
    public function complete($publish = null) {
        if ($publish) $publish($this->state['started']);
        else $this->writeFile($this->directory.'/lastindex.txt',(string)max(0,$this->state['started']-1));
        if (!unlink($this->progressPath())) throw new RuntimeException('Cannot finish the index checkpoint.');
    }
    public function release() {
        if (!is_resource($this->lock)) return;
        try {
            if ($this->ready) $this->marker(0);
        } finally {
            flock($this->lock,LOCK_UN);
            fclose($this->lock);
            $this->lock = null;
        }
    }
}
