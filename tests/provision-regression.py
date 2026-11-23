"""Run real PHP/MariaDB lifecycle, process death and HTTP media checks on a disposable site."""
from pathlib import Path
import os, subprocess, shutil, tempfile, json, signal
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
root=Path(__file__).resolve().parent.parent
php=os.environ.get('U5_TEST_PHP','php')
with tempfile.TemporaryDirectory(prefix='u5-provision-') as tmp:
    site=Path(tmp)/'site'
    shutil.copytree(root,site,ignore=shutil.ignore_patterns('.git','qa'))
    setup=[php,str(root/'tests/rename-web-fixture.php')]
    db=json.loads(subprocess.check_output(setup+[str(site)]))['database']
    run=[php,str(root/'tests/provision-regression.php'),str(site)]
    try:
        subprocess.run(run,check=True)
        writer=subprocess.Popen(run+['abandon'],stdout=subprocess.PIPE,text=True)
        try:
            if writer.stdout.readline().strip()!='READY': raise AssertionError('writer did not reach checkpoint')
            writer.kill()
            writer.wait(timeout=5)
            assert (site/'r/photo/.u5-provision.php').exists()
            subprocess.run(run+['retry'],check=True)
            assert not (site/'r/photo/.u5-provision.php').exists()
            assert 'restored:' in (site/'r/photo/.htpasswd').read_text()
            print('Interrupted writer recovered with current resource permissions.')
        finally:
            if writer.poll() is None: writer.kill();writer.wait()
    finally:
        subprocess.run(setup+['cleanup',db],check=True)
