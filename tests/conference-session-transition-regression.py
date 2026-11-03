#!/usr/bin/env python3
"""Actual review popups retained across session account changes and logout."""
import argparse,importlib.util,json,pathlib,subprocess,tempfile
HERE=pathlib.Path(__file__).resolve().parent
spec=importlib.util.spec_from_file_location('conference_workflow',HERE/'conference-workflow-regression.py')
conference=importlib.util.module_from_spec(spec);spec.loader.exec_module(conference)
from fixture_environment import ensure_fixture_environment
ensure_fixture_environment()

def exercise(source,work,language):
    site=conference.Conference(source,work);checks=0
    def check(condition,label):
        nonlocal checks
        if not condition:raise AssertionError(label)
        checks+=1
    try:
        response=site.request('u5admin/zr.php',user='conferenceadmin');check(response['status']==200 and 'Fatal error' not in response['body'],'actual bootstrap')
        site.settings.update(usesessioninsteadofbasicauth='no',allowmultiu5formloginsonfrontendpages='no');site.write_config();site.sql('DELETE FROM formdata')
        ids=[]
        for title,reviewer in [('TRANSITION-ALPHA',conference.USERS[1]),('TRANSITION-BETA',conference.USERS[2])]:
            fields={'youremail':'','authors_mandatory':'(1)Author','affiliations_mandatory':'(1)Institute','title_mandatory':title,'abstract_mandatory':'ABSTRACT-'+title,'userupload1':'','submissiontype_mandatory':'fullpaper','correspondencetitle_mandatory':'Dr','correspondencefirstname_mandatory':'Anna','correspondencename_mandatory':'Author','youremail_mandatory':conference.USERS[0],'thanks':'thanks'}
            response=site.request('formsave.php?n=!uploadabstract&l='+language,fields,user=conference.USERS[0]);check(response['status']==200 and 'Fatal error' not in response['body'],'localized abstract submission')
            record=site.rows('!uploadabstract')[-1];ids.append(record['id'])
            response=site.request('u5admin/notesave.php?id='+record['id'],{'note':'('+reviewer+')'},user='conferenceadmin');check(response['status']==200,'organizer assigns the reviewer')
        site.settings['usesessioninsteadofbasicauth']='yes';site.write_config()
        run=subprocess.run(['node',str(HERE/'conference-session-transition-browser.cjs')],input=json.dumps({'base':'http://127.0.0.1:'+str(site.port),'language':language,'idA':ids[0],'idB':ids[1],'userA':conference.USERS[1],'userB':conference.USERS[2],'password':conference.PASSWORD}),text=True,capture_output=True,timeout=150)
        if run.returncode:raise AssertionError(run.stdout+run.stderr)
        browser=json.loads(run.stdout);checks+=browser['checks']
        rows=site.rows('!reviewscore');check(len(rows)==2,'rejected identity and logged-out attempts must not create rows or history')
        active=[r for r in rows if r['status']=='1'];check(len(active)==2,'one active review remains per reviewer')
        by_owner={r['authuser']:conference.values(r) for r in active}
        check(by_owner[conference.USERS[1]]['comments']=='RECOVERED-A' and by_owner[conference.USERS[1]]['id_mandatory']==ids[0],'first reviewer retains the correct abstract and saved comment')
        check(by_owner[conference.USERS[2]]['comments']=='CONFIRMED-B' and by_owner[conference.USERS[2]]['id_mandatory']==ids[1],'second reviewer retains the correct abstract and saved comment')
        return {'language':language,'checks':checks,'status':'passed'}
    finally:site.close()

def main():
    parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('--source',type=pathlib.Path,default=HERE.parent);parser.add_argument('--output',type=pathlib.Path);args=parser.parse_args();results=[]
    with tempfile.TemporaryDirectory(prefix='u5-review-session-') as temp:
        for language in ['de','en','fr']:
            work=pathlib.Path(temp)/language;work.mkdir();result=exercise(args.source.resolve(),work,language);results.append(result);print(json.dumps(result),flush=True)
    report={'results':results,'checks':sum(r['checks'] for r in results),'failures':[]}
    if args.output:args.output.write_text(json.dumps(report,indent=2))
    return 0
if __name__=='__main__':raise SystemExit(main())
