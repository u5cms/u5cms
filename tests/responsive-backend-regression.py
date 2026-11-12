#!/usr/bin/env python3
"""Exercise renderer callers in the real backend against the pre-split renderer."""
import argparse,importlib.util,json,pathlib,tempfile
HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('reference',HERE/'main-reference-regression.py')
ref=importlib.util.module_from_spec(spec);spec.loader.exec_module(ref)
p=argparse.ArgumentParser();p.add_argument('--baseline',type=pathlib.Path,required=True);p.add_argument('--output',type=pathlib.Path);args=p.parse_args()
results=[]
with tempfile.TemporaryDirectory(prefix='u5-render-backend-') as tmp:
 for label,source in [('original',args.baseline),('current',HERE.parent)]:
  site=ref.Site(source,args.baseline,pathlib.Path(tmp),label)
  try:
   site.resource('renderprobe',[b'[[Title]]\n[logo]\n[:::logo]']*5)
   response=site.request('u5admin/countblockelements.php?c=renderprobe&l=en&l1=de&l2=en&l3=fr&l4=it&l5=es',user='referenceadmin')
   result={'label':label,'status':response['status'],'body':response['body']}
   results.append(result)
   print(json.dumps(result),flush=True)
  finally:site.close()
assert results[0]['status']==200 and 'Fatal error' not in results[0]['body'],'Original backend oracle must succeed'
assert results[1]['status']==results[0]['status'],results[1]
assert results[1]['body']==results[0]['body'],'Backend renderer output changed'
if args.output:args.output.write_text(json.dumps({'passed':True,'results':results},indent=2))
print('Backend block-element checker: original and current outputs match.')
