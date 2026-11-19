#!/usr/bin/env python3
"""Fail-closed regression entry point; capabilities are committed per branch.

Requires a disposable TCP MariaDB and installed PHP/Playwright dependencies.
No deployment credentials, production database or mail transport are used.
"""
import argparse,json,os,pathlib,subprocess,sys,tarfile,tempfile,time
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
ROOT=pathlib.Path(__file__).resolve().parent.parent
PHP=os.environ.get('U5_TEST_PHP','php')

def main():
    parser=argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--group',choices=['contracts','features','all'],default='all')
    parser.add_argument('--baseline',type=pathlib.Path)
    parser.add_argument('--output',type=pathlib.Path,default=ROOT/'ci-results')
    args=parser.parse_args();out=args.output.resolve();out.mkdir(exist_ok=True,parents=True)
    if not os.environ.get('U5CMS_TEST_PORT'):raise RuntimeError('A disposable U5CMS_TEST_PORT is required; refusing an implicit database.')
    manifest=json.loads((ROOT/'tests/regression-capabilities.json').read_text())
    for name in manifest['required_files']:
        if not (ROOT/name).is_file():raise RuntimeError('Required regression capability is missing: '+name)
    env=os.environ.copy();env['U5CMS_HTTPS_ROUTES']=json.dumps(manifest['https_routes'])
    env['U5CMS_HTTPS_LEGACY_ROUTES']=json.dumps(manifest['legacy_https_routes'])
    env['U5CMS_TEST_PHP']=PHP
    env['U5_REFERENCE_BROWSER']='1'
    env['U5CMS_PHP9_AUDIT']='1' if manifest['php9'] else ''
    env['U5CMS_MAIN_AUDIT']='1' if manifest['main_browser'] else ''
    env['U5CMS_RENAME_AUDIT']='1' if manifest['rename_browser'] else ''
    env['U5CMS_TEXTAREA_AUDIT']='1' if manifest['textarea_browser'] else ''
    env['U5CMS_BACKUP_STRESS']='1' if manifest['main_browser'] else ''
    summary={'branch_capabilities':manifest,'groups':args.group,'commands':[]}
    def run(command):
        command=[str(v) for v in command];print('+ '+' '.join(command),flush=True)
        start=time.monotonic();result=subprocess.run(command,cwd=ROOT,env=env)
        summary['commands'].append({'command':command,'exit_code':result.returncode,'seconds':round(time.monotonic()-start,2)})
        (out/'summary.json').write_text(json.dumps(summary,indent=2))
        if result.returncode:raise RuntimeError('Regression failed: '+' '.join(command))
    if args.group in ['contracts','all']:
        if manifest['https_routes'] or manifest['legacy_https_routes']:
            run([PHP,'tests/https-detection-regression.php'])
            run([sys.executable,'tests/regression-oracle-regression.py'])
        with tempfile.TemporaryDirectory(prefix='u5-original-') as temporary:
            baseline=args.baseline.resolve() if args.baseline else pathlib.Path(temporary)/'original'
            if not args.baseline:
                baseline.mkdir();archive=pathlib.Path(temporary)/'original.tar'
                subprocess.run(['git','archive',manifest['original_sha'],'--output='+str(archive)],cwd=ROOT,check=True)
                with tarfile.open(archive) as contents:contents.extractall(baseline,filter='data')
            for test in ['main-reference-regression.py','configuration-reference.py','formdata-view-reference.py','conference-seed-reference.py']:
                command=[sys.executable,'tests/'+test,'--baseline',baseline,'--target','branch='+str(ROOT),'--output',out/(test.removesuffix('.py')+'.json')]
                if test=='main-reference-regression.py' and manifest['php9']:command.append('--text-boundaries')
                run(command)
        run([sys.executable,'tests/conference-workflow-regression.py','--source',ROOT,'--output',out/'conference.json'])
    if args.group in ['features','all']:
        if manifest.get('responsive'):
            with tempfile.TemporaryDirectory(prefix='u5-render-original-') as temporary:
                baseline=pathlib.Path(temporary)/'original';baseline.mkdir()
                archive=pathlib.Path(temporary)/'original.tar'
                subprocess.run(['git','archive',manifest['responsive']['baseline_sha'],'--output='+str(archive)],cwd=ROOT,check=True)
                with tarfile.open(archive) as contents:contents.extractall(baseline,filter='data')
                run([sys.executable,'tests/responsive-render-regression.py','--baseline',baseline,'--output',out/'responsive-render.json'])
                run([sys.executable,'tests/responsive-backend-regression.py','--baseline',baseline,'--output',out/'responsive-backend.json'])
            run([sys.executable,'tests/responsive-web.py'])
        for name in manifest['php_tests']:run([PHP,'tests/'+name])
        for name in manifest['node_tests']:run(['node','tests/'+name])
        for name in manifest['http_browser_tests']:
            if name=='editorial-journey-web.py':run([sys.executable,'tests/'+name,'--source',ROOT]);continue
            if name not in ['mail-evolution-regression.py','mutation-recovery-http.py']:run([sys.executable,'tests/'+name]);continue
            with tempfile.TemporaryDirectory(prefix='u5-mail-original-') as temporary:
                baseline=args.baseline.resolve() if args.baseline else pathlib.Path(temporary)/'original'
                if not args.baseline:
                    baseline.mkdir();archive=pathlib.Path(temporary)/'original.tar'
                    subprocess.run(['git','archive',manifest['original_sha'],'--output='+str(archive)],cwd=ROOT,check=True)
                    with tarfile.open(archive) as contents:contents.extractall(baseline,filter='data')
                if name=='mail-evolution-regression.py':run([sys.executable,'tests/'+name,baseline])
                else:run([sys.executable,'tests/'+name,'--baseline',baseline,'--target','branch='+str(ROOT),'--output',out/'mutation-recovery.json'])
    print('All declared regression commands passed.',flush=True)

if __name__=='__main__':main()
