#!/usr/bin/env python3
"""Additional pass 2: real writers, live lock, interrupted rename, recovery/retry.

Only disposable installations/databases. Native SQL and file hashes prove that
rejected writes do not mutate business records, history or published bytes.
"""
import argparse, hashlib, importlib.util, json, os, pathlib, re, selectors
import subprocess, tempfile, urllib.parse

HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('reference',HERE/'main-reference-regression.py')
reference=importlib.util.module_from_spec(spec);spec.loader.exec_module(reference)
PHP=os.environ.get('U5_TEST_PHP','php')

def snapshot(site):
    fields='name,typ,deleted,lastmut,operator,logins,content_1,content_2,content_3,content_4,content_5,title_1'
    rows={table:site.sql('SELECT '+fields+' FROM '+table+' ORDER BY name,deleted,lastmut,content_1')['rows'] for table in ['resources','resources_log']}
    files={}
    for name in ['lock_source','lock_renamed','blockednew','blockedcopy','blockedrename']:
        directory=site.root/'r'/name
        if directory.is_dir():
            for path in directory.rglob('*'):
                if path.is_file():files[str(path.relative_to(site.root))]=hashlib.sha256(path.read_bytes()).hexdigest()
    for name in ['base.css','htmltemplate.html','mobile.js']:
        path=site.root/'r'/name
        if path.is_file():files['r/'+name]=hashlib.sha256(path.read_bytes()).hexdigest()
    return {'rows':rows,'files':files}

def token(site):
    response=site.request('u5admin/new.php?typ=p',user='referenceadmin')
    value=re.search(r'name=["\']u5scrtytknfrfrms["\'][^>]*value=["\']([^"\']+)',response['body'])
    if not value:raise AssertionError('Actual form token not found')
    return value.group(1)

def changes(name,csrf,revision='1700000000'):
    values={'name':name,'page':name,'coco':revision,'ishomepage':'0','hidden':'0','logins':'','u5scrtytknfrfrms':csrf,'u5resourceid':'recovery_probe','u5saveid':'recovery_probe'}
    for language in range(1,6):
        for field in ['content','title','desc','key']:values[field+'_'+str(language)]='BLOCKED-WRITE' if field=='content' else ''
    return values

def probe_writers(site,phase,csrf,issues):
    plain=changes('lock_source',csrf)
    code=changes('base!css',csrf)
    requests=[
        ('page','u5admin/savepage.php?i=i1',plain),
        ('metadata','u5admin/meta2.php?typ=p',plain),
        ('code','u5admin/coding2.php?typ=c',code),
        ('new','u5admin/new2.php?typ=p',{'name':'blockednew','u5scrtytknfrfrms':csrf}),
        ('copy','u5admin/copy2.php?name=lock_source',{'name':'blockedcopy','go':'no','wn':'i1','u5scrtytknfrfrms':csrf}),
        ('archive','u5admin/archiveunarchive2.php?name='+hashlib.md5(b'lock_source').hexdigest(),{'name':'lock_source','u5scrtytknfrfrms':csrf}),
        ('delete','u5admin/delete2.php?name='+hashlib.md5(b'lock_source').hexdigest(),{'name':'lock_source','dfiles':'yes','u5scrtytknfrfrms':csrf}),
        ('rename','u5admin/rename2.php?name=lock_source',{'name':'blockedrename','ulinks':'yes','u5scrtytknfrfrms':csrf}),
    ]
    observations=[]
    for action,path,payload in requests:
        # Older releases do not yet coordinate every page/metadata writer.
        # Exercise the actual lock consumers in that release, not a later API.
        if action in ['page','metadata'] and not (site.root/'u5admin/resource-rename.inc.php').is_file():
            continue
        before=snapshot(site);response=site.request(path,payload,user='referenceadmin');after=snapshot(site)
        kept=after==before
        record={'phase':phase,'action':action,'status':response['status'],'retry_after':response['headers'].get('Retry-After'),'state_preserved':kept,'explanation':'Retry' in response['body'] or 'retry' in response['body']}
        observations.append(record)
        if not kept:raise AssertionError('Blocked '+action+' changed resource/history/file state')
        if response['status']!=409:issues.append(phase+'/'+action+': busy operation returns '+str(response['status'])+' instead of a controlled 409')
        elif record['retry_after']!='2' or not record['explanation']:issues.append(phase+'/'+action+': missing retry contract')
    return observations

def exercise(site):
    assert site.request('u5admin/zr.php',user='referenceadmin')['status']==200
    site.resource('lock_source',[b'LOCK-SOURCE \xdcber \x80'],lastmut=1700000000,operator='referenceadmin')
    site.resource('lock_parent',[b'Parent [lock_source]'],lastmut=1700000000,operator='referenceadmin')
    fields='name,typ,deleted,lastmut,operator,logins,content_1,content_2,content_3,content_4,content_5,title_1'
    site.sql('INSERT INTO resources_log('+fields+') SELECT '+fields+" FROM resources WHERE name IN ('lock_source','lock_parent')")
    directory=site.root/'r/lock_source';directory.mkdir()
    original_bytes=b'\x00\xffRESOURCE-BYTES\r\n';(directory/'lock_source_de.txt').write_bytes(original_bytes)
    csrf=token(site);issues=[];observations=[]
    code='require '+json.dumps(str(site.root/'codeassets.inc.php'))+';$release=u5CodeMigrationLock('+json.dumps(str(site.root))+');echo "READY\\n";flush();fgets(STDIN);$release();'
    worker=subprocess.Popen([PHP,'-r',code],stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,text=True)
    try:
        ready=selectors.DefaultSelector();ready.register(worker.stdout,selectors.EVENT_READ)
        assert ready.select(5) and worker.stdout.readline().strip()=='READY','Live lock worker did not become ready'
        observations+=probe_writers(site,'active-lock',csrf,issues)
    finally:
        worker.communicate('\n',timeout=10)
        assert worker.returncode==0,'Lock worker failed'
    has_resumable=(site.root/'u5admin/resource-rename.inc.php').is_file()
    if has_resumable:
        interruption='''$_SERVER['REQUEST_URI']='/';$_SERVER['HTTP_HOST']='fixture.invalid';$_SERVER['REMOTE_ADDR']='127.0.0.1';$_SERVER['PHP_AUTH_USER']='referenceadmin';$_SERVER['PHP_AUTH_PW']=hash_hmac('sha256','reference',getenv('U5CMS_FIXTURE_PASSWORD'));
function u5CodeMigrationTestCheckpoint($phase){if($phase==='rename:directory')throw new RuntimeException('ACTUAL-INTERRUPTION');}
require 'connect.inc.php';require 'u5admin/resource-rename.inc.php';try{u5ResourceRename(__DIR__,'lock_source','lock_renamed');echo 'UNEXPECTED-COMPLETE';}catch(RuntimeException $e){echo $e->getMessage();}'''
        path=site.root/'interrupt-rename.php';path.write_text('<?php '+interruption)
        result=subprocess.run([PHP,str(path)],cwd=site.root,capture_output=True,text=True)
        assert result.returncode==0 and 'ACTUAL-INTERRUPTION' in result.stdout,result.stdout+result.stderr
        assert list((site.root/'fileversions').glob('resource-rename-*.pending.php')),'Real interrupted rename did not retain its checkpoint'
        observations+=probe_writers(site,'interrupted-rename',csrf,issues)
        payload={'name':'lock_renamed','ulinks':'yes','u5scrtytknfrfrms':csrf}
        response=site.request('u5admin/rename2.php?name=lock_source',payload,user='referenceadmin')
        assert response['status']==200 and 'Renamed' in response['body'],response['body'][-1000:]
        row=site.sql("SELECT name,typ,deleted,content_1,lastmut FROM resources WHERE name='lock_renamed'")['rows']
        assert len(row)==1 and row[0]['typ']=='p' and row[0]['deleted']=='0' and 'LOCK-SOURCE' in row[0]['content_1']
        assert not site.sql("SELECT name FROM resources WHERE name='lock_source' AND deleted!=1")['rows']
        assert '[lock_renamed]' in site.sql("SELECT content_1 FROM resources WHERE name='lock_parent'")['rows'][0]['content_1']
        assert (site.root/'r/lock_renamed/lock_renamed_de.txt').read_bytes()==original_bytes
        assert not list((site.root/'fileversions').glob('resource-rename-*.pending.php'))
        before=snapshot(site);site.request('u5admin/rename2.php?name=lock_source',payload,user='referenceadmin')
        assert snapshot(site)==before,'Retry changed a completed resource rename'
        stale=site.request('u5admin/savepage.php?i=i1',changes('lock_source',csrf),user='referenceadmin')
        assert stale['status']==409 and snapshot(site)==before,'Stale source editor was allowed to overwrite after rename'
        final=changes('lock_renamed',csrf,row[0]['lastmut']);final['content_1']='AFTER-RECOVERY'
    else:
        final=changes('lock_source',csrf)
    response=site.request('u5admin/meta2.php?typ=p',final,user='referenceadmin')
    assert response['status']==200
    assert site.sql('SELECT content_1 FROM resources WHERE name='+reference.literal(final['name']))['rows'][0]['content_1']==final['content_1']
    return {'writers':observations,'issues':issues,'actual_interruption_resumed':has_resumable,'normal_write_after_release':True}

def main():
    parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('--baseline',type=pathlib.Path,required=True);parser.add_argument('--target',action='append',default=[]);parser.add_argument('--output',type=pathlib.Path,required=True)
    args=parser.parse_args();report={'pass':'Additional 2: actual lock and interruption recovery','results':[]};args.output.parent.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='u5-recovery-http-') as temp:
        for target in args.target:
            label,source=target.split('=',1);site=reference.Site(pathlib.Path(source),args.baseline,pathlib.Path(temp),label)
            try:record={'label':label,**exercise(site)}
            except Exception as error:record={'label':label,'error':str(error)}
            finally:site.close()
            report['results'].append(record);args.output.write_text(json.dumps(report,indent=2))
            print(json.dumps({'label':label,'issues':record.get('issues'),'error':record.get('error')}),flush=True)
    return int(any(r.get('issues') or r.get('error') for r in report['results']))
if __name__=='__main__':raise SystemExit(main())
