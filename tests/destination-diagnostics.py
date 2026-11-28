from pathlib import Path
import faulthandler
faulthandler.dump_traceback_later(90, repeat=True)
import email, email.policy, http.client, http.server, json, os, re, shutil, socket, ssl, subprocess, tempfile, threading, time

root = Path(os.environ.get('U5_DOCUMENTATION_SOURCE', Path(__file__).resolve().parent.parent)).resolve()
lab = Path(__file__).resolve().parent
php = [os.environ.get('U5_TEST_PHP', 'php'), '-d', 'mysqli.default_port=' + os.environ['U5CMS_TEST_PORT']]
os.environ['U5CMS_FIXTURE_PASSWORD'] = __import__('secrets').token_urlsafe(36)

def port():
    with socket.socket() as probe:
        probe.bind(('127.0.0.1', 0))
        return probe.getsockname()[1]

with tempfile.TemporaryDirectory(prefix='u5-documentation-') as temporary:
    directory = Path(temporary)
    output = Path(os.environ.get('U5_DOCUMENTATION_OUTPUT', directory/'evidence'))
    output.mkdir(parents=True,exist_ok=True)
    site = directory / 'site'
    shutil.copytree(Path(os.environ['U5_AUDIT_APPLICATION']), site, ignore=shutil.ignore_patterns('.git','tests'))
    fixture = json.loads(subprocess.check_output(php+[str(root/'tests/rename-web-fixture.php'),str(site)],cwd=site,text=True))
    with (site/'config.php').open('a', encoding='latin1') as config:
        config.write("\n$usesessioninsteadofbasicauth='yes';\n")
    setup = """
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';
        require 'connect.inc.php';
        mysql_query("UPDATE accounts SET email='alice@example.org' WHERE id=1");
        foreach (['bob@example.org', 'charlie@example.org'] as $email) {
            mysql_query("INSERT INTO accounts(email,pw,accadmin) SELECT '$email',pw,0 FROM accounts WHERE id=1");
        }
    """
    subprocess.run(php+['-r',setup],cwd=site,check=True,stdout=subprocess.DEVNULL)
    common = '[h:]<div style="height:1900px" aria-hidden="true"></div><p id="audit-target"><em>Gen&#232;ve</em> und <strong>Seidenatlas</strong></p><div style="height:1300px" aria-hidden="true"></div>[:h]'
    pages = {'auditvenue': [common, common]}
    insert = """
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_HOST'] = 'localhost';
        require 'connect.inc.php';
        foreach (json_decode($argv[1], true) as $name => $page) {
            $name = mysql_real_escape_string($name);
            $first = mysql_real_escape_string($page[0]);
            $second = mysql_real_escape_string($page[1]);
            $logins = empty($page[2]) ? '' : '?';
            $modified = time();
            $saved = mysql_query("INSERT INTO resources (name,typ,content_1,content_2,title_1,title_2,logins,lastmut)
                VALUES ('$name','p','$first','$second','$name','$name','$logins',$modified)");
            if (!$saved) throw new RuntimeException(mysql_error());
        }
    """
    subprocess.run(php + ['-r', insert, json.dumps(pages)], cwd=site, check=True, stdout=subprocess.DEVNULL)
    router = directory/'router.php'
    router.write_text("<?php $_SERVER['HTTPS']='on'; return false;\n")
    observer = directory/'observer.php'
    observer.write_text("""<?php
set_error_handler(function($kind,$message,$file,$line){
    if ($kind===E_DEPRECATED && basename($file)==='u5sys.content.php') {
        error_log('TARGET_DIAGNOSTIC '.json_encode([$line,$message,(bool)(error_reporting() & $kind)]));
    }
    return false;
});
register_shutdown_function(function(){
    $files=array_map('basename',get_included_files());
    error_log('TARGET_PATH '.json_encode([$_GET['q'] ?? null,in_array('u5sys.content.php',$files,true)]));
});
""")
    backend_port = port()
    server_log=(directory/'php.log').open('wb')
    server=subprocess.Popen(php+['-d','auto_prepend_file='+str(observer),'-d','opcache.enable=0','-S','127.0.0.1:'+str(backend_port),'-t',str(site),str(router)],cwd=site,stdout=server_log,stderr=server_log)
    tls_port=port()
    with (site/'config.php').open('a', encoding='latin1') as fixture_config:
        fixture_config.write(f"\n$canonicalprotocolanddomain='https://localhost:{tls_port}';\n")
    subprocess.run(['openssl','req','-x509','-newkey','rsa:2048','-nodes','-keyout',str(directory/'key.pem'),'-out',str(directory/'cert.pem'),'-days','1','-subj','/CN=localhost','-addext','subjectAltName=DNS:localhost,IP:127.0.0.1'],check=True,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    class Proxy(http.server.BaseHTTPRequestHandler):
        def log_message(self,*args): pass
        def handle_request(self):
            connection=http.client.HTTPConnection('127.0.0.1',backend_port,timeout=20)
            length=int(self.headers.get('Content-Length','0'))
            body=self.rfile.read(length) if length else None
            headers={k:v for k,v in self.headers.items() if k.lower() not in ['connection','accept-encoding']}
            headers['X-Forwarded-Proto']='https'
            try:
                connection.request(self.command,self.path,body,headers)
                response=connection.getresponse();data=response.read()
                self.send_response(response.status)
                for key,value in response.getheaders():
                    if key.lower() not in ['connection','transfer-encoding','content-length']:self.send_header(key,value)
                self.send_header('Content-Length',str(len(data)));self.end_headers();self.wfile.write(data)
            finally:connection.close()
        do_GET=handle_request
        do_POST=handle_request

    tls=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);tls.load_cert_chain(directory/'cert.pem',directory/'key.pem')
    class ThreadedTLS(http.server.ThreadingHTTPServer):
        def finish_request(self, request, address):
            request.settimeout(25)
            with tls.wrap_socket(request, server_side=True) as stream:
                super().finish_request(stream, address)
    proxy = ThreadedTLS(('127.0.0.1', tls_port), Proxy)
    thread=threading.Thread(target=proxy.serve_forever,daemon=True);thread.start()
    try:
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1',backend_port),timeout=1):break
            except OSError:time.sleep(.05)
        for query in ['', 'Seidenatlas', 'Gen%C3%A8ve', 'Gen%u00E8ve', '%u0153%u4E2D', '%uD83D%uDE00', '%uZZZZ']:
            log_path = directory/'php.log'
            offset = log_path.stat().st_size
            conn = http.client.HTTPConnection('127.0.0.1',backend_port,timeout=15)
            conn.request('GET','/index.php?c=auditvenue&l=en&q='+query,headers={'Host':'localhost:'+str(tls_port)})
            res = conn.getresponse()
            html = res.read()
            conn.close()
            assert res.status == 200 and b'audit-target' in html, (res.status,html[:250])
            for attempt in range(40):
                log = log_path.read_bytes()[offset:].decode(errors='replace')
                if 'TARGET_PATH ' in log: break
                time.sleep(.025)
            matches = re.findall(r'TARGET_PATH (.+)',log)
            assert len(matches)==1 and json.loads(matches[0])[1], (query,matches,log[-900:])
            notices = [json.loads(v) for v in re.findall(r'TARGET_DIAGNOSTIC (.+)',log)]
            print('OBSERVED',json.dumps([query,json.loads(matches[0]),notices]),flush=True)
    finally:
        print('Stopping the isolated TLS proxy', flush=True)
        proxy.shutdown();proxy.server_close();server.terminate();server.wait(timeout=10)
        subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),'cleanup',fixture['database']],check=True)
