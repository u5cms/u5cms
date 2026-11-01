#!/usr/bin/env python3
"""Run the original installation first, freeze its observations, then compare.

Requires a disposable TCP MariaDB, U5CMS_TEST_PORT and U5_TEST_PHP. The immutable
original source is supplied with --baseline; --target LABEL=PATH is repeatable.
No target CMS helper is used to seed, upgrade, inspect or define expectations.
All fixtures live in private temporary directories and databases and are removed.
"""
import argparse, base64, datetime, hashlib, json, os, pathlib, re, shutil
import socket, subprocess, tempfile, time, urllib.error, urllib.parse, urllib.request

HERE=pathlib.Path(__file__).resolve().parent
PHP=os.environ.get('U5_TEST_PHP','php')
CHECKS=0

def require(condition,message):
    global CHECKS
    CHECKS+=1
    if not condition: raise AssertionError(message)

def literal(value):
    if isinstance(value,str):value=value.encode('cp1252')
    return "_latin1 X'"+value.hex()+"'"

class Site:
    def __init__(self,source,base,work,label):
        self.label=label;self.root=work/label;self.database='u5reference_'+os.urandom(7).hex()
        self.server=None;self.log=None
        shutil.copytree(source,self.root,ignore=shutil.ignore_patterns('.git','config.php','__pycache__'))
        (self.root/'fileversions').mkdir(exist_ok=True)
        self.db(action='create',seed=str(base/'u5standard.sql'))
        self.config=(base/'config.php.sample').read_bytes()
        self.settings={
            'host':'127.0.0.1','username':os.environ.get('U5CMS_TEST_USER','root'),
            'password':os.environ.get('U5CMS_TEST_PASSWORD',''),'db':self.database,
            'mymail':'reference@example.invalid','usesessioninsteadofbasicauth':'no',
            'blockautoincluderefresherforhtaccessandindex':'yes',
            'cssremoteurlsifnecessary':'','doublepasswordmailing':'no',
            'requireturnstileonfrontendformsave':'no',
        }
        self.write_config()
        sha='{SHA}'+base64.b64encode(hashlib.sha1(b'reference-password').digest()).decode()
        self.sql("UPDATE accounts SET email='referenceadmin',pw="+literal(sha)+",accadmin=1 WHERE id=1")
        self.sql("INSERT INTO accounts(email,pw,accadmin) VALUES('referenceeditor',"+literal(sha)+",0)")
        self.sql("UPDATE languages SET lan4na='it',lan5na='es',lan4name='Italiano',lan5name='Espanol'")
        template=b'<!doctype html><html><head><meta charset="windows-1252"><title>Reference site</title></head><body id="body"><main id="reference-content">{{{content}}}</main></body></html>'
        self.sql("UPDATE resources SET content_1="+literal(template)+",content_2='',content_3='',content_4='',content_5='' WHERE name='htmltemplate'")
        (self.root/'r/htmltemplate.css').write_bytes(template)
        self.resource('refpage',[b'[h:]<div id="proof">REF-DE \xdcber \x80 &amp; [literal]</div>[:h]',b'[h:]<div id="proof">REF-EN</div>[:h]',b'',b'[h:]<div id="proof">REF-IT</div>[:h]',b''])
        self.resource('refphp',[b'[h:]<?php echo "EXECUTED-REFERENCE"; ?>[:h]'])
        self.resource('refarchive',[b'[h:]<?php echo "EXECUTED-ARCHIVE"; ?>[:h]'],deleted=2)
        self.resource('refprivate',[b'[h:]<div>PRIVATE-REFERENCE</div>[:h]'],logins='?reader:reader-password;\n?readertwo:reader-password;')
        self.fields={'name_mandatory':'Anna','comment':'He said "hello"; C:\\new\\path\r\nLine two \xdcber \u20ac &amp; [literal]',
                     'optional':'','zero':'0','qv2bo':'off','in2my':'off','em2cu':'off','em2me':'off','thanks':'referenceDone()'}
        form='[h:]<form name="u5form" id="reference-form" action="formsave.php?n=refform&l=de" method="post" target="saveframe">'
        for key,value in self.fields.items():
            form+='<textarea name="'+key+'"></textarea>'
        form+='<button id="reference-submit" type="submit">Save</button></form><iframe name="saveframe"></iframe><script>function referenceDone(){window.referenceSaved=true}</script>[:h]'
        self.resource('refform',[form.encode('cp1252')])
        owned=form.replace('n=refform','n=refownerform')+'\n[h:]<!-- SELF_TOEDITITEMS_LOGINS_SAME_AS_[refprivate] SELF_TODELETEITEMS_LOGINS_SAME_AS_[refprivate] -->[:h]'
        self.resource('refownerform',[owned.encode('cp1252')],logins='?reader:reader-password;\n?readertwo:reader-password;')
        app=b'''<?php require __DIR__.'/connect.inc.php';header('Content-Type: application/json');echo json_encode(array('query'=>$_GET,'cookies'=>$_COOKIE));'''
        (self.root/'reference-app.php').write_bytes(app)
        # The snapshot endpoint is not part of the CMS and never goes into releases.
        with socket.socket() as probe:probe.bind(('127.0.0.1',0));self.port=probe.getsockname()[1]
        self.log=(work/(label+'.server.log')).open('w')
        self.server=subprocess.Popen([PHP,'-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-d','sendmail_path=/bin/false','-S','127.0.0.1:'+str(self.port),'-t',str(self.root)],cwd=self.root,stdout=self.log,stderr=self.log)
        for attempt in range(100):
            try:
                with socket.create_connection(('127.0.0.1',self.port),timeout=.2):break
            except OSError:
                if self.server.poll() is not None:raise RuntimeError('PHP server did not start')
                time.sleep(.025)

    def write_config(self):
        # Preserve literal fixture credentials, including PHP interpolation chars.
        suffix='\n'+''.join('$'+key+'=base64_decode("'+base64.b64encode(value.encode()).decode()+'");\n' for key,value in self.settings.items())
        suffix+='\n$backup_send_mail=false;\n'
        (self.root/'config.php').write_bytes(self.config+suffix.encode('ascii'))

    def db(self,action='query',**args):
        payload=dict(args,action=action,database=self.database)
        result=subprocess.run([PHP,str(HERE/'main-reference-db.php')],input=json.dumps(payload),text=True,capture_output=True,check=True)
        return json.loads(result.stdout)

    def sql(self,query):
        if isinstance(query,str):query=query.encode('cp1252')
        result=self.db(sql=base64.b64encode(query).decode())
        result['rows']=[{k:None if v is None else base64.b64decode(v).decode('cp1252') for k,v in row.items()} for row in result['rows']]
        return result

    def resource(self,name,contents,**attrs):
        attrs=dict(name=name,typ='p',deleted=0,hidden=0,**attrs) if not attrs else dict({'name':name,'typ':'p','deleted':0,'hidden':0},**attrs)
        for lang in range(1,6):attrs['content_'+str(lang)]=contents[lang-1] if lang<=len(contents) else b''
        self.sql('INSERT INTO resources (`'+'`,`'.join(attrs)+'`) VALUES ('+','.join(literal(v) if isinstance(v,(str,bytes)) else str(v) for v in attrs.values())+')')

    def request(self,path,data=None,user=None,cookies=None):
        headers={}
        if user:
            headers['Authorization']='Basic '+base64.b64encode((user+':'+('reader-password' if user in ['reader','readertwo'] else 'reference-password')).encode()).decode()
        if cookies:headers['Cookie']=cookies
        if data is not None:
            if isinstance(data,dict):data=list(data.items())
            data=urllib.parse.urlencode([(k,v.encode('cp1252') if isinstance(v,str) else v) for k,v in data],doseq=True).encode()
        req=urllib.request.Request('http://127.0.0.1:'+str(self.port)+'/'+path,data=data,headers=headers)
        try:response=urllib.request.urlopen(req,timeout=30)
        except urllib.error.HTTPError as error:response=error
        body=response.read()
        return {'status':response.status,'body':body.decode('cp1252'),'headers':dict(response.headers)}

    def rows(self,name='refform'):
        return self.sql("SELECT id,formname,headcsv,datacsv,status,notes,authuser FROM formdata WHERE formname="+literal(name)+" ORDER BY id")['rows']

    def close(self):
        if self.server:self.server.terminate();self.server.wait(timeout=15)
        if self.log:self.log.close()
        self.db(action='drop')

def exercise(site):
    observations={}
    # Execute the real upgrade entry, not a target migration helper.
    response=site.request('u5admin/zr.php',user='referenceadmin')
    require(response['status']==200 and 'Fatal error' not in response['body'],site.label+': actual backend bootstrap '+response['body'][-1400:])
    observations['upgrade']={'status':response['status'],'runonce_removed':not (site.root/'r/runonce.php').exists()}
    for lang,expected in [('de','REF-DE'),('en','REF-EN'),('fr','REF-DE'),('it','REF-IT'),('es','REF-DE')]:
        response=site.request('index.php?c=refpage&l='+lang)
        require(response['status']==200 and expected in response['body'],site.label+': language '+lang+' '+response['body'][-500:])
        proof=re.search(r'<div id="proof">(.*?)</div>',response['body'],re.S)
        observations['language-'+lang]=proof.group(1) if proof else None
    for mode in ['onallpages','inarchiveonly','nowhere']:
        site.settings['executephp']=mode;site.write_config()
        for page in ['refphp','refarchive']:
            response=site.request('index.php?c='+page+'&l=de')
            marker='EXECUTED-REFERENCE' if page=='refphp' else 'EXECUTED-ARCHIVE'
            executed=marker in response['body'] and '<?php' not in response['body']
            want=mode=='onallpages' or (mode=='inarchiveonly' and page=='refarchive')
            require(response['status']==200 and executed==want,site.label+': PHP execution '+mode+'/'+page)
            observations['php-'+mode+'-'+page]=executed
    site.settings['executephp']='onallpages';site.write_config()
    response=site.request('index.php?c=refprivate&l=de')
    require(response['status']==401 and 'PRIVATE-REFERENCE' not in response['body'],site.label+': protected page denial')
    observations['private-denied']=response['status']
    response=site.request('index.php?c=refprivate&l=de',user='reader')
    require(response['status']==200 and 'PRIVATE-REFERENCE' in response['body'],site.label+': protected page allow')
    observations['private-allowed']=True
    # Direct applications may use CMS bootstrap without index's route consumers.
    for key in ['c','n','p','u5prvldd','f','s','t','q','selection']:
        path='reference-app.php?'+urllib.parse.urlencode([(key+'[]','alpha'),(key+'[]','0')])
        response=site.request(path)
        observations['application-array-'+key]={'status':response['status'],'body':response['body']}
    for key in ['u','p','hp','application']:
        response=site.request('reference-app.php',cookies=key+'[setting]=compact')
        observations['application-cookie-array-'+key]={'status':response['status'],'body':response['body']}
    response=site.request('index.php?c=refform&l=de')
    require(response['status']==200 and 'reference-form' in response['body'],site.label+': real form render')
    response=site.request('formsave.php?n=refform&l=de',dict(site.fields,name_mandatory=''))
    require(len(site.rows())==0 and 'referenceDone();' not in response['body'],site.label+': required field denial without write')
    response=site.request('formsave.php?n=refform&l=de',site.fields)
    rows=site.rows()
    require(response['status']==200 and len(rows)==1 and 'Fatal error' not in response['body'],site.label+': public form save '+response['body'][-1000:])
    row=rows[0]
    expected_data=''.join('\u00b7'+value.replace(';',',.')+';' for key,value in site.fields.items() if key!='thanks')
    require(row['datacsv']==expected_data,site.label+': exact legacy semicolon storage '+repr(row))
    observations['form-saved']=row
    bad=site.request('formsave.php?n=refform&l=de',dict(site.fields,foreignfield='must not save'))
    require(len(site.rows())==1 and 'Data cannot be saved' in bad['body'],site.label+': unknown form field denial')
    rowid=row['id']
    response=site.request('formdataeditgen.php?n=refform&id='+rowid+'&a=1',user='referenceadmin')
    require(response['status']==200 and 'generic formdata editor' in response['body'],site.label+': generic editor render')
    action=re.search(r'action="([^"]*editformsave.php[^"]*)"',response['body'])
    require(action is not None,site.label+': edit action present')
    response=site.request(action.group(1).replace('&amp;','&'),dict(site.fields,name_mandatory='Bea',comment='Edited \xdcber; "quote" \\end'),user='referenceadmin')
    rows=site.rows();current=next((item for item in rows if item['id']==rowid),None)
    require(response['status']==200 and current and 'Bea' in current['datacsv'],site.label+': generic form edit '+response['body'][-800:])
    observations['form-edited']=current
    # Export format is deliberately compared byte-for-byte except its timestamp.
    response=site.request('u5admin/formdata2csv.php?n=refform',user='referenceadmin')
    require(response['status']==200 and 'Bea' in response['body'] and 'text/x-csv' in response['headers'].get('Content-Type',''),site.label+': CSV export')
    observations['form-export']=re.sub(r'\d{4}\.\d{2}\.\d{2} \d{2}:\d{2}:\d{2}','<TIME>',response['body'])
    # Import is tab-delimited Excel text. Include both standard escaped quotes and
    # literal backslashes, and keep exact original observations before comparison.
    secret=os.environ.get('U5CMS_TEST_USER','root')+os.environ.get('U5CMS_TEST_PASSWORD','')+'referenceadmin'+'reference-password'
    token=hashlib.sha1((secret+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%d')).encode()).hexdigest()
    payload={'u5scrtytknfrfrms':token,'csv':'Name\tComment\tEmpty\tZero\r\nAlice\t"He said ""hi"" C:\\path"\t\t0\r\nBob\tplain\\end\t\t0\r\n',
             'target':'refimport','h':'2','m':'1','n':'1','fixnotes':'fixed notes','fixauthuser':'importowner'}
    response=site.request('u5admin/import2.php',payload,user='referenceadmin')
    rows=site.rows('refimport')
    require(response['status']==200 and len(rows)==2 and 'Fatal error' not in response['body'],site.label+': real CSV import '+response['body'][-800:])
    observations['form-import']=rows
    payload['target']='reflegacyimport'
    payload['csv']='Name\tComment\tEmpty\tZero\r\nAlice\t"He said \\"hi\\" C:\\path"\t\t0\r\n'
    response=site.request('u5admin/import2.php',payload,user='referenceadmin')
    rows=site.rows('reflegacyimport')
    require(response['status']==200 and len(rows)==1 and 'Fatal error' not in response['body'],site.label+': legacy escaped CSV import')
    observations['form-import-legacy']=rows
    response=site.request('u5admin/formdata2csv.php?n=refform',user='referenceeditor')
    require('Bea' not in response['body'],site.label+': HIADRI export denial')
    observations['export-low-rights-denied']=True
    # A denial remains a denial; later branches intentionally return HTTP 403.
    site.settings['formdataRqHIADRI']='no';site.write_config()
    response=site.request('u5admin/formdata2csv.php?n=refform',user='referenceeditor')
    require('Bea' in response['body'],site.label+': configured non-HIADRI export allowance')
    observations['export-low-rights-configured']=True
    response=site.request('formsave.php?n=refownerform&l=de',site.fields,user='reader')
    rows=site.rows('refownerform')
    require(response['status']==200 and len(rows)==1 and rows[0]['authuser']=='reader',site.label+': protected form records its actual owner')
    owned=rows[0];ownerid=owned['id']
    action='editformsave.php?n=refownerform&id='+ownerid+'&a=0&st=1&ti=1600000000&hu=2020.09.13'
    response=site.request(action,dict(site.fields,name_mandatory='Foreign'),user='readertwo')
    require('forbidden editing' in response['body'] and site.rows('refownerform')==rows,site.label+': same-page reader cannot edit another owner')
    response=site.request('delformsave.php?n=refownerform&id='+ownerid+'&a=0&st=1',site.fields,user='readertwo')
    require('forbidden deletion' in response['body'] and site.rows('refownerform')==rows,site.label+': same-page reader cannot delete another owner')
    response=site.request(action.replace('a=0','a=1'),site.fields,user='readertwo')
    require('Username and or password wrong' in response['body'] and site.rows('refownerform')==rows,site.label+': owner override denied without backend rights (legacy denial body)')
    response=site.request(action,dict(site.fields,name_mandatory='Owner edited'),user='reader')
    rows=site.rows('refownerform');current=next(item for item in rows if item['id']==ownerid)
    require(response['status']==200 and 'Owner edited' in current['datacsv'] and current['authuser']=='reader' and len(rows)==2,site.label+': owner edit preserves identity and history')
    observations['owner-edited']=rows
    response=site.request(action,dict(site.fields,name_mandatory='Stale',firstsaverwins='0'),user='reader')
    require('newer than the timestamp' in response['body'] and site.rows('refownerform')==rows,site.label+': stale form cannot overwrite current data')
    response=site.request('delformsave.php?n=refownerform&id='+ownerid+'&a=0&st=1&ti=1600000000&hu=2020.09.13',site.fields,user='reader')
    rows=site.rows('refownerform');current=next(item for item in rows if item['id']==ownerid)
    require(response['status']==200 and current['status']=='5' and current['authuser']=='reader' and len(rows)==3,site.label+': owner delete retains deleted record and histories '+repr(rows))
    observations['owner-deleted']=rows
    if os.environ.get('U5_REFERENCE_BROWSER')=='1':
        fields=dict(site.fields,name_mandatory='Browser Anna',comment='Browser \xdcber \u20ac \u4e2d\u6587 \U0001f600; "quote" \\tail')
        run=subprocess.run(['node',str(HERE/'main-reference-browser.cjs')],input=json.dumps({'base':'http://127.0.0.1:'+str(site.port),'fields':fields}),text=True,capture_output=True,check=True)
        browser=json.loads(run.stdout)
        require(browser['callback'] and not browser['errors'],site.label+': browser submit callback')
        row=site.rows()[-1]
        require('Browser Anna' in row['datacsv'] and '\u00dcber \u20ac' in row['datacsv'] and '&#20013,.' in row['datacsv'] and '&#128512,.' in row['datacsv'],site.label+': original browser encoding preserves Unicode through numeric entities '+repr(row))
        observations['browser-form']=dict(browser,row=row)
    return observations

def exercise_text_boundaries(site):
    for key in ['c','n','l','p','u5prvldd']:
        response=site.request('index.php?'+urllib.parse.urlencode([(key+'[]','bad')]))
        require(response['status']==400 and 'Fatal error' not in response['body'],site.label+': real route rejects '+key+'[]')
    response=site.request('formsave.php?n[]=refform',site.fields)
    require(response['status']==400,site.label+': real form route rejects n[]')
    response=site.request('f.php?f[]=bad')
    require(response['status']==400,site.label+': real file route rejects f[]')
    token=hashlib.sha1((os.environ.get('U5CMS_TEST_USER','root')+os.environ.get('U5CMS_TEST_PASSWORD','')+'referenceadminreference-password'+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%d')).encode()).hexdigest()
    payload={'u5scrtytknfrfrms':token,'csv':'Name\tComment\r\nAlice\t"trailing slash \\"\r\n','target':'refrfcimport','h':'2','m':'1','n':'1','fixnotes':'','fixauthuser':'','csv_escape':'rfc4180'}
    response=site.request('u5admin/import2.php',payload,user='referenceadmin')
    rows=site.rows('refrfcimport')
    require(response['status']==200 and len(rows)==1 and rows[0]['datacsv']=='\u00b7Alice;\u00b7trailing slash \\;',site.label+': explicit standard CSV retains trailing backslash '+repr(rows))
    for key,bad in [('csv_escape','unknown'),('csv_escape[]','legacy')]:
        payload.pop('csv_escape',None);payload.pop('csv_escape[]',None)
        payload[key]=bad
        before=site.rows('refrfcimport')
        response=site.request('u5admin/import2.php',payload,user='referenceadmin')
        require(response['status']==400 and site.rows('refrfcimport')==before,site.label+': invalid CSV mode does not write')
    response=site.request('u5admin/import.php',user='referenceadmin')
    require('name="csv_escape"' in response['body'] and 'value="legacy"' in response['body'] and 'value="rfc4180"' in response['body'],site.label+': both CSV choices are available in actual form')
    # The direct bootstrap is not an authentication consumer, even in session mode.
    site.settings['usesessioninsteadofbasicauth']='yes';site.write_config()
    for key in ['u','p']:
        cookie=key+'[owned]=value'
        response=site.request('reference-app.php',cookies=cookie)
        require(response['status']==200 and json.loads(response['body'])['cookies'][key]=={'owned':'value'},site.label+': session-mode independent app cookie '+key)
        for path in ['index.php?c=refprivate','u5admin/index.php']:
            response=site.request(path,cookies=cookie)
            require(response['status']==400 and 'Fatal error' not in response['body'],site.label+': actual authentication rejects '+key+'[]')
    site.settings['usesessioninsteadofbasicauth']='no';site.write_config()

def differences(expected,actual):
    return {key:{'original':expected.get(key),'target':actual.get(key)} for key in sorted(set(expected)|set(actual)) if expected.get(key)!=actual.get(key)}

def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--baseline',type=pathlib.Path,required=True)
    parser.add_argument('--target',action='append',default=[],help='label=source-directory')
    parser.add_argument('--text-boundaries',action='store_true',help='also assert contextual text guards on targets that provide them')
    parser.add_argument('--oracle',type=pathlib.Path,help='explicit prior successful original observations for a capability-gain run; does not claim a new original execution')
    parser.add_argument('--output',type=pathlib.Path,required=True)
    args=parser.parse_args();report={'method':'Original installation executes first; observations frozen before targets.','baseline':str(args.baseline),'results':[]}
    args.output.parent.mkdir(parents=True,exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='u5-reference-') as temporary:
        work=pathlib.Path(temporary)
        if args.oracle:
            expected=json.loads(args.oracle.read_text())['original']
            report['method']='Capability-gain run against explicit prior successful original observations; original is not newly executed in this profile.'
            report['oracle']=str(args.oracle)
        else:
            baseline=Site(args.baseline,args.baseline,work,'original-main')
            try:expected=exercise(baseline)
            except Exception as error:
                baseline.log.flush()
                report['original_failure']={'error':str(error),'server_log':(work/'original-main.server.log').read_text(errors='replace')[-6000:],'checks_before_failure':CHECKS}
                args.output.write_text(json.dumps(report,indent=2,ensure_ascii=True))
                raise
            finally:baseline.close()
        report['original']=expected;report['original_checks']=CHECKS
        args.output.write_text(json.dumps(report,indent=2,ensure_ascii=True))
        print(('Prior original oracle loaded; no new original checks.' if args.oracle else 'Original main expectations frozen: '+str(CHECKS)+' checks.'),flush=True)
        for value in args.target:
            label,path=value.split('=',1);site=Site(pathlib.Path(path),args.baseline,work,label)
            try:
                actual=exercise(site);diff=differences(expected,actual)
                if args.text_boundaries:exercise_text_boundaries(site)
                record={'label':label,'differences':diff,'observations':actual}
            except Exception as error:
                record={'label':label,'error':str(error)}
            finally:site.close()
            report['results'].append(record);report['checks']=CHECKS
            args.output.write_text(json.dumps(report,indent=2,ensure_ascii=True))
            print(json.dumps({'label':label,'differences':record.get('differences'),'error':record.get('error')}),flush=True)
    print('Reference comparison completed: '+str(CHECKS)+' checks.',flush=True)
    return 1 if any(item.get('differences') or item.get('error') for item in report['results']) else 0

if __name__=='__main__':raise SystemExit(main())
