#!/usr/bin/env python3
"""Compare actual mail construction with an original-main checkout; never send."""
import base64, email, email.policy, json, os, pathlib, subprocess, sys
ROOT=pathlib.Path(__file__).resolve().parent.parent
BASE=pathlib.Path(sys.argv[1]).resolve()
PHP=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
WORKER=r'''
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
$root=$argv[1];chdir($root);$_SERVER['REQUEST_URI']='/mail-audit';$_SERVER['HTTP_HOST']='cms.example';
require $root.'/myfunctions.inc.php';require $root.'/u5admin/u5idn.inc.php';
set_error_handler(function($level,$message){if($level & (E_NOTICE|E_WARNING|E_DEPRECATED|E_USER_DEPRECATED))return true;throw new ErrorException($message,0,$level);});
$source=file_get_contents($root.'/u5admin/mailsend.php');$start=strpos($source,'function validateemailaddress(');$end=strpos($source,'ignore_user_abort',$start);eval(substr($source,$start,$end-$start));
$result=array();
foreach (json_decode(stream_get_contents(STDIN),true) as $case) {
 foreach($case['fields'] as $key=>$value) $$key=$case['kind']==='simple' ? mb_convert_encoding($value,'Windows-1252','UTF-8') : $value;
 try {
  $source=file_get_contents($root.($case['kind']==='simple'?'/zendmail.php':'/u5admin/mailsend.php'));
  $start=strpos($source,'$mail = new Message();');$end=strpos($source,'try {'."\n".'    MailTransport',$start);
  if($start===false||$end===false)throw new RuntimeException('Cannot locate actual mail constructor.');
  preg_match_all('/^use [^;]+;$/m',$source,$imports);eval(implode("\n",$imports[0])."\n".substr($source,$start,$end-$start));
  $result[]=array('wire'=>base64_encode($mail->toString()),'error'=>null);
 }catch(Throwable $error){$result[]=array('wire'=>null,'error'=>get_class($error),'detail'=>base64_encode($error->getMessage()));}
}
echo json_encode($result);
'''
common={'zendfrom':'sender@example.org','zendto':'reader@example.org','zendname':'Jörg Müller – €','zendsubject':'Grüsse & Nachrichten «2026»','zendmessage':'Text mit ü, € und &amp; entity.','zendcc':'one@example.org;two@example.org','zendbcc':'hidden@example.org'}
cases=[]
def add(kind,**changes):cases.append({'kind':kind,'fields':dict(common,**changes)})
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
def run(root):
 p=subprocess.run(PHP+['-r',WORKER,str(root)],input=json.dumps(cases,ensure_ascii=True),capture_output=True,text=True)
 if p.returncode:raise RuntimeError(p.stdout+p.stderr)
 return json.loads(p.stdout)
def semantic(row):
 if row['error']:return {'accepted':False}
 message=email.message_from_bytes(base64.b64decode(row['wire']),policy=email.policy.default)
 return {'accepted':True,'headers':{name:tuple(str(x) for x in message.get_all(name,[])) for name in ['Subject','From','To','Reply-To','Cc','Bcc']},'parts':[(p.get_content_type(),p.get_content()) for p in message.walk() if not p.is_multipart()]}
before=run(BASE);after=run(ROOT);checks=0
for i,(case,a,b) in enumerate(zip(cases,before,after)):
 # Old Laminas cannot serialize even ASCII addresses without intl constants.
 # The new distribution supplies those constants; this is a capability gain,
 # separately checked from parity where the original constructor worked.
 baseline_missing_api=(a.get('error')=='Error' and b'Undefined constant' in base64.b64decode(a.get('detail','')) and b'IDNA_' in base64.b64decode(a.get('detail','')))
 if not baseline_missing_api:assert semantic(a)==semantic(b),(i,case['kind'],semantic(a),semantic(b))
 elif i in [0,1,2,5,6,7,9,10]:assert b['error'] is None,('Dependency IDN API must make this previously unavailable mail case work',i,b)
 else:assert b['error'] is not None,('Invalid or legacy unsupported address remains rejected',i,b)
 checks+=1
 if i in [5,10]:assert 'injected@example.org' not in str(semantic(b).get('headers',{}).get('Bcc',())), 'Subject cannot inject a Bcc header';checks+=1
 elif i in [0,1,2,6,7,9]:assert b['error'] is None,('Valid mail rejected',i,b);checks+=1
 elif i==8:assert a['error'] is not None and b['error'] is not None,'Legacy non-ASCII local-part header policy remains unchanged';checks+=1
print('Mail evolution: '+str(checks)+' semantic construction/denial checks passed; '+str(len(cases))+' actual plain/serial constructor cases; no transport invoked.')
