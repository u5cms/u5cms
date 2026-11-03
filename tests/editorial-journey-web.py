#!/usr/bin/env python3
"""Additional pass 3: complete browser journeys in fresh branch-specific sites."""
import argparse,json,os,pathlib,shutil,socket,subprocess,tempfile,time,urllib.request
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
HERE=pathlib.Path(__file__).resolve().parent
PHP=os.environ.get('U5_TEST_PHP','php')
def main():
    parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('--source',type=pathlib.Path,required=True);args=parser.parse_args();source=args.source.resolve()
    with tempfile.TemporaryDirectory(prefix='u5-editorial-journey-') as temp:
        work=pathlib.Path(temp);site=work/'site';shutil.copytree(source,site,ignore=shutil.ignore_patterns('.git','__pycache__','config.php'))
        setup=subprocess.run([PHP,str(source/'tests/codeassets-web-fixture.php'),str(site)],capture_output=True,text=True,check=True)
        database=json.loads(setup.stdout)['database']
        with socket.socket() as probe:probe.bind(('127.0.0.1',0));port=probe.getsockname()[1]
        base='http://127.0.0.1:'+str(port)
        with (work/'server.log').open('w+') as log:
            server=subprocess.Popen([PHP,'-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-S','127.0.0.1:'+str(port),'-t',str(site)],cwd=site,stdout=log,stderr=log)
            try:
                for attempt in range(100):
                    try:
                        with socket.create_connection(('127.0.0.1',port),timeout=.2):break
                    except OSError:
                        if server.poll() is not None:raise RuntimeError('PHP server stopped')
                        time.sleep(.03)
                else:raise RuntimeError('PHP server readiness timed out')
                subprocess.run(['node',str(HERE/'editorial-journey-browser.cjs'),base,str(site)],check=True)
            except Exception:
                log.flush();print((work/'server.log').read_text(errors='replace')[-3500:]);raise
            finally:
                server.terminate();server.wait(timeout=10)
                subprocess.run([PHP,str(source/'tests/codeassets-web-fixture.php'),'cleanup',database],check=True)
if __name__=='__main__':main()
