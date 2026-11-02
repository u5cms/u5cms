#!/usr/bin/env python3
"""Independent original-first read-path and completion matrix on legacy fixtures.

Compares status, actual completion and route-specific semantics, not merely 200.
No send, delete, save or cleanup endpoint is followed. SQL observations confirm
that the view traversal does not change form data or resource content.
"""
import argparse, importlib.util, json, pathlib, re, tempfile

HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('reference',HERE/'main-reference-regression.py')
reference=importlib.util.module_from_spec(spec);spec.loader.exec_module(reference)

BACKEND=[
    'index.php','zr.php','p1.php','p2.php','i.php','d.php','v1.php','e.php','s1.php','s2.php','c.php',
    'new.php?typ=p','copy.php?name=refpage','rename.php?name=refpage',
    'editor.php?c=refpage','meta.php?name=refpage','localize.php?name=refpage',
    'textversions.php?c=refpage','fileversions.php','trxlist.php','intranetmembers.php',
    'definelanguages.php','defineloginglobals.php','definesizes.php','acc.php',
    'formdata.php?n=refform','formdata2.php?n=refform','formdatamail.php?n=refform',
    'formdata2csv.php?n=refform','formdata2.php?n=emptyform','formdatamail.php?n=emptyform',
    'formdata2csv.php?n=emptyform','formdataintro.php?n=refform',
    'formdata2.php?n=refform&f=Anna','formdatamail.php?n=refform&f=Anna',
    'formdata2csv.php?n=refform&f=Anna','formdata2.php?n=refform&f=absent',
    'countblockelements.php?c=refpage&l1=de&l2=en&l3=fr&l4=it&l5=es',
    'countblockelements.php?c=missing&l1=de&l2=en&l3=fr&l4=it&l5=es',
]
FRONTEND=[
    'index.php?c=refpage&l=de','index.php?c=refpage&l=en',
    'index.php?c=refpage&l=fr','index.php?c=refpage&l=it','index.php?c=refpage&l=es',
    'index.php?c=emptyform&l=de','index.php?c=missing','index.php?c=refarchive',
    'formdataeditgen.php?n=refform','formdataeditgen.php?n=emptyform',
    'chart.php?n=refform','chart.php?n=emptyform','chart.php?n=refform&f=Anna',
    'chart3.php?n=refform','chart3.php?n=emptyform',
    'max.php?n=refform','max.php?n=emptyform','maxcalc.php?n=refform','maxcalc.php?n=emptyform',
    'mean.php?n=refform','mean.php?n=emptyform',
    'df.php?f=not-a-file','f.php?f=missing','ff.php?f=missing',
    'fff.php?f=missing','ffff.php?f=missing',
]

def exercise(site):
    upgrade=site.request('u5admin/zr.php',user='referenceadmin')
    if upgrade['status']!=200: raise AssertionError('Upgrade did not finish')
    site.resource('emptyform',[b'[h:]<form><input name="empty"></form>[:h]'])
    site.request('formsave.php?n=refform&l=de',site.fields)
    resource_sql="SELECT name,typ,deleted,content_1,content_2,content_3,content_4,content_5 FROM resources ORDER BY name,deleted"
    before={'forms':site.rows(),'resources':site.sql(resource_sql)['rows']}
    result={}
    for role in ['referenceadmin','referenceeditor']:
        for prefix,paths in [('u5admin/',BACKEND),('',FRONTEND)]:
            for path in paths:
                endpoint=prefix+path
                if not (site.root/endpoint.split('?')[0]).is_file():continue
                response=site.request(endpoint,user=role)
                body=response['body']
                result[role+'/'+endpoint]={
                    'status':response['status'],
                    'html':bool(re.search(r'<html\b',body,re.I)),
                    'complete':bool(re.search(r'</html\s*>',body,re.I)),
                    'server_error':bool(re.search(r'Fatal error|Uncaught (?:TypeError|Error|Exception)|SQL_[a-z_]+-Query failed',body)),
                    'anna':'Anna' in body,
                    'generic_editor':'generic formdata editor' in body,
                    'bytes':len(body),
                }
    after={'forms':site.rows(),'resources':site.sql(resource_sql)['rows']}
    if before!=after:raise AssertionError('Read traversal changed saved data')
    return result

def main():
    parser=argparse.ArgumentParser();parser.add_argument('--sources',type=pathlib.Path,required=True)
    parser.add_argument('--output',type=pathlib.Path,required=True);args=parser.parse_args()
    sources=json.loads(args.sources.read_text());base=pathlib.Path(sources[0]['source'])
    report={'method':'Fresh original first; all current branches; real read routes, full-page completion, data preservation.','results':[]}
    with tempfile.TemporaryDirectory(prefix='u5-evening-views-') as tmp:
        work=pathlib.Path(tmp)
        for h in sources:
            label=h['branch'].replace('/','__');site=None
            try:
                site=reference.Site(pathlib.Path(h['source']),base,work,label)
                observations=exercise(site)
                if h==sources[0]:
                    expected=observations;report['original']=expected
                    differences=[]
                else:
                    differences=[]
                    for key,want in expected.items():
                        if key not in observations:continue
                        actual=observations[key]
                        # Preserve success, original completion and meaningful data markers.
                        # Intentional 200->403 denial tightening remains a denial.
                        if want['status']==200 and (actual['status']>=500 or (want['complete'] and not actual['complete']) or (want['anna'] and not actual['anna']) or (want['generic_editor'] and not actual['generic_editor'])):
                            differences.append({'route':key,'original':want,'actual':actual})
                record={'branch':h['branch'],'sha':h['sha'],'routes':len(observations),'differences':differences,'observations':observations}
            except Exception as error:record={'branch':h['branch'],'error':str(error)}
            finally:
                if site:site.close()
            report['results'].append(record);args.output.write_text(json.dumps(report,indent=2)+'\n')
            print(json.dumps({k:v for k,v in record.items() if k not in ['observations','differences']}|{'differences':len(record.get('differences',[]))}),flush=True)
    return int(any(r.get('error') or r.get('differences') for r in report['results']))

if __name__=='__main__':raise SystemExit(main())
