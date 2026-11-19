#!/usr/bin/env python3
"""Exercise both installed seeds using the actual responsive CMS, without external services."""
from pathlib import Path
import json,os,shutil,socket,subprocess,tempfile,time,urllib.request
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
os.environ.pop('U5CMS_TEST_TEMPLATE',None)
repo=Path(__file__).resolve().parent.parent
php=[os.environ.get('U5_TEST_PHP','php')]
for seed in ['u5standard.sql','u5conference.sql']:
 with tempfile.TemporaryDirectory(prefix='u5-responsive-web-') as tmp:
  work=Path(tmp);site=work/'site';shutil.copytree(repo,site,ignore=shutil.ignore_patterns('.git','ci-results','__pycache__'))
  fixture=site/'tests/responsive-setup.php';fixture.write_bytes((repo/'tests/codeassets-web-fixture.php').read_bytes().replace(b"'/u5standard.sql'",("'/"+seed+"'").encode()))
  setup=subprocess.run(php+[str(fixture),str(site)],capture_output=True,text=True)
  if setup.returncode:raise RuntimeError(setup.stdout+setup.stderr)
  db=json.loads(setup.stdout)['database']
  extra=site/'tests/responsive-extra.php'
  extra.write_text(r'''<?php
chdir($argv[1]);require 'connect.inc.php';
function q($s){if(mysql_query($s)===false)throw new RuntimeException(mysql_error());}
function v($s){return "'".mysql_real_escape_string($s)."'";}
q("UPDATE languages SET lan1na='de',lan2na='fr',lan3na='it',lan4na='rm',lan5na='en'");
// Verify the template really came from the imported seed, not a hand-built browser fixture.
$row=mysql_fetch_array(mysql_query("SELECT content_1 FROM resources WHERE name='htmltemplate!html'"));
$normalize=function($s){return trim(preg_replace('/\?u5v=[0-9]+/','',$s));};
if($normalize($row['content_1'])!==$normalize(file_get_contents('r/htmltemplate!html.tpl')))throw new RuntimeException('Seed/reset template mismatch');
if($normalize($row['content_1'])!==$normalize(file_get_contents('r/htmltemplate.html')))throw new RuntimeException('Seed/export template mismatch');
foreach(array('responsive-image'=>'i','responsive-youtube'=>'y','responsive-album'=>'a','responsive-free'=>'f','responsive-freewidth'=>'f','responsive-freeheight'=>'f') as $name=>$type){q('INSERT INTO resources(name,typ,deleted,hidden,title_1,desc_1,content_1) VALUES('.v($name).','.v($type).",0,0,'Example image',".v($type==='y'?'fixture-video':'A caption').",'Video caption')");@mkdir('r/'.$name);copy('r/logo/logo_en.jpg','r/'.$name.'/'.$name.'_en.jpg');}
q("UPDATE resources SET key_1='800',key_2='400' WHERE name='responsive-free'");
q("UPDATE resources SET key_1='800',key_2='' WHERE name='responsive-freewidth'");
q("UPDATE resources SET key_1='',key_2='600' WHERE name='responsive-freeheight'");
q("INSERT INTO formdata(formname,headcsv,datacsv,status) VALUES ('responsivegrid',_latin1 0xb76e616d653bb76e756d6265723b,_latin1 0xb7416e6e613bb734323b,1)");
$content=<<<'HTML'
[[Responsive fixture]]
A long word: ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789
[:::::responsive-image]
[::responsive-youtube]
[::responsive-album]
[h:]<div style="clear:both"></div><section id="freeboth">[:h]
[::responsive-free]
[h:]</section><div style="clear:both"></div><section id="freewidth">[:h]
[::responsive-freewidth]
[h:]</section><div style="clear:both"></div><section id="freeheight">[:h]
[::responsive-freeheight]
[h:]</section>

<div style="clear:both"></div>
<table id="dat-grid"><caption>Data table</caption><thead><tr><th>Name</th><th>Number</th></tr></thead><tbody>
[name,number|responsivegrid|ALL||1|<tr><td>|</td></tr>|</td><td>:dat]
</tbody></table>
<table id="wide-grid" style="width:1400px;min-width:1400px"><caption>Wide data remains available</caption><tr><th>First</th><th>Last</th></tr><tr><td>Start</td><td>End</td></tr></table>
<pre>very_long_unbroken_preformatted_line_preserves_its_text_and_horizontal_scrolling_012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789</pre>
<form id="responsive-form" name="u5form" method="post" action="formsave.php?n=responsivefixture&amp;l=en" target="responsive-save">
<fieldset><legend>A long form legend that needs to wrap on a narrow screen without clipping</legend>
<label for="responsive-name">Name</label><input id="responsive-name" name="name_mandatory" required>
<label for="responsive-comment">Comment</label><textarea id="responsive-comment" name="comment"></textarea>
<label><input id="responsive-checkbox" name="checkbox" type="checkbox" value="yes">Remember this choice</label>
<input name="em2me" type="hidden" value="off"><input name="em2cu" type="hidden" value="off"><input name="in2my" type="hidden" value="off"><input name="qv2bo" type="hidden" value="off">
<input name="thanks" type="hidden" value="responsiveSaved()"><button id="responsive-submit" type="submit">Save</button>
</fieldset></form>
<iframe name="responsive-save" id="responsive-save" title="Save result" hidden></iframe>
<script>window.responsiveForm=document.getElementById('responsive-form');window.responsiveEvents=0;window.responsiveForm.addEventListener('proof',function(){window.responsiveEvents++});function responsiveSaved(){window.responsiveWasSaved=true;}</script>
[:h]
HTML;
q("INSERT INTO resources(name,typ,deleted,hidden,content_1) VALUES ('responsivefixture','p',0,0,".v($content).")");
$nav=mysql_fetch_array(mysql_query("SELECT content_1 FROM resources WHERE name='navigation'"));
q("UPDATE resources SET content_1=".v($nav['content_1']."\n#[Responsive:responsivefixture]")." WHERE name='navigation'");
echo "Imported template/exports/reset parity and fixture data OK.\n";
''')
  try:
   subprocess.run(php+['-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],str(extra),str(site)],check=True)
   with socket.socket() as s:s.bind(('127.0.0.1',0));port=s.getsockname()[1]
   base='http://127.0.0.1:'+str(port)
   router=site/'router.php';router.write_text('''<?php
header('X-Content-Type-Options: nosniff');$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$file=realpath(__DIR__.$path);
if(!$file||($file!==__DIR__&&strpos($file,__DIR__.'/')!==0)){http_response_code(404);return;}if(is_dir($file))$file.='/index.php';
if(!is_file($file)){http_response_code(404);return;}if(substr($file,-4)==='.php'){chdir(dirname($file));require $file;return;}
$types=array('js'=>'text/javascript','css'=>'text/css','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','gif'=>'image/gif');header('Content-Type: '.($types[pathinfo($file,PATHINFO_EXTENSION)]??'application/octet-stream'));readfile($file);
''')
   # Switch layouts through the actual editable template for the split-navigation case.
   (site/'fixture-split.php').write_text('''<?php
require 'connect.inc.php';$path=u5CodeTemplatePath();$s=file_get_contents($path);$s=str_replace('    <!--','',$s);$s=preg_replace('/    REMOVING HTML COMMENT TAG HERE.*?-->/s','',$s);$s=str_replace('id="navLeft"','id="navLeftSubTop"',$s);file_put_contents($path,$s);echo 'split';
''')
   with (work/'server.log').open('w+') as log:
    server=subprocess.Popen(php+['-d','opcache.enable=0','-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],'-S','127.0.0.1:'+str(port),'-t',str(site),str(router)],cwd=site,stdout=log,stderr=log)
    try:
     for i in range(100):
      try:urllib.request.urlopen(base+'/?c=responsivefixture',timeout=2).close();break
      except OSError:time.sleep(.05)
     else:raise RuntimeError('CMS did not start')
     subprocess.run(['node',str(repo/'tests/responsive-browser.cjs'),base,seed,str(site)],check=True)
    except Exception:
     log.flush();print((work/'server.log').read_text()[-5000:]);raise
    finally:server.terminate();server.wait(timeout=10)
  finally:subprocess.run(php+[str(repo/'tests/codeassets-web-fixture.php'),'cleanup',db],check=True)
