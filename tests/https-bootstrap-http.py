#!/usr/bin/env python3
"""Real HTTP bootstrap checks for legacy HTTPS return/assignment conventions.

Uses fresh CMS databases and local HTTP only. A configured trusted detector
simulates TLS termination; redirects are inspected without following them.
"""
import argparse, base64, importlib.util, json, pathlib, tempfile, urllib.error, urllib.request

HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('reference',HERE/'main-reference-regression.py')
reference=importlib.util.module_from_spec(spec);spec.loader.exec_module(reference)
PROFILES=[('bool-true','return true;',True),('bool-false','return false;',False),
    ('int-one','return 1;',True),('int-zero','return 0;',False),
    ('string-one','return "1";',True),('string-zero','return "0";',False),
    ('on','return "on";',True),('empty','return "";',False),
    ('assign-true','$httpsisinuse=true;',True),('assign-false','$httpsisinuse=false;',False)]

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs):return None

def request(site,route):
    req=urllib.request.Request('http://127.0.0.1:'+str(site.port)+'/'+route,
        headers={'Authorization':'Basic '+base64.b64encode(b'referenceadmin:reference-password').decode()})
    try:response=urllib.request.build_opener(NoRedirect()).open(req,timeout=15)
    except urllib.error.HTTPError as error:response=error
    return {'status':response.status,'location':response.headers.get('Location'),'body':response.read().decode('cp1252')}

def main():
    parser=argparse.ArgumentParser();parser.add_argument('--sources',type=pathlib.Path,required=True)
    parser.add_argument('--output',type=pathlib.Path,required=True);args=parser.parse_args()
    sources=json.loads(args.sources.read_text());base=pathlib.Path(sources[0]['source']);results=[]
    with tempfile.TemporaryDirectory(prefix='u5-https-bootstrap-') as tmp:
        work=pathlib.Path(tmp)
        for h in sources:
            # Main's legacy backend ignored return values entirely. Its frontend
            # supplies the compatibility oracle; the newer branch supports both.
            site=reference.Site(pathlib.Path(h['source']),base,work,h['branch'].replace('/','__'))
            issues=[];checks=0
            try:
                site.request('u5admin/zr.php',user='referenceadmin')
                (site.root/'https-probe.php').write_text("<?php require __DIR__.'/connect.inc.php';echo 'HTTPS-PROOF:'.($httpsisinuse ? 'yes' : 'no');")
                (site.root/'u5admin/https-probe.php').write_text("<?php require __DIR__.'/connect.inc.php';echo 'HTTPS-PROOF:'.($httpsisinuse ? 'yes' : 'no');")
                legacy=b'$u5HttpsResult' not in (site.root/'connect.inc.php').read_bytes()
                for label,config,secure in PROFILES:
                    if legacy and label.startswith('assign-'):continue
                    site.settings.update(evaluateifhttpsisinuse=config,forcehttpsonfrontend='yes',forcehttpsonbackend='yes');site.write_config()
                    for route in ['https-probe.php']+([] if legacy else ['u5admin/https-probe.php']):
                        response=request(site,route);checks+=1
                        expected=200 if secure else 302
                        if response['status']!=expected or (secure and not legacy and 'HTTPS-PROOF:yes' not in response['body']) or (not secure and not (response['location'] or '').startswith('https://')):
                            issues.append({'profile':label,'route':route,'expected':expected,'response':response})
            finally:site.close()
            results.append({'branch':h['branch'],'sha':h['sha'],'checks':checks,'issues':issues})
            args.output.write_text(json.dumps({'results':results},indent=2)+'\n')
            print(json.dumps({'branch':h['branch'],'checks':checks,'issues':len(issues)}),flush=True)
    return int(any(r['issues'] for r in results))

if __name__=='__main__':raise SystemExit(main())
