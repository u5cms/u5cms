#!/usr/bin/env python3
"""Compare actual form-handler results across releases using private SQL tables.

No production connection, stored credentials, external mail or site data is used.
Rendering/authentication are fixture boundaries; the form handlers, version check
and SQL compatibility layer are the actual selected source files.
"""
import base64
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('U5_TEST_PHP', 'php')
SCENARIOS = ['ordinary','explicit-off','mail','exclude-columns','callback','repeat','two-steps','replace','conflict','foreign','monofill','shared','admin-edit','admin-delete','blank','invalid-email','unexpected-field','empty-post']
FILES = ['formsave.php','editformsave.php','delformsave.php','formdata-write-lock.inc.php','formdata-version.inc.php','saveversionconflict.inc.php','mysql.php','onlymine.inc.php','form-evidence.inc.php','request.inc.php']
refs = subprocess.check_output(['git','tag','--list','v12.9.5*','--sort=version:refname'],cwd=ROOT,text=True).splitlines()
refs += ['v12.10.0','v12.10.1','v12.10.2','v12.10.3','v12.10.4','v12.10.5']
refs += [ref for ref in ['origin/V13-more-Img-Formats','origin/V14-JS-CSS-HTML-Exports','origin/V15-Phrase-Search-and-OIDC','origin/V16-Default-Design','origin/V17-New-Responsive-Layouts'] if subprocess.run(['git','rev-parse','--verify',ref],cwd=ROOT,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL).returncode==0]
if os.environ.get('U5_EVOLUTION_REFS'):
    refs=json.loads(os.environ['U5_EVOLUTION_REFS'])

def run_line(ref, folder):
    folder.mkdir()
    (folder/'fileversions').mkdir()
    for name in FILES:
        if ref == 'working-tree':
            if (ROOT/name).exists():shutil.copyfile(ROOT/name,folder/name)
        else:
            source=subprocess.run(['git','show',ref+':'+name],cwd=ROOT,capture_output=True)
            if source.returncode==0:(folder/name).write_bytes(source.stdout)
    for name in ['connect.inc.php','render.inc.php','loginformsave.inc.php','keyfocus.inc.php','ob.php','config.php','editreadrights.inc.php','delreadrights.inc.php']:
        (folder/name).write_text('<?php\n')
    (folder/'zendmail.php').write_text("<?php $capture[]=array_map('base64_encode',array('to'=>$zendto,'from'=>$zendfrom,'subject'=>$zendsubject,'body'=>$zendmessage));\n")
    shutil.copyfile(ROOT/'tests/form-evolution-worker.php',folder/'worker.php')
    output={}
    for scenario in SCENARIOS:
        result=folder/'result.json'
        result.unlink(missing_ok=True)
        process=subprocess.run([PHP,'worker.php',scenario],cwd=folder,capture_output=True)
        assert process.returncode==0 and result.exists(),(ref,scenario,process.stdout[-700:],process.stderr[-700:])
        output[scenario]=json.loads(result.read_text())
    return output

def decoded(row,key):
    return base64.b64decode(row[key]).decode('latin1') if row[key] is not None else None

with tempfile.TemporaryDirectory(prefix='u5-form-evolution-') as temporary:
    directory=Path(temporary)
    reference=run_line('v12.9.5',directory/'baseline')
    # Explicit business expectations prevent a bad baseline becoming the oracle.
    assert len(reference['ordinary']['rows'])==1 and reference['ordinary']['completed']
    assert len(reference['repeat']['rows'])==2
    assert {decoded(r,'formname') for r in reference['two-steps']['rows']}=={'complexform','secondstep'}
    assert len(reference['mail']['mail'])==3
    assert not reference['blank']['rows'] and not reference['invalid-email']['rows']
    assert not reference['unexpected-field']['rows'] and reference['unexpected-field']['field_error']
    assert not reference['empty-post']['rows']
    assert len(reference['conflict']['rows'])==1 and reference['conflict']['conflict']
    assert len(reference['foreign']['rows'])==2
    assert sorted(decoded(r,'status') for r in reference['replace']['rows'])==['2','6']
    checks=11
    for index,ref in enumerate(refs+['working-tree']):
        actual=run_line(ref,directory/str(index))
        for scenario in SCENARIOS:
            assert actual[scenario]==reference[scenario],(ref,scenario,actual[scenario],reference[scenario])
            checks+=1
        print(ref+': '+str(len(SCENARIOS))+' form scenarios match 12.9.5',flush=True)
    print(json.dumps({'status':'PASS','checks':checks,'lines':len(refs)+1,'scenarios_per_line':len(SCENARIOS),'boundary':'actual handlers and SQL; fixture rendering/authentication; captured mail only'}))
