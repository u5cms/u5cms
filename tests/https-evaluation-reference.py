#!/usr/bin/env python3
"""Evaluate the exact HTTPS configuration blocks, with a frozen legacy oracle.

No network, credentials or database. The configuration itself is trusted PHP;
the expected truth table preserves scalar return values accepted by main.
"""
import argparse, base64, json, os, pathlib, re, subprocess

PROFILES=[
    ('return-bool-true','return true;',True),
    ('return-bool-false','return false;',False),
    ('return-int-one','return 1;',True),
    ('return-int-zero','return 0;',False),
    ('return-string-one','return "1";',True),
    ('return-string-zero','return "0";',False),
    ('return-https-on','return "on";',True),
    ('return-empty','return "";',False),
    ('assignment-true','$httpsisinuse=true;',True),
    ('assignment-false','$httpsisinuse=false;',False),
    ('assignment-one','$httpsisinuse=1;',True),
    ('assignment-on','$httpsisinuse="on";',True),
    ('return-takes-precedence','$httpsisinuse=true;return false;',False),
    ('assignment-with-null-return','$httpsisinuse=true;return null;',True),
]

def block(path):
    source=path.read_bytes().decode('cp1252')
    if path.as_posix().endswith('oidc/bootstrap.inc.php'):
        pattern=r'\$httpsisinuse\s*=\s*false;.*?(?=\s*if\s*\(!\$httpsisinuse\))'
    else:
        pattern=r'\$httpsisinuse\s*=\s*false;.*?\$httpsisinuse\s*=\s*\(bool\)\s*\$httpsisinuse;'
    match=re.search(pattern,source,re.S)
    return match.group(0) if match else None

def main():
    p=argparse.ArgumentParser();p.add_argument('--sources',type=pathlib.Path,required=True)
    p.add_argument('--output',type=pathlib.Path,required=True);a=p.parse_args()
    sources=json.loads(a.sources.read_text());records=[];checks=0
    original=pathlib.Path(sources[0]['source'],'connect.inc.php').read_bytes().decode('cp1252')
    oracle=re.search(r'\$httpsisinuse\s*=\s*eval\(\$evaluateifhttpsisinuse\);',original)
    assert oracle,'Original frontend HTTPS evaluation not found'
    php=os.environ.get('U5_TEST_PHP','php')
    for h in sources:
        for route in ['connect.inc.php','u5admin/connect.inc.php','saml/login.php','oidc/bootstrap.inc.php']:
            path=pathlib.Path(h['source'],route)
            if not path.is_file():continue
            code=block(path)
            if code is None:continue
            failures=[]
            for label,config,expected in PROFILES:
                setting='base64_decode("'+base64.b64encode(config.encode()).decode()+'")'
                program='$evaluateifhttpsisinuse='+setting+';'+code+';echo json_encode((bool)$httpsisinuse);'
                execution=subprocess.run([php,'-r',program],capture_output=True,text=True,check=True)
                actual=json.loads(execution.stdout);checks+=1
                if actual is not expected:failures.append({'profile':label,'expected':expected,'actual':actual})
            records.append({'branch':h['branch'],'route':route,'checks':len(PROFILES),'failures':failures})
    # Returned scalar profiles are executed on the original as the factual oracle.
    legacy=[]
    for label,config,expected in PROFILES:
        if not label.startswith('return-') or label=='return-takes-precedence':continue
        setting='base64_decode("'+base64.b64encode(config.encode()).decode()+'")'
        result=subprocess.run([php,'-r','$evaluateifhttpsisinuse='+setting+';'+oracle.group(0)+'echo json_encode((bool)$httpsisinuse);'],capture_output=True,text=True,check=True)
        actual=json.loads(result.stdout);assert actual is expected
        legacy.append({'profile':label,'actual':actual});checks+=1
    report={'checks':checks,'original_return_oracle':legacy,'results':records}
    a.output.write_text(json.dumps(report,indent=2)+'\n')
    failures=[r for r in records if r['failures']]
    print(json.dumps({'checks':checks,'affected_routes':len(failures),'failures':sum(len(r['failures']) for r in failures)}))
    return int(bool(failures))

if __name__=='__main__':raise SystemExit(main())
