#!/usr/bin/env python3
"""Differential syntax/dat oracle against an immutable pre-responsive renderer.

Uses real seeded MariaDB tables, all dat a-n options and real resource files.
The baseline is supplied by --baseline; it is never generated from the new code.
"""
import argparse,base64,json,os,pathlib,re,shutil,subprocess,tempfile
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()
ROOT=pathlib.Path(__file__).resolve().parent.parent
PHP=os.environ.get('U5_TEST_PHP','php')
p=argparse.ArgumentParser();p.add_argument('--baseline',type=pathlib.Path,required=True);p.add_argument('--output',type=pathlib.Path);args=p.parse_args()
assert (args.baseline/'render.inc.php').is_file()
cases=[]
def case(name,source,get=None,settings=None):
 cases.append(dict(name=name,source=base64.b64encode(source.encode('cp1252')).decode(),get=get or {},settings=settings or {"executephp":"off"}))
def dat(name,**kw):
 vals=['name*,comment,?id','responsive-data','ALL','','','<article>','</article>',' / ','id ASC','<br>','','','','0']
 for k,v in kw.items():vals[ord(k)-ord('a')]=v
 case('dat-'+name,'['+'|'.join(vals)+':dat]')
for source in ['', '0', ' A\n\nB ', 'Über € &amp; &#20013; < > "', '[[Heading]]\n[[[Subheading]]]', '[h:]<div data-x="[literal]">RAW &amp; <?php echo "x"; ?></div>[:h]', '[unknown]\n[missing:unknown]','[h:]<script>var a=[1,2];</script>[:h]', '[fo:]<input name="name_mandatory">[:fo]', '[o:]---first---\n---second---[:o]', '[h:]<table><tr><td>Raw table</td></tr></table>[:h]', '[_pagename_] [_language_] [_mymail_] [_myurl_] [_pagename!_]']:
 case('syntax-'+str(len(cases)),source)
for n in range(6):
 for suffix in ['', '!']:
  dat('mode-'+str(n)+suffix,a='name*,comment,?notes',n=str(n)+suffix)
for field in ['name*','name_mandatory','Name_mandatory','missing','comment','?notes','?id,?formname,?time,?ip,?status,?lastmut,?authuser,?humantime,?sent','?missing']:
 dat('fields-'+field,a=field)
for spec in ['','1','2','999999','ALL','?']:
 dat('record-'+spec,c=spec)
case('dat-id-get','[name*|responsive-data|?:dat]',get={'id':'2'})
for filt in ['','review','(reader)','(authuser)','SELF','datacsv LIKE %Anna%','datacsv LIKE %·Anna;%','notes LIKE %review%','WHERE status=1 AND id>1','WHERE status=1 AND id!=1']:
 dat('filter-'+filt,d=filt)
case('dat-WHERE-raw','[h:][name*|responsive-data|ALL|WHERE status=1 AND id>1:dat][:h]')
case('dat-SQL-error-hidden','[name*|responsive-data|ALL|WHERE status=1 AND id>1:dat]',settings={'showdatcommandsqlerrors':'no'})
# Boolean LIKE terms are documented GET values; their pipes never split the syntax.
for value in ['datacsv LIKE %Anna%||%Bea%','datacsv LIKE %Anna%&&%alpha%','datacsv LIKE %·Anna;%','review','WHERE status=1']:
 case('dat-get-filter-'+value,'[name*|responsive-data|ALL|?:dat]',get={'notes':value})
for status in ['','1','2','0','missing']:dat('status-'+status,e=status)
for order in ['','id DESC','datacsv ASC','id DESC LIMIT 0,2','LIMIT 1,1','?','? LIMIT 0,1']:
 dat('order-'+order,i=order)
case('dat-get-sort','[name*|responsive-data|ALL||||||? LIMIT 0,2:dat]',get={'sort':'ORDER BY id DESC'})
case('dat-invalid-sort','[name*|responsive-data|ALL||||||?:dat]',get={'sort':'id DESC'})
for sep in ['', '<br>', '<em>,</em>', '<br>@@<hr>', ' / @@ &amp; ']:dat('delimiter-'+sep,h=sep)
for newline in ['', '<br>', '<hr>', ' - ']:dat('newline-'+newline,a='comment,?notes',j=newline)
for val,out in [('Anna','<strong>YES</strong>'),('unknown','YES'),('','EMPTY')]:dat('condition-'+val,a='name*',l=val,m=out)
dat('placeholders',f='$id:$st:$se:$la:$ip:$hu:$au:$ah<article>',g='</article>')
dat('whitespace',b=' responsive-data ')
dat('absent-form',b='missing-data')
for size in ['', '!', '300x0','0x100','500x300','500+300','300x0!','0x100!','500x300!']:
 dat('uploads-'+size,a='userupload1,userupload2,userupload3',k=size)
for mode in ['off','onallpages','inarchiveonly']:
 case('php-policy-'+mode,'[h:][<?php echo "name*"; ?>|responsive-data|ALL:dat][:h]',settings={'executephp':mode})
for lang in ['de','en','fr','it','es']:
 case('languages-'+lang,'[responsive-page] [$$$:responsive-page]',get={'l':lang})
for typ,name,maxc in [('i','responsive-image',5),('f','responsive-free',2),('y','responsive-youtube',5),('v','testmp3',6),('a','responsive-album',8),('p','responsive-page',2),('d','responsive-doc',1),('e','responsive-link',1)]:
 for n in range(maxc+1):case('resource-'+name+'-'+str(n),'['+':'*n+name+']')
for preview in ['1','2']:
 case('preview-'+preview,'[h:]<form id="f"><input name="x"></form>[:h]',get={'p':preview})
with tempfile.TemporaryDirectory(prefix='u5-responsive-render-') as temp:
 root=pathlib.Path(temp)/'site';shutil.copytree(ROOT,root,ignore=shutil.ignore_patterns('.git','ci-results'))
 setup=subprocess.run([PHP,str(ROOT/'tests/codeassets-web-fixture.php'),str(root)],capture_output=True,text=True)
 if setup.returncode:raise RuntimeError(setup.stdout+setup.stderr)
 db=json.loads(setup.stdout)['database']
 try:
  # Keep exact baseline bytes in the same root so its original relative includes work.
  (root/'reference-render.inc.php').write_bytes((args.baseline/'render.inc.php').read_bytes())
  fixture=r'''<?php
chdir($argv[1]);require 'connect.inc.php';
function q($s){if(mysql_query($s)===false)throw new RuntimeException(mysql_error());}
function v($s){return "'".mysql_real_escape_string($s)."'";}
q('DELETE FROM formdata');
$rows=array(array('Anna','alpha <b>bold</b> [[:not-an-object]]'."\n".'https://example.invalid/a','review (reader)',1,'reader'),array('Bea','beta; literal &amp; &#20013;','other',2,'other'),array('Carl','0','review'."\n".'visible|||private',1,'reader'));
foreach($rows as $i=>$r){$fields=array('name_mandatory','comment','userupload1','userupload2','userupload3');$values=array($r[0],$r[1],'https://fixture.invalid/r/P/example.jpg','https://fixture.invalid/fileversions/useruploads/private.png','https://fixture.invalid/r/P/example.pdf');$head='';$data='';foreach($fields as $k=>$f){$head.="\xb7".$f.';';$data.="\xb7".str_replace(';',',.',$values[$k]).';';}
q('INSERT INTO formdata (id,formname,headcsv,datacsv,status,notes,authuser,time,humantime,lastmut,ip) VALUES ('.($i+1).",'responsive-data',".v($head).','.v($data).','.$r[3].','.v($r[2]).','.v($r[4]).",1700000000,'2023-11-14 22:13:20',1700000100,'127.0.0.1 proxy')");}
foreach(array('responsive-page'=>'p','responsive-image'=>'i','responsive-free'=>'f','responsive-youtube'=>'y','responsive-album'=>'a','responsive-doc'=>'d','responsive-link'=>'e') as $name=>$type){q('INSERT INTO resources(name,typ,deleted,hidden,title_1,title_2,content_1,content_2,desc_1,desc_2,key_1,key_2) VALUES('.v($name).','.v($type).",0,0,'Titel','Title','Caption DE','Caption EN',".v($type==='y'?'example-video-id':($type==='e'?'https://example.invalid/?a=1&b=2':'Description')).",'',800,400)");@mkdir('r/'.$name);copy('r/logo/logo_en.jpg','r/'.$name.'/'.$name.'_en.jpg');}
@mkdir('r/P');copy('r/logo/logo_en.jpg','r/P/example.jpg');file_put_contents('r/P/example.pdf','PDF fixture');
$corpus=array();$result=mysql_query("SELECT name,content_1,content_2,content_3,content_4,content_5 FROM resources WHERE typ='p' AND deleted!=1");
while($row=mysql_fetch_array($result))foreach(range(1,5) as $language)if(($row['content_'.$language]??'')!=='')$corpus[]=array('name'=>'seed-page-'.$row['name'].'-'.$language,'source'=>base64_encode($row['content_'.$language]),'settings'=>array('executephp'=>'off'));
file_put_contents('render-corpus.json',json_encode($corpus));
echo 'seeded';
'''
  (root/'fixture-render.php').write_text(fixture)
  r=subprocess.run([PHP,'-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],str(root/'fixture-render.php'),str(root)],capture_output=True,text=True)
  if r.returncode:raise RuntimeError(r.stdout+r.stderr)
  cases.extend(json.loads((root/'render-corpus.json').read_text()))
  source=root/'cases.json';source.write_text(json.dumps(cases))
  def run(renderer,mode):
   r=subprocess.run([PHP,'-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],str(ROOT/'tests/responsive-render-worker.php'),str(root),str(root/renderer),str(source),mode],capture_output=True,text=True)
   if r.returncode:raise RuntimeError(r.stdout+r.stderr)
   try:return json.loads(r.stdout)
   except Exception:raise RuntimeError(r.stdout[:3000]+r.stderr)
  old=run('reference-render.inc.php','legacy');new=run('render.inc.php','legacy');responsive=run('render.inc.php','responsive')
  for a,b in zip(old,new):
   assert a==b,(a['name'],'legacy output mismatch',base64.b64decode(a['html']),base64.b64decode(b['html']))
  for a,b in zip(old,responsive):
   if not a['name'].startswith(('resource-responsive-youtube-','resource-testmp3-','resource-responsive-free-','seed-page-')):
    assert a==b,(a['name'],'responsive changed syntax/dat output')
  # Independent expected effects: a comparison alone could preserve a broken fixture.
  outputs={a['name']:base64.b64decode(a['html']).decode('cp1252') for a in new}
  assert 'Anna' in outputs['dat-fields-name*'] and 'Bea' in outputs['dat-fields-name*']
  assert outputs['dat-fields-name*']==outputs['dat-fields-name_mandatory']
  assert 'Anna' in outputs['dat-filter-SELF'] and 'Carl' in outputs['dat-filter-SELF'] and 'Bea' not in outputs['dat-filter-SELF']
  assert 'Anna' in outputs['dat-filter-(authuser)'] and 'Bea' not in outputs['dat-filter-(authuser)']
  assert 'Bea' in outputs['dat-id-get'] and 'Anna' not in outputs['dat-id-get']
  assert outputs['dat-order-id DESC'].index('Carl')<outputs['dat-order-id DESC'].index('Bea')
  assert 'private' not in outputs['dat-fields-?notes']
  assert outputs['dat-get-sort'].index('Carl')<outputs['dat-get-sort'].index('Bea') and 'Anna' not in outputs['dat-get-sort']
  assert 'Carl' in outputs['dat-filter-WHERE status=1 AND id!=1'] and 'Anna' not in outputs['dat-filter-WHERE status=1 AND id!=1']
  assert 'Carl' in outputs['dat-WHERE-raw'] and 'Anna' not in outputs['dat-WHERE-raw']
  assert 'SQL syntax' in outputs['dat-filter-WHERE status=1 AND id>1'] and outputs['dat-SQL-error-hidden']==''
  assert 'df.php?' in outputs['dat-uploads-300x0!'] and 'thumb.php?' in outputs['dat-uploads-300x0!']
  assert '<form' not in outputs['preview-1'] and '<xform' in outputs['preview-1']
  # Fatal denials must run separately: die() intentionally stops the rendering request.
  boundaries=[
   ('disabled-full-WHERE','[name*|responsive-data|ALL|WHERE id>0:dat]',{}, {'allowfullwhereasdatparameterd':'no'},'FATAL ERROR:'),
   ('array-id','[name*|responsive-data|?:dat]',{'id':['1']},{},'Invalid request parameter.'),
   ('array-notes','[name*|responsive-data|ALL|?:dat]',{'notes':['review']},{},'Invalid request parameter.'),
   ('array-sort','[name*|responsive-data|ALL||||||?:dat]',{'sort':['id DESC']},{},'Invalid request parameter.'),
  ]
  for name,markup,get,settings,expected in boundaries:
   source.write_text(json.dumps([dict(name=name,source=base64.b64encode(markup.encode()).decode(),get=get,settings=settings)]))
   responses=[]
   for renderer,mode in [('reference-render.inc.php','legacy'),('render.inc.php','legacy'),('render.inc.php','responsive')]:
    r=subprocess.run([PHP,'-d','mysqli.default_port='+os.environ['U5CMS_TEST_PORT'],str(ROOT/'tests/responsive-render-worker.php'),str(root),str(root/renderer),str(source),mode],capture_output=True)
    assert r.returncode==0,(name,r.stdout,r.stderr)
    assert expected.encode() in r.stdout,(name,'expected denial absent',r.stdout,r.stderr)
    responses.append(r.stdout)
   assert responses[0]==responses[1]==responses[2],(name,'denial changed')
  report={'baseline':str(args.baseline),'cases':len(cases),'legacy_byte_comparisons':len(cases),'responsive_syntax_dat_comparisons':sum(not x['name'].startswith(('resource-responsive-youtube-','resource-testmp3-','resource-responsive-free-','seed-page-')) for x in cases),'expected_effects':13,'denial_cases':len(boundaries),'passed':True,'names':[x['name'] for x in cases]}
  if args.output:args.output.parent.mkdir(parents=True,exist_ok=True);args.output.write_text(json.dumps(report,indent=2))
  print(json.dumps({k:v for k,v in report.items() if k!='names'}))
 finally:subprocess.run([PHP,str(ROOT/'tests/codeassets-web-fixture.php'),'cleanup',db],check=True)
