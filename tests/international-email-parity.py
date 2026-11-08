#!/usr/bin/env python3
"""Compare actual legacy/new message construction in memory; never send."""
import base64,email,email.policy,json,os,pathlib,subprocess,sys
ROOT=pathlib.Path(__file__).resolve().parent.parent
BASE=pathlib.Path(sys.argv[1]).resolve()
PHP=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
WORKER=r'''
use Laminas\Mail\Message;use Laminas\Mime\Message as MimeMessage;use Laminas\Mime\Mime;use Laminas\Mime\Part as MimePart;
$root=$argv[1];chdir($root);ini_set('default_charset','Windows-1252');require $root.'/vendor/autoload.php';require $root.'/u5admin/u5idn.inc.php';
function tnuoc($v){return is_countable($v)?count($v):($v===null?0:1);}
class CapturedMail{public function send($message){$GLOBALS['capturedWire']=$message->toString();}}
function MailTransport($a,$b){return new CapturedMail();}
if(is_file($root.'/mail/delivery.php'))require $root.'/mail/delivery.php';
$usesmtp='no';$mysmtpoptions=null;$rows=array();
foreach(json_decode(stream_get_contents(STDIN),true) as $case){
 $GLOBALS['capturedWire']=null;foreach($case['fields'] as $key=>$value)$$key=$case['kind']==='simple'?mb_convert_encoding($value,'Windows-1252','UTF-8'):$value;
 ob_start();try{
  if($case['kind']==='simple')include $root.'/zendmail.php';
  elseif(function_exists('u5mail_send'))u5mail_send(array('from'=>u5mail_address_decoded($zendfrom),'to'=>u5mail_addresses($zendto,'utf8'),'cc'=>u5mail_addresses($zendcc,'utf8'),'bcc'=>u5mail_addresses($zendbcc,'utf8'),'name'=>$zendname,'subject'=>$zendsubject,'body'=>$zendmessage),true);
  else{$source=file_get_contents($root.'/u5admin/mailsend.php');$start=strpos($source,'$mail = new Message();');$end=strpos($source,'try {'."\n".'    MailTransport',$start);if($start===false||$end===false)throw new RuntimeException('Missing legacy constructor.');$source=substr($source,$start,$end-$start);$source=str_replace('validateemailaddress(', 'parityValid(', $source);eval('use Laminas\Mail\Message;use Laminas\Mime\Message as MimeMessage;use Laminas\Mime\Mime;use Laminas\Mime\Part as MimePart;'.$source);MailTransport(null,null)->send($mail);}
  if($case['kind']==='simple' && $GLOBALS['capturedWire']!==null && (!isset($mail) || !($mail instanceof Laminas\Mail\Message)))throw new RuntimeException('Legacy include message object was lost.');
  $rows[]=array('wire'=>$GLOBALS['capturedWire']===null?null:base64_encode($GLOBALS['capturedWire']),'error'=>$GLOBALS['capturedWire']===null?'delivery-refused':null);
 }catch(Throwable $e){$rows[]=array('wire'=>null,'error'=>get_class($e),'detail'=>$e->getMessage());}ob_end_clean();
}echo json_encode($rows,JSON_INVALID_UTF8_SUBSTITUTE);
function parityValid($a){return strpos($a,'@')!==false;}
'''
common=dict(zendfrom='sender@example.org',zendto='reader@example.org',zendname='Jörg Müller – €',zendsubject='Grüsse & Nachrichten «2026»',zendmessage='Text mit ü, € und &amp; entity.',zendcc='one@example.org;two@example.org',zendbcc='hidden@example.org')
cases=[]
def add(kind,**changes):cases.append(dict(kind=kind,fields=dict(common,**changes)))
add('simple');add('simple',zendsubject='Langer Betreff ü '*60)
add('simple',zendfrom='alice@xn--bcher-kva.example',zendto='reader@xn--bcher-kva.example')
add('simple',zendfrom='alice@b&#252;cher.example',zendto='reader@b&#252;cher.example')
add('simple',zendfrom='müller@bücher.example',zendto='müller@bücher.example')
add('simple',zendsubject='subject\r\nBcc: injected@example.org')
add('serial',zendmessage='<p>Deutsch: Grüsse – Français: été – 日本語 😀</p>')
add('serial',zendsubject='Langer Betreff ü '*60,zendmessage='<p>line one</p>\r\n<p>line two</p>')
add('serial',zendfrom='müller@xn--bcher-kva.example',zendto='leser@xn--bcher-kva.example')
add('serial',zendcc=';'.join('member'+str(i)+'@example.org' for i in range(20)))
add('serial',zendsubject='subject\r\nBcc: injected@example.org')
add('simple',zendto='"Müller, Anna" <reader@example.org>')
add('simple',zendto='"space name"@example.org')
add('simple',zendto='Anna <reader@example.org>')
def run(root):
 p=subprocess.run(PHP+['-d','error_reporting=22527','-r',WORKER,str(root)],input=json.dumps(cases,ensure_ascii=True),capture_output=True,text=True)
 if p.returncode:raise RuntimeError(p.stdout+p.stderr)
 return json.loads(p.stdout)
def semantic(row):
 if row['error']:return {'accepted':False}
 m=email.message_from_bytes(base64.b64decode(row['wire']),policy=email.policy.default)
 return dict(accepted=True,headers={n:tuple(str(x) for x in m.get_all(n,[])) for n in ['Subject','From','To','Reply-To','Cc','Bcc']},parts=[(p.get_content_type(),p.get_content()) for p in m.walk() if not p.is_multipart()])
before=run(BASE);after=run(ROOT);checks=0
for i,(a,b) in enumerate(zip(before,after)):
 if i in [5,10]:assert b['error'] is not None,('CRLF rejected before delivery',i,b)
 elif i==11:assert semantic(b)['headers']['To']==('"Müller, Anna" <reader@example.org>',),('display name preserved',b)
 elif a['error']=='Error' and 'Undefined constant' in a.get('detail',''):assert b['error'] is None,('missing-IDN capability gain',i,b)
 else:assert semantic(a)==semantic(b),(i,semantic(a),semantic(b))
 checks+=1
 if i in [0,1,2,3,6,7,9]:assert b['error'] is None,('ordinary message rejected',i,b);checks+=1
 if b['wire']:assert 'injected@example.org' not in str(semantic(b)['headers'].get('Bcc',())),('Bcc injection',i);checks+=1
print('International mail parity: '+str(checks)+' checks passed, ordinary headers/body/MIME preserved; CRLF rejected; no transport invoked.')
