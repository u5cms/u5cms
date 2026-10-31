#!/usr/bin/env python3
"""Real HTTPS OIDC provider, callback, CMS and browser tests. Uses a disposable DB.

Requires cryptography, PHP/cURL/mysqli and U5CMS_TEST_PORT. The ephemeral provider
is an intentionally simple test double; its keys and credentials never persist.
"""
from pathlib import Path
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import urlparse, parse_qs, urlencode
from cryptography import x509
from cryptography.x509.oid import NameOID
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import rsa, padding
import base64, datetime, hashlib, http.client, ipaddress, json, os, secrets
import shutil, socket, ssl, subprocess, sys, tempfile, threading, time, urllib.request, urllib.error

REPO=Path(__file__).resolve().parent.parent
PHP=[os.environ.get('U5_TEST_PHP','php')]+json.loads(os.environ.get('U5_TEST_PHP_ARGS','[]'))
def b64(data): return base64.urlsafe_b64encode(data).rstrip(b'=').decode()
def number(n): return b64(n.to_bytes((n.bit_length()+7)//8,'big'))
def port():
    with socket.socket() as s: s.bind(('127.0.0.1',0)); return s.getsockname()[1]
def fixture(*args):
    return subprocess.run(PHP+[str(REPO/'tests/oidc-web-fixture.php'),*map(str,args)],check=True,capture_output=True,text=True).stdout

with tempfile.TemporaryDirectory(prefix='u5-oidc-web-') as temporary:
    work=Path(temporary);root=work/'www';site=root/'cms';site.parent.mkdir()
    shutil.copytree(REPO,site,ignore=shutil.ignore_patterns('.git','tests'))
    session_dir=work/'sessions';session_dir.mkdir()
    key=rsa.generate_private_key(public_exponent=65537,key_size=2048)
    subject=x509.Name([x509.NameAttribute(NameOID.COMMON_NAME,'u5CMS ephemeral test CA')])
    now=datetime.datetime.now(datetime.timezone.utc)
    cert=(x509.CertificateBuilder().subject_name(subject).issuer_name(subject).public_key(key.public_key())
          .serial_number(x509.random_serial_number()).not_valid_before(now-datetime.timedelta(minutes=1))
          .not_valid_after(now+datetime.timedelta(days=1))
          .add_extension(x509.BasicConstraints(ca=True,path_length=None),critical=True)
          .add_extension(x509.SubjectAlternativeName([x509.IPAddress(ipaddress.ip_address('127.0.0.1'))]),critical=False)
          .sign(key,hashes.SHA256()))
    ca=work/'ca.pem';ca.write_bytes(cert.public_bytes(serialization.Encoding.PEM))
    keyfile=work/'key.pem';keyfile.write_bytes(key.private_bytes(serialization.Encoding.PEM,serialization.PrivateFormat.PKCS8,serialization.NoEncryption()))
    tls=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);tls.load_cert_chain(ca,keyfile)
    backend_port=port();cms_port=port();idp_port=port()
    base=f'https://127.0.0.1:{cms_port}/cms';issuer=f'https://127.0.0.1:{idp_port}/issuer'
    setup=json.loads(fixture(site,issuer,base,ca));database=setup['database']
    tickets={};codes={};mode='normal';token_requests=[];discovery_requests=[]
    signing_key=rsa.generate_private_key(public_exponent=65537,key_size=2048)
    signing_kid='key-1'
    def jwk():
        n=signing_key.public_key().public_numbers()
        return {'kty':'RSA','use':'sig','alg':'RS256','kid':signing_kid,'n':number(n.n),'e':number(n.e)}
    def jwt(payload):
        header=b64(json.dumps({'alg':'RS256','kid':signing_kid}).encode())
        data=(header+'.'+b64(json.dumps(payload).encode())).encode()
        return data.decode()+'.'+b64(signing_key.sign(data,padding.PKCS1v15(),hashes.SHA256()))
    class Quiet(BaseHTTPRequestHandler):
        def log_message(self,*args): pass
        def respond(self,status,body=b'',kind='application/json',headers=()):
            self.send_response(status);self.send_header('Content-Type',kind)
            for name,value in headers: self.send_header(name,value)
            self.send_header('Content-Length',str(len(body)));self.end_headers();self.wfile.write(body)
    class IdP(Quiet):
        def do_GET(self):
            global mode, signing_key, signing_kid
            p=urlparse(self.path);q={k:v[0] for k,v in parse_qs(p.query).items()}
            if p.path=='/control':
                mode=q.get('mode','normal')
                if q.get('rotate'):
                    signing_key=rsa.generate_private_key(public_exponent=65537,key_size=2048);signing_kid='rotated-key'
                self.respond(200,b'{}');return
            if p.path=='/issuer/.well-known/openid-configuration':
                discovery_requests.append(p.path)
                metadata={'issuer':issuer,'authorization_endpoint':issuer+'/authorize','token_endpoint':issuer+'/token',
                    'jwks_uri':issuer+'/keys','end_session_endpoint':issuer+'/logout','response_types_supported':['code'],
                    'id_token_signing_alg_values_supported':['RS256'],'code_challenge_methods_supported':['S256'],
                    'token_endpoint_auth_methods_supported':['client_secret_basic'],'authorization_response_iss_parameter_supported':True}
                if mode=='wrong_discovery_issuer':metadata['issuer']=issuer+'-wrong'
                if mode=='untrusted_keys':metadata['jwks_uri']='https://untrusted.invalid/keys'
                if mode=='discovery_redirect':self.respond(302,headers=[('Location',issuer+'/keys')]);return
                if mode=='oversized_metadata':metadata['padding']='x'*300000
                self.respond(200,json.dumps(metadata).encode());return
            if p.path=='/issuer/keys':self.respond(200,json.dumps({'keys':[jwk()]}).encode());return
            if p.path=='/issuer/authorize':
                assert q['client_id']=='fixture-client' and q['response_type']=='code' and q['response_mode']=='query'
                assert q['redirect_uri']==base+'/oidc/callback.php' and q['code_challenge_method']=='S256'
                assert 'client_secret' not in q and 'code_verifier' not in q
                ticket=secrets.token_urlsafe(24);tickets[ticket]=q
                buttons=''.join(f'<button name="sub" value="{s}">{s}</button>' for s in ['admin-sub','editor-sub','member-sub','page-sub','no-rights-sub','unknown-sub','cancel'])
                body=f'<!doctype html><html><title>Test Identity Provider</title><form action="/issuer/approve"><input type="hidden" name="ticket" value="{ticket}">{buttons}</form></html>'
                self.respond(200,body.encode(),'text/html');return
            if p.path=='/issuer/approve':
                request=tickets.pop(q['ticket']);response={'state':request['state'],'iss':issuer}
                if q['sub']=='cancel':response['error']='access_denied'
                else:
                    code=secrets.token_urlsafe(32);codes[code]=dict(request,sub=q['sub'],mode=mode);response['code']=code
                    if mode=='missing_response_issuer':response.pop('iss')
                    if mode=='wrong_response_issuer':response['iss']=issuer+'-wrong'
                self.respond(303,headers=[('Location',request['redirect_uri']+'?'+urlencode(response))]);return
            if p.path=='/issuer/logout':
                assert q['post_logout_redirect_uri']==base+'/oidc/loggedout.php'
                assert q['id_token_hint'].count('.')==2 and q['client_id']=='fixture-client'
                self.respond(303,headers=[('Location',q['post_logout_redirect_uri']+'?'+urlencode({'state':q['state']}))]);return
            self.respond(404)
        def do_POST(self):
            if urlparse(self.path).path!='/issuer/token':self.respond(404);return
            form={k:v[0] for k,v in parse_qs(self.rfile.read(int(self.headers['Content-Length'])).decode()).items()}
            token_requests.append(form)
            expected='Basic '+base64.b64encode(b'fixture-client:fixture-secret').decode()
            assert self.headers.get('Authorization')==expected
            request=codes.pop(form['code'],None)
            if request is None:self.respond(400,b'{"error":"invalid_grant"}');return
            assert form['redirect_uri']==request['redirect_uri'] and form['grant_type']=='authorization_code'
            assert b64(hashlib.sha256(form['code_verifier'].encode()).digest())==request['code_challenge']
            payload={'iss':issuer,'sub':request['sub'],'aud':'fixture-client','iat':int(time.time()),'exp':int(time.time())+600,'nonce':request['nonce'],
                     'email':'admin@example.org','email_verified':True}
            if request['sub'] in ['email-new-sub','email-recycled-sub']:payload['email']='new-member@example.org'
            if request['sub']=='email-unicode-sub':payload['email']='Müller@BÜCHER.ch'
            if request['mode']=='email_changed':payload['email']='changed-member@example.org'
            if request['mode']=='email_unverified':payload['email_verified']=False
            if request['mode']=='bad_nonce':payload['nonce']='wrong'
            if request['mode']=='bad_audience':payload['aud']='other-client'
            if request['mode']=='bad_token_issuer':payload['iss']=issuer+'-wrong'
            if request['mode']=='expired_token':payload['exp']=int(time.time())-60
            response={'id_token':jwt(payload),'access_token':'ACCESS-TOKEN-NEVER-SAVE','token_type':'Bearer'}
            if request['mode']=='missing_id_token':response.pop('id_token')
            if request['mode']=='bad_json':self.respond(200,b'{broken');return
            if request['mode']=='token_redirect':self.respond(307,headers=[('Location',issuer+'/keys')]);return
            self.respond(200,json.dumps(response).encode())
    class Proxy(Quiet):
        def forward(self):
            conn=http.client.HTTPConnection('127.0.0.1',backend_port,timeout=30)
            body=self.rfile.read(int(self.headers.get('Content-Length',0)))
            headers={k:v for k,v in self.headers.items() if k.lower() not in ['host','connection','accept-encoding']}
            headers['Host']=f'127.0.0.1:{cms_port}'
            conn.request(self.command,self.path,body,headers);response=conn.getresponse();data=response.read()
            self.send_response(response.status)
            for k,v in response.getheaders():
                if k.lower() not in ['connection','content-length','transfer-encoding']:self.send_header(k,v)
            try:
                self.send_header('Content-Length',str(len(data)));self.end_headers();self.wfile.write(data)
            except (BrokenPipeError, ConnectionResetError, ssl.SSLEOFError):
                pass  # Browser navigations may cancel old frame/resource responses.
            finally:conn.close()
        do_GET=forward
        do_POST=forward
    router=work/'router.php'
    router.write_text("<?php\n$_SERVER['SERVER_PORT']="+str(cms_port)+";\n"+'''
$_SERVER['HTTPS']='on';
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$root=$_SERVER['DOCUMENT_ROOT'];$file=realpath($root.$path);
if ($file===false || strpos($file,$root.'/')!==0) {http_response_code(404);return;}
if (is_dir($file)) $file.='/index.php';
if (substr($file,-4)==='.php') {chdir(dirname($file));require $file;return;}
return false;
''')
    servers=[]
    for listen,handler in [(idp_port,IdP),(cms_port,Proxy)]:
        server=ThreadingHTTPServer(('127.0.0.1',listen),handler);server.socket=tls.wrap_socket(server.socket,server_side=True)
        threading.Thread(target=server.serve_forever,daemon=True).start();servers.append(server)
    if os.environ.get('U5CMS_PHP9_AUDIT'):
        router.write_text('<?php require '+json.dumps(str(REPO/'tests/php9-guard.php'))+'; ?>\n'+router.read_text())
    with (work/'php.log').open('w+') as log:
        php=subprocess.Popen(PHP+['-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-d','session.save_path='+str(session_dir),
            '-d','output_buffering='+os.environ.get('U5_OIDC_OUTPUT_BUFFERING','0'),'-S',f'127.0.0.1:{backend_port}','-t',str(root),str(router)],cwd=root,stdout=log,stderr=log)
        try:
            for attempt in range(100):
                try:
                    with socket.create_connection(('127.0.0.1',backend_port),timeout=.1):break
                except OSError:time.sleep(.05)
            env=os.environ.copy();env['U5_OIDC_LEGACY_COOKIE']=setup['legacy_cookie'];env['U5_OIDC_TEST_DATABASE']=database
            env['U5_OIDC_SESSION_DIR']=str(session_dir)
            subprocess.run([sys.executable,str(REPO/'tests/oidc-web-http.py'),base,issuer,str(site),str(ca)],env=env,check=True)
            if not os.environ.get('U5_OIDC_HTTP_ONLY'):
                subprocess.run(['node',str(REPO/'tests/oidc-web-browser.cjs'),base,issuer,str(site)],env=env,check=True)
            assert token_requests and all('code_verifier' in r for r in token_requests)
            assert len(discovery_requests)>1
            content=''.join(p.read_text(errors='replace') for p in session_dir.iterdir())
            assert 'ACCESS-TOKEN-NEVER-SAVE' not in content
            log.flush();logs=(work/'php.log').read_text()
            assert 'ACCESS-TOKEN-NEVER-SAVE' not in logs and 'fixture-secret' not in logs
            print('OIDC HTTPS provider: real TLS, PKCE and client authentication verified; no access-token persistence or secret logging')
        except Exception:
            log.flush();print((work/'php.log').read_text()[-6500:]);raise
        finally:
            php.terminate();php.wait(timeout=10)
            for server in servers:server.shutdown();server.server_close()
            fixture('cleanup',database)
