from pathlib import Path
import http.client, http.server, json, os, re, shutil, socket, ssl, subprocess, tempfile, threading, time

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
    shutil.copytree(root, site, ignore=shutil.ignore_patterns('.git','tests'))
    fixture = json.loads(subprocess.check_output(php+[str(root/'tests/rename-web-fixture.php'),str(site)],cwd=site,text=True))
    config = (site/'config.php').read_text(encoding='latin1')
    config += "\n$usesessioninsteadofbasicauth='yes';\n$u5trusteddeviceenabled='yes';\n$u5trusteddeviceusers=array('alice@example.org'=>2,'bob@example.org'=>1);\n"
    (site/'config.php').write_text(config,encoding='latin1')
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
    lesson = "[[[[A small workshop]]]]\nUse [b]precise words[/] and [i]short sentences[/].\n[[[Reusable content]]]\n[$$$:docfragment]\n[Open the fragment:docfragment]\n[h:]<p id=\"literal-markers\">&#91;b&#93;Keep these markers&#91;/&#93;</p><section id=\"included-html\">[$$$:docfragment]</section>[:h]"
    form = "[fo:]\n[fi:]\n[le:]Workshop registration[:le]\n[h:]\n<input type=\"hidden\" name=\"thanks\" value=\"documentationSaved()\">\n<input type=\"hidden\" name=\"in2my\" value=\"off\">\n<input type=\"hidden\" name=\"em2cu\" value=\"off\">\n<input type=\"hidden\" name=\"em2me\" value=\"off\">\n<label for=\"participant\">Your name</label><input id=\"participant\" name=\"participant_mandatory\">\n<button type=\"submit\">Register for the workshop</button>\n<p id=\"submission-status\"></p>\n<script>function documentationSaved(){document.getElementById('submission-status').textContent='Your registration was saved.';}</script>\n[:h]\n[:fi]\n[:fo]"
    pages={'doclesson':['First-language version.',lesson], 'docfragment':['A reusable opening.','A reusable opening.'], 'docfallback':['Fallback text from the first language.',''], 'docform':[form,form], 'docsearch':['A search exercise.','The atlasquartz workshop begins here.']}
    # Run each exercise from the same page sources that readers copy from the handbook.
    example_pages = {}
    for guide in ('dat.md', 'dat/parameters.md', 'forms/survey-results.md', 'forms/manage-entries.md'):
        handbook = (root/'docs'/guide).read_text(encoding='utf-8')
        example_pages.update(re.findall(r'^### `([a-z]+)`\n\n```text\n(.*?)\n```$', handbook, re.MULTILINE | re.DOTALL))
    expected_pages = {'courseentry', 'courses', 'coursedetail', 'mycourses', 'mycourse', 'datcheck',
                      'workshoppoll', 'pollresults', 'polltitles', 'loanentry', 'myloans', 'timepreference'}
    if set(example_pages) != expected_pages:
        raise ValueError('Expected the named page sources in the DAT and form guides')
    pages.update({name: [source, source, True] for name, source in example_pages.items()})
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
    backend_port = port()
    server_log=(directory/'php.log').open('wb')
    server=subprocess.Popen(php+['-d','opcache.enable=0','-S','127.0.0.1:'+str(backend_port),'-t',str(site),str(router)],cwd=site,stdout=server_log,stderr=server_log)
    tls_port=port()
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
    proxy=http.server.ThreadingHTTPServer(('127.0.0.1',tls_port),Proxy)
    tls=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);tls.load_cert_chain(directory/'cert.pem',directory/'key.pem')
    proxy.socket=tls.wrap_socket(proxy.socket,server_side=True)
    thread=threading.Thread(target=proxy.serve_forever,daemon=True);thread.start()
    try:
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1',backend_port),timeout=1):break
            except OSError:time.sleep(.05)
        environment=os.environ.copy();environment.update({'U5_DOCUMENTATION_SOURCE':str(root),'U5_DOCUMENTATION_SITE':str(site),'U5_DOCUMENTATION_ORIGIN':'https://localhost:'+str(tls_port),'U5_DOCUMENTATION_OUTPUT':str(output)})
        subprocess.run(['node', str(lab/'documentation-dat.cjs')], env=environment, check=True)
        subprocess.run(['node',str(lab/'documentation-trusted-browser.cjs')],env=environment,check=True)
        print((output/'trusted-browser-observations.json').read_text())
        subprocess.run(['node',str(lab/'documentation-content.cjs')],env=environment,check=True)
    finally:
        proxy.shutdown();proxy.server_close();server.terminate();server.wait(timeout=10)
        shutil.copyfile(directory/'php.log',output/'documentation-server.log')
        subprocess.run(php+[str(root/'tests/rename-web-fixture.php'),'cleanup',fixture['database']],check=True)
