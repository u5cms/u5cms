#!/usr/bin/env python3
"""Test pinned patches against cached, hash-matching upstream Composer archives.

Run after Composer install has populated its cache. COMPOSER_CACHE_DIR selects
another cache; U5_TEST_PHP selects one runtime. No production/vendor file changes.
"""
from pathlib import Path
import json,zipfile,hashlib,tempfile,shutil,subprocess,os,sys
repo=Path(__file__).resolve().parent.parent;cache=Path(os.environ.get('COMPOSER_CACHE_DIR',str(Path.home()/'.cache/composer')));manifest=json.loads((repo/'patches/php9-return-types.json').read_text());sources={};checks=0
for f in manifest['files']:
 p=Path(f['path']);package='/'.join(p.parts[1:3]);relative='/'.join(p.parts[3:]);source=None
 for archive in (cache/'files'/package).glob('*.zip'):
  with zipfile.ZipFile(archive) as z:
   for member in z.namelist():
    if member.endswith('/'+relative):
     candidate=z.read(member)
     if hashlib.sha256(candidate).hexdigest()==f['before_sha256']:source=candidate;break
  if source is not None:break
 assert source is not None,p;sources[str(p)]=source;checks+=1
for php in [os.environ.get('U5_TEST_PHP','php')]:
 with tempfile.TemporaryDirectory() as d:
  root=Path(d)
  for directory in ['patches','tools']:(root/directory).mkdir()
  shutil.copy(repo/'patches/php9-return-types.json',root/'patches');shutil.copy(repo/'tools/apply-dependency-compatibility.php',root/'tools')
  for name,data in sources.items():p=root/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(data)
  def run(*args):return subprocess.run([php,str(root/'tools/apply-dependency-compatibility.php'),*args],capture_output=True,text=True)
  assert run('--check').returncode==1;checks+=1
  corrupt=root/manifest['files'][-1]['path'];corrupt.write_bytes(corrupt.read_bytes()+b'\n/* unexpected upstream */')
  assert run().returncode==1;checks+=1
  for name,data in sources.items():
   if root/name!=corrupt:assert (root/name).read_bytes()==data;checks+=1
  corrupt.write_bytes(sources[str(corrupt.relative_to(root))]);r=run();assert r.returncode==0,r.stderr;checks+=1
  for f in manifest['files']:assert hashlib.sha256((root/f['path']).read_bytes()).hexdigest()==f['after_sha256'];checks+=1
  assert run('--check').returncode==0;checks+=1
  assert run().stdout.startswith('0 dependency');checks+=1
 print(Path(php).name,'cold locked-source installation, final hashes, idempotence and all-before-write rejection passed.',flush=True)
print(checks,'dependency installation checks passed.')
