"""Kill an actual worker and verify the kernel lock and retry checkpoint."""
from pathlib import Path
import fcntl, json, os, shutil, subprocess, tempfile, time

root = Path(__file__).resolve().parent.parent
php = [os.environ.get('U5_TEST_PHP', 'php')] + json.loads(os.environ.get('U5_TEST_PHP_ARGS', '[]'))
with tempfile.TemporaryDirectory(prefix='u5-index-crash-') as folder:
    temp = Path(folder)
    state = temp/'fileversions'
    state.mkdir()
    for path in ['indexer.php', 'background-work.inc.php', 'search-index.inc.php', 'search-core.inc.php']:
        if (root/path).is_file():
            shutil.copy(root/path, temp/path)
    for name, value in [('lastsave', 200), ('lastindex', 100), ('indexerrunning', 0)]:
        (state/(name+'.txt')).write_text(str(value))
    worker = temp/'worker.php'
    worker.write_text('''<?php
chdir(__DIR__);$_GET=array('u5indexcontinue'=>'1');require 'indexer.php';
$slice=new U5IndexSlice(__DIR__.'/fileversions',false,false,false);
if (!$slice->ready()) {echo 'busy';return;}
if (!$slice->beginResource(array('resource_key'=>'4142','deleted'=>0))) exit(3);
echo "held\\n";flush();sleep(20);
''')
    process = subprocess.Popen(php + [str(worker)], stdout=subprocess.PIPE)
    try:
        assert process.stdout.readline() == b'held\n'
        contender = subprocess.check_output(php + [str(worker)])
        assert contender == b'busy'
        process.kill()
        process.wait(timeout=5)
        assert (state/'lastindex.txt').read_text() == '100'
        checkpoint = json.loads((state/'indexer-progress.php').read_text().split('\n', 1)[1])
        assert checkpoint['next'] > time.time() + 890
        with (state/'indexerrunning.txt').open() as lock:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        assert subprocess.check_output(php + [str(worker)]) == b'busy'
        # Make only the disposable checkpoint eligible and check the killed row.
        checkpoint['next'] = 0
        (state/'indexer-progress.php').write_text('<?php exit; ?>\n' + json.dumps(checkpoint))
        recovery = temp/'recover.php'
        recovery.write_text('''<?php
chdir(__DIR__);
$_GET=array('u5indexcontinue'=>'1');
// Load class definitions without dispatching an actual database job.
$lock=fopen('fileversions/indexerrunning.txt','c+');flock($lock,LOCK_EX);
require 'indexer.php';fclose($lock);
$s=new U5IndexSlice(__DIR__.'/fileversions',true,false,false);
if (!$s->ready() || $s->beginResource(array('resource_key'=>'4142','deleted'=>0))) exit(2);
if (!$s->beginResource(array('resource_key'=>'4143','deleted'=>0))) exit(3);
$s->processed(array('resource_key'=>'4143','deleted'=>0));
if ($s->complete()!==false) exit(4);
$s->release();
''')
        subprocess.run(php + [str(recovery)], check=True, stdout=subprocess.DEVNULL)
        assert (state/'lastindex.txt').read_text() == '100'
        checkpoint = json.loads((state/'indexer-progress.php').read_text().split('\n', 1)[1])
        assert checkpoint['upper'] == '4142' and checkpoint['retryRound'] == 1
        print('Crash checks: competitor excluded; SIGKILL released the kernel lock; watermark retained; retry delayed.')
    finally:
        if process.poll() is None:
            process.kill()
            process.wait(timeout=5)
