#!/usr/bin/env python3
"""Prove the HTTPS oracle rejects removed detectors and the prior bool-only fix."""
import json,os,pathlib,re,shutil,subprocess,tempfile
ROOT=pathlib.Path(__file__).resolve().parent.parent
PHP=os.environ.get('U5_TEST_PHP','php')
routes=json.loads(os.environ.get('U5CMS_HTTPS_ROUTES','["connect.inc.php","u5admin/connect.inc.php"]'))
legacy=json.loads(os.environ.get('U5CMS_HTTPS_LEGACY_ROUTES','[]'))
routes+=legacy
assert routes and all((ROOT/r).is_file() for r in routes)
checks=0
with tempfile.TemporaryDirectory(prefix='u5-oracle-') as tmp:
    root=pathlib.Path(tmp);(root/'tests').mkdir()
    shutil.copyfile(ROOT/'tests/https-detection-regression.php',root/'tests/https-detection-regression.php')
    for route in routes:
        target=root/route;target.parent.mkdir(exist_ok=True);target.write_bytes((ROOT/route).read_bytes())
    env=dict(os.environ,U5CMS_HTTPS_ROUTES=json.dumps([r for r in routes if r not in legacy]),U5CMS_HTTPS_LEGACY_ROUTES=json.dumps(legacy))
    def run():return subprocess.run([PHP,str(root/'tests/https-detection-regression.php')],env=env,capture_output=True,text=True)
    assert run().returncode==0,'Unmodified detector contracts must pass';checks+=1
    for route in routes:
        path=root/route;original=path.read_bytes()
        path.write_bytes(b'<?php /* detector removed by mutation */')
        result=run();assert result.returncode!=0 and 'HTTPS detector not found' in result.stdout+result.stderr,route+' silently skipped a removed detector';checks+=1
        path.write_bytes(original)
        if route in legacy:continue
        mutated=re.sub(rb'(if\s*\()\$(u5HttpsResult|result)\s*!==\s*null(\))',rb'\1is_bool($\2)\3',original)
        assert mutated!=original,'Scalar-result mutation not applied: '+route
        path.write_bytes(mutated)
        result=run();assert result.returncode!=0 and 'HTTPS configuration contract failed' in result.stdout+result.stderr,route+' failed to detect the former bool-only regression';checks+=1
        path.write_bytes(original)
print('HTTPS test-oracle mutations:',checks,'passed.')
