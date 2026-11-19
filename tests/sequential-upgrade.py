"""Apply the successive source archives to one populated, disposable .7 installation."""
import base64
import json
import os
from pathlib import Path
import shutil
import socket
import subprocess
import sys
import tempfile
import time
import urllib.request

repo = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(repo/'tests'))
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
php = [os.environ['U5_TEST_PHP'], '-d','display_errors=0','-d', 'mysqli.default_port='+os.environ['U5CMS_TEST_PORT']]
auth = 'Basic '+base64.b64encode(('auditadmin:'+os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode()
release_refs = [
    ('12.9.5.7', 'v12.9.5.7'),
    ('13.0.0', 'v13.0.0'),
    ('14.0.0', 'v14.0.0'),
    ('15.0.0', 'v15.0.0'),
    ('16.0.0', 'v16.0.0'),
    ('17.0.0', 'HEAD'),
]
with tempfile.TemporaryDirectory(prefix='u5-evolution-') as temporary:
    baseline=Path(temporary)/'baseline'
    baseline.mkdir()
    archive=Path(temporary)/'baseline.tar'
    subprocess.run(['git','-C',str(repo),'archive','--output='+str(archive),'v12.9.5.7'],check=True)
    subprocess.run(['tar','-xf',str(archive),'-C',str(baseline)],check=True)
    fixture=str(baseline/'tests/rename-web-fixture.php')
    site = Path(temporary) / 'site'
    shutil.copytree(baseline,site,ignore=shutil.ignore_patterns('.git','tests'))
    db = json.loads(subprocess.check_output(php+[fixture,str(site)],cwd=site))['database']
    def query(statement):
        code = """require 'config.php'; if ($db!==$argv[1]) exit(1); $c=new mysqli($host,$username,$password,$db); $result=$c->query($argv[2]); if($result!==true) echo json_encode($result->fetch_row());"""
        return subprocess.check_output(php+['-r',code,db,statement],cwd=site)
    def get(path):
        request=urllib.request.Request(origin+path, headers={'Authorization':auth})
        with urllib.request.urlopen(request,timeout=30) as response: return response.read()
    try:
        css='body { color: rgb(19, 35, 51); } /* evolution fixture */'
        query("UPDATE resources SET content_1=CONCAT(content_1,CHAR(10),'"+css+"') WHERE name='cssstyle' AND deleted=0")
        for name in ['cssstyle.tpl','cssstyle.css']:
            with (site/'r'/name).open('ab') as style_file: style_file.write(b'\n'+css.encode())
        query("UPDATE resources SET content_1=CONCAT(COALESCE(content_1,''),' Evolution fixture') WHERE name='start' AND deleted=0")
        row="SELECT name,SHA2(content_1,256) FROM resources WHERE name='start' AND content_1 LIKE '%Evolution fixture' AND deleted=0"
        original=query(row)
        assert json.loads(original)[1]
        config=(site/'config.php').read_bytes()
        uploaded=site/'r/evolution-upload.txt'
        uploaded.write_text('A custom file kept throughout all upgrades.')
        for version,ref in release_refs:
            if version!='12.9.5.7':
                archive=str(Path(temporary)/'upgrade.tar')
                subprocess.run(['git','-C',str(repo),'archive','--format=tar','--output='+archive,ref],check=True)
                subprocess.run(['tar','--exclude=r','-xf',archive,'-C',str(site)],check=True)
                subprocess.run(['tar','-xf',archive,'-C',str(site),'r/runonce.php'],check=True)
            with socket.socket() as probe:
                probe.bind(('127.0.0.1',0))
                port=probe.getsockname()[1]
            origin='http://127.0.0.1:'+str(port)
            with (Path(temporary)/'http.log').open('w') as log:
                server=subprocess.Popen(php+['-S','127.0.0.1:'+str(port),'-t',str(site)],cwd=site,stdout=log,stderr=log)
                try:
                    for attempt in range(80):
                        try:
                            with socket.create_connection(('127.0.0.1',port),0.1):break
                        except OSError:time.sleep(.05)
                    get('/u5admin/zr.php')
                    assert not (site/'r/runonce.php').exists(), version+' runonce'
                    assert ('u5CMS version '+version+' from').encode() in get('/index.php?c=start')
                    assert query(row)==original and (site/'config.php').read_bytes()==config
                    assert uploaded.read_text()=='A custom file kept throughout all upgrades.'
                    obj,style=('cssstyle','cssstyle') if version.startswith(('12.','13.')) else ('style!css','style')
                    saved=json.loads(query("SELECT SHA2(content_1,256),LOCATE('"+css+"',content_1) FROM resources WHERE name='"+obj+"' AND deleted=0"))
                    if version=='12.9.5.7': baseline_style=saved
                    assert int(saved[1])>0 and saved==baseline_style,(version,'source',saved,baseline_style)
                    assert css.encode() in get('/r/'+style+'.css'),version+' export'
                    assert css.encode() in (site/'r'/(obj+'.tpl')).read_bytes(),version+' reset template'
                    print(version,'served version, page name and content hash, config, upload, custom database style, served CSS and reset template passed',flush=True)
                    if (version,ref)==release_refs[-1]:
                        reset_name='u5admin/reactivateinitialu5cmslayout.php'
                        reset=site/reset_name
                        assert b'TO RESET CSS' in get('/'+reset_name)
                        lines=reset.read_bytes().splitlines(keepends=True)
                        assert lines[1].startswith(b"die('TO RESET CSS")
                        reset.write_bytes(b''.join(lines[:1]+lines[2:]))
                        assert b'TO OVERWRITE' in get('/'+reset_name)
                        packaged=subprocess.check_output(['git','-C',str(repo),'show','v16.0.0:r/style!css.tpl'])
                        (site/'r'/'style!css.tpl').write_bytes(packaged)
                        reply=get('/'+reset_name+'?iswear=iknowthatthisoverwritesthecurrentlayout')
                        assert b'Layout reset completed' in reply,reply[-800:]
                        assert b'system-ui' in get('/r/style.css') and css.encode() not in get('/r/style.css')
                        assert query(row)==original and uploaded.is_file()
                        print('Guarded opt-in reset and re-publication served the packaged typography; page data and upload retained.',flush=True)
                finally:
                    server.terminate()
                    server.wait(timeout=10)
    finally:subprocess.run(php+[fixture,'cleanup',db],check=True)
