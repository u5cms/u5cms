#!/usr/bin/env python3
"""Real loopback SMTP conversations; no external message delivery."""
import base64, email, email.policy, json, os, pathlib, socket, ssl, subprocess, tempfile, threading

ROOT=pathlib.Path(__file__).resolve().parent.parent
PHP=json.loads(os.environ.get('U5_MAIL_TEST_PHP','["php"]'))

class Peer:
    def __init__(self, utf8=True, reject=False, tls=False, cert=None):
        self.utf8=utf8;self.reject=reject;self.tls=tls;self.cert=cert
        self.commands=[];self.messages=[];self.error=None
        self.socket=socket.socket();self.socket.bind(('127.0.0.1',0));self.socket.listen(1);self.socket.settimeout(10)
        self.port=self.socket.getsockname()[1]
        self.thread=threading.Thread(target=self.run,daemon=True);self.thread.start()
    def run(self):
        try:
            conn,_=self.socket.accept();conn.settimeout(10)
            stream=conn.makefile('rwb',buffering=0)
            def reply(text):stream.write(text.encode()+b'\r\n')
            reply('220 fixture.example ESMTP')
            while True:
                line=stream.readline()
                if not line:break
                line=line.rstrip(b'\r\n');self.commands.append(line.decode('utf8',errors='replace'))
                verb=line.split(b' ',1)[0].upper()
                if verb==b'EHLO':
                    reply('250-fixture.example');reply('250-8BITMIME')
                    if self.utf8:reply('250-SMTPUTF8')
                    if self.tls:reply('250-STARTTLS')
                    reply('250 AUTH LOGIN PLAIN CRAM-MD5')
                elif verb==b'HELO':reply('250 fixture.example')
                elif verb==b'STARTTLS':
                    reply('220 Ready for TLS');stream.close()
                    context=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);context.load_cert_chain(*self.cert)
                    conn=context.wrap_socket(conn,server_side=True);stream=conn.makefile('rwb',buffering=0)
                    self.tls=False
                elif verb==b'AUTH':
                    method=line.split()[1].upper()
                    if method==b'LOGIN':
                        reply('334 VXNlcm5hbWU6');stream.readline();reply('334 UGFzc3dvcmQ6');stream.readline()
                    elif method==b'PLAIN' and len(line.split())==2:reply('334 ');stream.readline()
                    elif method==b'CRAM-MD5':reply('334 '+base64.b64encode(b'<fixture@example>').decode());stream.readline()
                    reply('235 Authentication successful')
                elif verb==b'MAIL':reply('250 Sender accepted')
                elif verb==b'RCPT':reply('550 Recipient rejected' if self.reject else '250 Recipient accepted')
                elif verb==b'DATA':
                    reply('354 Send data');message=[]
                    while True:
                        value=stream.readline()
                        if value==b'.\r\n':break
                        if not value:raise RuntimeError('Truncated DATA')
                        message.append(value[1:] if value.startswith(b'..') else value)
                    self.messages.append(b''.join(message));reply('250 Queued fixture')
                elif verb==b'QUIT':reply('221 Bye');break
                elif verb in [b'RSET',b'NOOP']:reply('250 OK')
                else:reply('500 Unsupported fixture command')
            stream.close();conn.close()
        except socket.timeout:
            pass # Capability/config rejection may occur before opening a socket.
        except Exception as e:self.error=repr(e)
        finally:self.socket.close()
    def finish(self):
        self.thread.join(12)
        if self.thread.is_alive():raise RuntimeError('Fixture did not stop')
        if self.error:raise RuntimeError(self.error)

def run(case, **peer_options):
    peer=Peer(**peer_options);payload=dict(case,port=peer.port)
    proc=subprocess.run(PHP+[str(ROOT/'tests/international-email-worker.php')],input=json.dumps(payload,ensure_ascii=True),text=True,capture_output=True,timeout=20)
    peer.finish()
    if proc.returncode:raise RuntimeError(proc.stdout+proc.stderr)
    result=json.loads(proc.stdout)
    result.update(commands=peer.commands,messages=peer.messages)
    return result

def check(condition,label):
    global checks
    checks+=1
    if not condition:raise AssertionError(label)

checks=0;summary=[]
with tempfile.TemporaryDirectory(prefix='u5-mail-tls-') as temp:
    cert=pathlib.Path(temp)/'cert.pem';key=pathlib.Path(temp)/'key.pem'
    subprocess.run(['openssl','req','-x509','-newkey','rsa:2048','-nodes','-keyout',str(key),'-out',str(cert),'-days','1','-subj','/CN=localhost'],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL,check=True)
    for to,expected,utf8 in [
        ('Anna+Privat@example.ch','Anna+Privat@example.ch',False),
        ('info@straße.ch','info@xn--strae-oqa.ch',False),
        ('info@STRAẞE.CH','info@xn--strae-oqa.ch',False),
        ('info@日本.jp','info@xn--wgv71a.jp',False),
        ('Müller@straße.ch','Müller@xn--strae-oqa.ch',True),
        ('漢字@例子.中国','漢字@xn--fsqu00a.xn--fiqs8s',True),
        ('𠮷@example.ch','𠮷@example.ch',True),
        ('&Auml;nne@stra&#223;e.ch','Änne@xn--strae-oqa.ch',True)]:
        result=run(dict(kind='plain',to=to))
        check(result['ok'],(to,result))
        check('RCPT TO:<'+expected+'>' in result['commands'],(to,result['commands']))
        check(any('SMTPUTF8' in c for c in result['commands'] if c.startswith('MAIL '))==utf8,('SMTPUTF8 envelope',to))
        check(len(result['messages'])==1,('exactly one message',to))
        parsed=email.message_from_bytes(result['messages'][0],policy=email.policy.default)
        check(expected in str(parsed['To']),('To header',to,parsed['To']))
        check('Grüsse' in str(parsed['Subject']),('Subject encoding',to,parsed['Subject']))
        check('Grüsse' in parsed.get_content(),('Body encoding',to))
        summary.append({'to':to,'ok':True,'smtputf8':utf8})
    for auth in ['login','plain','crammd5']:
        result=run(dict(kind='plain',to='Müller@example.ch',auth=auth))
        check(result['ok'],('auth',auth,result));check(any(c.startswith('AUTH ') for c in result['commands']),auth)
    result=run(dict(kind='plain',to='Müller@example.ch',auth='login',tls=True),tls=True,cert=(str(cert),str(key)))
    check(result['ok'],('TLS',result));check('STARTTLS' in result['commands'],'STARTTLS configured');check(sum(c.startswith('EHLO') for c in result['commands'])==2,'EHLO repeated after TLS')
    result=run(dict(kind='plain',to='Müller@example.ch'),utf8=False)
    check(not result['ok'],'unsupported UTF8 denied');check(not result['messages'],'no DATA when unsupported');check(not any(c.startswith('MAIL ') for c in result['commands']),'no MAIL when unsupported')
    result=run(dict(kind='plain',to='Anna@example.ch'),utf8=False)
    check(result['ok'],'ordinary mail works without SMTPUTF8')
    result=run(dict(kind='plain',to='Müller@example.ch'),reject=True)
    check(not result['ok'],'recipient rejection reported');check(not result['messages'],'rejected message never accepted')
    result=run(dict(kind='serial',to='漢字@example.ch',cc='Müller@example.ch,cc@example.ch',bcc='hidden@example.ch'))
    check(result['ok'],('serial',result));check(len(result['messages'])==1,'serial message count')
    parsed=email.message_from_bytes(result['messages'][0],policy=email.policy.default)
    check(parsed.get_content_type()=='multipart/alternative','serial MIME type');check(len(list(parsed.iter_parts()))==2,'plain and HTML alternatives')
    check(parsed.get('Bcc') is None,'Bcc absent from wire headers');check('RCPT TO:<hidden@example.ch>' in result['commands'],'Bcc envelope recipient retained')
print(json.dumps({'checks':checks,'php':PHP,'cases':summary},ensure_ascii=False))
