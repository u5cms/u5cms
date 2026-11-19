<?php
/* Focused behavior contracts; run on each available PHP runtime. No mail is sent. */
require __DIR__.'/php9-guard.php';
$_SERVER['REQUEST_URI']='/readiness-test';$_SERVER['HTTP_HOST']='localhost';
// This suite exercises an explicitly available IDN API. The separate identity
// regression also verifies the historical profile without that capability.
require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/myfunctions.inc.php';
require dirname(__DIR__).'/u5admin/u5idn.inc.php';
$checks=0;
function readinessSame($actual,$expected,$label) {
    global $checks;$checks++;
    if ($actual!==$expected) throw new RuntimeException($label.': '.var_export($actual,true));
}
function readinessFunction($file,$name,$replacement) {
    $code=file_get_contents($file);$tokens=token_get_all($code);$found=false;$depth=0;$started=false;$out='';
    for ($i=0;$i<count($tokens);$i++) {
        $t=$tokens[$i];$value=is_array($t) ? $t[1] : $t;
        if (!$found && is_array($t) && $t[0]===T_FUNCTION) {
            $next=$i+1;while (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][0]===T_WHITESPACE) $next++;
            if (isset($tokens[$next]) && is_array($tokens[$next]) && $tokens[$next][1]===$name) $found=true;
        }
        if (!$found) continue;
        $out.=$value;
        if (!is_array($t) && $value==='{') {$depth++;$started=true;}
        if (!is_array($t) && $value==='}' && --$depth===0 && $started) break;
    }
    if (!$found || !$started || $depth!==0) throw new RuntimeException('Missing fixture function '.$name);
    return preg_replace('/\b'.preg_quote($name,'/').'\s*\(/',$replacement.'(',$out);
}
readinessSame(function_exists('tnuoc'),false,'No count mimic remains');
function readinessRequest(array $query,array $cookies=array()) {
    $code='$_GET='.var_export($query,true).';$_COOKIE='.var_export($cookies,true).';register_shutdown_function(function(){echo "|status=".(http_response_code() ?: 200);});require '.var_export(dirname(__DIR__).'/request.inc.php',true).';u5ValidateTextRequest($_GET,array("c","n","l","p","u5prvldd"));u5ValidateTextRequest($_COOKIE,array("u","p","hp"));echo "accepted";';
    $process=proc_open(array(PHP_BINARY,'-r',$code),array(1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
    if (!is_resource($process)) throw new RuntimeException('Cannot test the isolated request boundary.');
    $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    if (proc_close($process)!==0 || $err!=='') throw new RuntimeException('Request-boundary child failed: '.$err);
    return $out;
}
readinessSame(readinessRequest(array()),'accepted|status=200','Missing request keys retain absence semantics');
readinessSame(readinessRequest(array('c'=>'page','l'=>'de')),'accepted|status=200','Scalar route input is accepted');
readinessSame(readinessRequest(array('c'=>array('page'))),'Invalid request parameter.|status=400','Collection-shaped route input is rejected');
readinessSame(readinessRequest(array(),array('u'=>array('name'))),'Invalid request parameter.|status=400','Collection-shaped cookie input is rejected');
readinessSame(readinessRequest(array('q'=>array('term'))),'accepted|status=200','Search query keeps its dedicated parser policy');

readinessSame(u5toutf8(null),'','Missing text is empty at its encoding boundary');
readinessSame(u5allnument(null),'','Missing identifier text has no fabricated codepoint');
readinessSame(u5toutf8("\x80 \x8C \x9C \xE4 &amp;"),"\xE2\x82\xAC \xC5\x92 \xC5\x93 \xC3\xA4 &",'Windows-1252 and entity conversion');
$latin='';$utf8='';
for ($i=0;$i<=255;$i++) {
    $latin.=chr($i);$utf8.=$i<128 ? chr($i) : chr(0xC0|($i>>6)).chr(0x80|($i&63));
}
// Independent byte-to-codepoint reference, including control bytes and NUL.
readinessSame(mb_convert_encoding($latin,'UTF-8','ISO-8859-1'),$utf8,'All Latin-1 bytes retain their explicit mapping');
readinessSame(mb_convert_encoding($utf8,'ISO-8859-1','UTF-8'),$latin,'Every Latin-1 byte round trips');
foreach (array('upload','upload_mandatory','Pupload','Pupload_mandatory') as $i=>$directory) {
    $path=dirname(__DIR__).'/'.$directory.'/uploaddone.php';
    $prefix='readiness'.$i;
    eval(readinessFunction($path,'mkfilename',$prefix.'filename'));
    $ascii=readinessFunction($path,'mkascii',$prefix.'ascii');
    $ascii=preg_replace('/\bmkfilename\s*\(/',$prefix.'filename(',$ascii);eval($ascii);
    $fn=$prefix.'ascii';
    readinessSame($fn("M\xC3\xBCller_\xC3\x84\xC3\x96\xC3\x9C.pdf"),'Mueller_AeOeUe.pdf',$directory.' existing filename transliteration');
    readinessSame($fn('plain-file.txt'),'plain-file.txt',$directory.' ASCII filename');
}
foreach (array('alice@example.invalid','alice@xn--bcher-kva.example','alice@b&#252;cher.example') as $address) {
    $normalized=u5flatidn($address);
    readinessSame(u5flatidn($normalized),$normalized,'Identifier normalization is idempotent: '.$address);
}
foreach (array('','plain','alice@example.invalid,broken','alice@example.invalid,','alice@@example.invalid','@example.invalid','alice@') as $address) {
    readinessSame(u5flatidn($address),$address,'Malformed/empty identity list entries are preserved: '.$address);
}
readinessSame(u5toidn('alice@b&#252;cher.example'),'alice@xn--bcher-kva.example','IDN encoding retains the existing ASCII account key');
readinessSame(u5flatidn('alice@xn--bcher-kva.example'),"alice@b\xFCcher.example",'IDN display conversion retains Windows-1252 account text');
$stack=new Laminas\Stdlib\ArrayStack(array('first','second'));
eval(readinessFunction(dirname(__DIR__).'/u5admin/sendfilea2.php','reArrayFiles','readinessAlbumFiles'));
$uploads=null;
readinessSame(readinessAlbumFiles($uploads),array(),'An absent album upload is an empty collection');
$uploads=array('name'=>'photo.jpg','tmp_name'=>'/tmp/not-an-upload','error'=>0,'size'=>12,'type'=>'image/jpeg');
readinessSame(readinessAlbumFiles($uploads),array(),'A single-file field is not a multiple-upload collection');
$uploads=array('name'=>array(),'tmp_name'=>array(),'error'=>array(),'size'=>array(),'type'=>array());
readinessSame(readinessAlbumFiles($uploads),array(),'An empty multiple-upload field remains empty');
$uploads=array('name'=>array('one.jpg','two.jpg'),'tmp_name'=>array('/tmp/one','/tmp/two'),'error'=>array(0,4),'size'=>array(12,0),'type'=>array('image/jpeg',''));
readinessSame(readinessAlbumFiles($uploads),array(array('name'=>'one.jpg','tmp_name'=>'/tmp/one','error'=>0,'size'=>12,'type'=>'image/jpeg'),array('name'=>'two.jpg','tmp_name'=>'/tmp/two','error'=>4,'size'=>0,'type'=>'')),'Native upload order, metadata and error status are retained');
readinessSame(array_values(iterator_to_array($stack->getIterator())),array('second','first'),'Upstream iterator contract preserves stack order');
$smtp=MailTransport('yes',new Laminas\Mail\Transport\SmtpOptions(array('host'=>'127.0.0.1','port'=>2525,'name'=>'localhost')));
readinessSame($smtp instanceof Laminas\Mail\Transport\Smtp,true,'Configured SMTP transport remains available');
readinessSame(MailTransport('no',array()) instanceof Laminas\Mail\Transport\Sendmail,true,'Configured sendmail transport remains available');
$message=new Laminas\Mail\Message();$message->setEncoding('UTF-8');
$message->setFrom('sender@example.invalid','Sender');$message->addTo('recipient@example.invalid','Recipient');
$message->addCc('copy@example.invalid');$message->addBcc('hidden@example.invalid');$message->setSubject("\xC3\x9Cber \xE2\x82\xAC");
$part=new Laminas\Mime\Part("Body \xC5\x93");$part->type='text/plain';$part->charset='UTF-8';$part->encoding=Laminas\Mime\Mime::ENCODING_BASE64;
$body=new Laminas\Mime\Message();$body->setParts(array($part));$message->setBody($body);
$serialized=$message->toString();
readinessSame(strpos($serialized,'recipient@example.invalid')!==false,true,'Mail recipients serialize');
readinessSame(strpos($serialized,'=?UTF-8?')!==false,true,'Non-ASCII mail subject is encoded');
readinessSame(strpos($serialized,base64_encode("Body \xC5\x93"))!==false,true,'MIME body retains Unicode bytes');
$addresses=new Laminas\Mail\AddressList();$addresses->add('one@example.invalid');$addresses->add('two@example.invalid');
$iterated=array();foreach ($addresses as $address) $iterated[]=$address->getEmail();
readinessSame($iterated,array('one@example.invalid','two@example.invalid'),'Typed mail iterator retains advancement and order');
$priorities=new Laminas\Stdlib\PriorityList();$priorities->insert('low','second',1);$priorities->insert('high','first',10);
readinessSame(array_values(iterator_to_array($priorities)),array('first','second'),'Typed priority iterator retains ordering');
$queue=new Laminas\Stdlib\SplPriorityQueue();$queue->insert('first',10);$queue->insert('second',1);
readinessSame($queue->extract(),'first','Typed native SPL insertion/extraction contract');
$patches=json_decode(file_get_contents(dirname(__DIR__).'/patches/php9-return-types.json'),true,512,JSON_THROW_ON_ERROR);
foreach ($patches['files'] as $patch) {
    $source=file_get_contents(dirname(__DIR__).'/'.$patch['path']);
    preg_match('/namespace\s+([^;]+);/',$source,$namespace);
    $class=new ReflectionClass($namespace[1].'\\'.pathinfo($patch['path'],PATHINFO_FILENAME));
    readinessSame(strpos($source,'#[ReturnTypeWillChange]'),false,'No suppressed interface return contracts in '.$class->name);
    foreach ($patch['replacements'] as $replacement) if (preg_match('/function\s+&?\s*(\w+)\s*\(/',$replacement['new'],$method))
        readinessSame($class->getMethod($method[1])->hasReturnType(),true,'Explicit native return contract '.$class->name.'::'.$method[1]);
}
$source=file_get_contents(dirname(__DIR__).'/u5admin/import2.php');$begin=strpos($source,'class CsvImporter');
$tokens=token_get_all("<?php ".substr($source,$begin));$class='';$depth=0;$started=false;
foreach ($tokens as $token) {
    if (is_array($token) && $token[0]===T_OPEN_TAG) continue;
    $value=is_array($token) ? $token[1] : $token;$class.=$value;
    if (!is_array($token) && $value==='{') {$started=true;$depth++;}
    if (!is_array($token) && $value==='}' && --$depth===0 && $started) break;
}
eval($class);
$file=tempnam(sys_get_temp_dir(),'u5csv-');
try {
    $fp=fopen($file,'w');
    $rows=array(array('first','second'),array('quote " and slash \\','line one'."\n".'line two'),array('','0'));
    foreach ($rows as $row) fputcsv($fp,$row,';','"','');fclose($fp);
    $csv=new CsvImporter($file,true,';');
    readinessSame($csv->get(),array(array('first'=>$rows[1][0],'second'=>$rows[1][1]),array('first'=>'','second'=>'0')),'CSV quotes, backslashes, multiline, empty and zero values');
    unset($csv);file_put_contents($file,"first;second\nonly-one-field\n");$csv=new CsvImporter($file,true,';');readinessSame($csv->get(),array(array('first'=>'only-one-field','second'=>'')),'A missing CSV column is empty text and never the previous row');
    unset($csv);file_put_contents($file,'');$csv=new CsvImporter($file,false,';');readinessSame($csv->get(),array(),'Empty CSV is an empty collection');unset($csv);
} finally {unlink($file);}
echo 'PHP readiness behavior checks: '.$checks.' passed on '.PHP_VERSION.".\n";
