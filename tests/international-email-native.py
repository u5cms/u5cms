#!/usr/bin/env python3
"""Native mail/backup MIME capture and the historical duplicate success path."""
import base64,email,email.policy,importlib.util,json,os,pathlib,subprocess,tempfile
ROOT=pathlib.Path(__file__).resolve().parent.parent
spec=importlib.util.spec_from_file_location('smtp',ROOT/'tests/international-email-smtp.py');smtp=importlib.util.module_from_spec(spec);spec.loader.exec_module(smtp)
checks=0
def check(value,label):
 global checks
 checks+=1
 if not value:raise AssertionError(label)
with tempfile.TemporaryDirectory(prefix='u5-native-mail-') as temp:
 temp=pathlib.Path(temp);capture=temp/'wire';sender=temp/'sendmail'
 sender.write_text('#!/bin/sh\ncat > '+str(capture)+'\nexit 0\n');sender.chmod(0o700)
 php=smtp.PHP+['-d','sendmail_path='+str(sender)]
 def run(kind,to,port=9):
  capture.unlink(missing_ok=True)
  p=subprocess.run(php+[str(ROOT/'tests/international-email-worker.php')],input=json.dumps(dict(kind=kind,to=to,port=port),ensure_ascii=True),text=True,capture_output=True,timeout=20)
  if p.returncode:raise RuntimeError(p.stdout+p.stderr)
  return json.loads(p.stdout)
 r=run('native','info@straße.ch');check(r['ok'],r);wire=capture.read_bytes();check(b'To: info@xn--strae-oqa.ch' in wire,'native IDN recipient');check(b'Native body' in wire,'native body preserved')
 r=run('duplicate','Müller@example.ch');check(not r['ok'],'EAI native duplicate skipped');check(not capture.exists(),'no unsupported EAI native submission')
 r=run('backup','info@straße.ch');check(r['ok'],r);m=email.message_from_bytes(capture.read_bytes(),policy=email.policy.default)
 check('sender@xn--strae-oqa.ch' in str(m['From']),'backup From IDN');parts=[p for p in m.walk() if p.get_content_disposition()=='attachment'];check(len(parts)==1,('native backup attachment',m.get_content_type(),m.defects,[(p.get_content_type(),str(p.get('Content-Disposition'))) for p in m.walk()]));check(parts[0].get_payload(decode=True)==b'Backup fixture\x00\xff\r\n','native backup attachment bytes')
 peer=smtp.Peer();r=run('backup','Müller@example.ch',peer.port);peer.finish();check(r['ok'],r);check(len(peer.messages)==1,'EAI backup message count')
 m=email.message_from_bytes(peer.messages[0],policy=email.policy.default);parts=[p for p in m.walk() if p.get_content_disposition()=='attachment'];check(len(parts)==1 and parts[0].get_payload(decode=True)==b'Backup fixture\x00\xff\r\n','EAI backup attachment bytes')
 # Reject the primary SMTP recipient after native mail accepted the same
 # password notification. The existing fallback must still count as success.
 peer=smtp.Peer(reject=True);r=run('plain-with-duplicate','reader@example.ch',peer.port);peer.finish();check(r['ok'],r);check(capture.exists(),'matching native duplicate accepted');check('could not be sent' not in r['output'],'successful fallback avoids false failure')
print(json.dumps(dict(checks=checks,php=smtp.PHP)))
