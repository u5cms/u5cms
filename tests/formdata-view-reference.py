#!/usr/bin/env python3
"""Compare complete real backend data views with an executable original main.

Uses fresh databases and the original SQL seed through main-reference's native
fixture plumbing. No mail transport is called. HTTP 200 alone is insufficient:
the complete table, installation extension hook and closing HTML must appear.
"""
import argparse
import importlib.util
import json
import pathlib
import re
import tempfile
import time
import urllib.parse

HERE = pathlib.Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('reference', HERE/'main-reference-regression.py')
reference = importlib.util.module_from_spec(spec)
spec.loader.exec_module(reference)


def exercise(site):
    response = site.request('u5admin/zr.php', user='referenceadmin')
    if response['status'] != 200:
        raise AssertionError('The actual upgrade entry did not succeed.')
    site.resource('modify!formdata2!php', [b'[h:]<div id="review-after-render">REVIEW-AFTER-RENDER</div>[:h]'])
    site.resource('reviewform', [b'[h:]<form><input name="reviewvalue"></form>[:h]'])
    now = int(time.time())
    for number, status in enumerate([1, 2, 3, 4, 5, 5, 6, 7], 1):
        value = ['ALPHA red', 'BETA blue', 'ALPHA blue', 'GAMMA red',
                 'ALPHA recent', 'ALPHA old', 'ALPHA former', 'ALPHA imported'][number-1]
        mutation = now-40*86400 if number == 6 else now
        site.sql("INSERT INTO formdata(id,formname,headcsv,datacsv,status,notes,authuser,time,lastmut,ip,humantime) VALUES ("+
                 str(97000+number)+",'reviewform','reviewvalue;',"+reference.literal(value+';')+','+
                 str(status)+','+reference.literal('note '+str(9-number))+','+
                 reference.literal('user'+str(number))+',1700000000,'+str(mutation)+",'127.0.0.1','2023-11-14 22:13:20')")
    cases = [
        ('missing-filter', ''), ('empty-filter', '&f='),
        ('whitespace-filter', '&f=%20%20%09'), ('quote-only-filter', '&f=%22'),
        ('single-filter', '&f=ALPHA'), ('or-filter', '&f=ALPHA%20blue'),
        ('and-filter', '&f=ALPHA%20blue', 'fdbool=and'),
        ('negative-filter', '&f=-ALPHA'), ('no-results', '&f=NO-MATCH-HERE'),
        ('notes-order', '', 'fdorder=no'), ('authuser-order', '', 'fdorder=au'),
        ('field-order', '', 'fdorder=ff'),
    ]
    cases += [('status-'+str(status), '&s='+str(status)) for status in range(1, 8)]
    observations = {}
    for endpoint in ['formdata2.php', 'formdatamail.php']:
        for case in cases:
            name, suffix = case[:2]
            response = site.request('u5admin/'+endpoint+'?n=reviewform'+suffix,
                                    user='referenceadmin', cookies=case[2] if len(case)>2 else None)
            body = response['body']
            observations[endpoint+'/'+name] = {
                'status': response['status'],
                'complete': '</html>' in body.lower(),
                'extension': 'REVIEW-AFTER-RENDER' in body,
                'rows': re.findall(r'id="i(9700[1-8])"', body),
                'fatal_output': 'Fatal error' in body or 'Uncaught TypeError' in body,
            }
        response = site.request('u5admin/'+endpoint+'?n=reviewempty', user='referenceadmin')
        observations[endpoint+'/empty-form'] = {
            'status': response['status'], 'complete': '</html>' in response['body'].lower(),
            'extension': 'REVIEW-AFTER-RENDER' in response['body'],
            'rows': re.findall(r'id="i(9700[1-8])"', response['body']),
            'fatal_output': 'Fatal error' in response['body'] or 'Uncaught TypeError' in response['body'],
        }
        denied = site.request('u5admin/'+endpoint+'?n=reviewform', user='referenceeditor')
        observations[endpoint+'/denied'] = {
            'denied': 'You do not have enough rights' in denied['body'],
            'rows': re.findall(r'id="i(9700[1-8])"', denied['body']),
            'extension': 'REVIEW-AFTER-RENDER' in denied['body'],
        }
    return observations


def assert_original(observations):
    for name, record in observations.items():
        if name.endswith('/denied'):
            assert record == {'denied': True, 'rows': [], 'extension': False}, name
        else:
            assert record['status'] == 200 and record['complete'] and record['extension'] and not record['fatal_output'], name
    for endpoint in ['formdata2.php', 'formdatamail.php']:
        prefix = endpoint+'/'
        assert observations[prefix+'missing-filter']['rows'] == ['97004', '97003', '97002', '97001']
        assert observations[prefix+'empty-filter']['rows'] == observations[prefix+'missing-filter']['rows']
        assert observations[prefix+'whitespace-filter']['rows'] == observations[prefix+'missing-filter']['rows']
        assert observations[prefix+'single-filter']['rows'] == ['97003', '97001']
        assert observations[prefix+'and-filter']['rows'] == ['97003']
        assert observations[prefix+'negative-filter']['rows'] == ['97004', '97002'], repr(observations[prefix+'negative-filter'])
        assert observations[prefix+'no-results']['rows'] == []
        assert observations[prefix+'status-5']['rows'] == ['97005']
        assert observations[prefix+'empty-form']['rows'] == []


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--baseline', type=pathlib.Path, required=True)
    parser.add_argument('--target', action='append', default=[])
    parser.add_argument('--output', type=pathlib.Path, required=True)
    args = parser.parse_args()
    report = {'method': 'Execute original first; compare complete HTTP views, actual row selections and installation hook.', 'results': []}
    args.output.parent.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory(prefix='u5-view-reference-') as temporary:
        work = pathlib.Path(temporary)
        original = reference.Site(args.baseline, args.baseline, work, 'original')
        try:
            expected = exercise(original)
            report['original'] = expected
            args.output.write_text(json.dumps(report, indent=2))
            assert_original(expected)
        finally:
            original.close()
        report['original'] = expected
        args.output.write_text(json.dumps(report, indent=2))
        for target in args.target:
            label, source = target.split('=', 1)
            site = reference.Site(pathlib.Path(source), args.baseline, work, label)
            try:
                actual = exercise(site)
                differences = reference.differences(expected, actual)
                record = {'label': label, 'cases': len(actual), 'differences': differences}
            except Exception as error:
                record = {'label': label, 'error': str(error)}
            finally:
                site.close()
            report['results'].append(record)
            args.output.write_text(json.dumps(report, indent=2))
            print(json.dumps({'label': label, 'cases': record.get('cases'), 'differences': len(record.get('differences', {})), 'error': record.get('error')}), flush=True)
    failed = any(item.get('differences') or item.get('error') for item in report['results'])
    print('Complete view cases:', len(expected)*(1+len(args.target)), '; comparison passed:', not failed)
    return int(failed)


if __name__ == '__main__':
    raise SystemExit(main())
