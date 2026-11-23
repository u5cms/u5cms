"""Exercise the two homepage endpoints in the disposable rename-web fixture."""
import base64
from html.parser import HTMLParser
import json
import os
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request

base, database = sys.argv[1:]
assert database.startswith('u5rename_web_')
php = [os.environ.get('U5_TEST_PHP', 'php')] + json.loads(os.environ.get('U5_TEST_PHP_ARGS', '[]'))
auth = base64.b64encode(('auditadmin:' + os.environ['U5CMS_FIXTURE_PASSWORD']).encode()).decode()


def sql(query):
    command = """
        $db=new mysqli('127.0.0.1', getenv('U5CMS_TEST_USER') ?: 'root',
            getenv('U5CMS_TEST_PASSWORD') ?: '', $argv[1], (int)getenv('U5CMS_TEST_PORT'));
        $result=$db->query(stream_get_contents(STDIN));
        if ($result instanceof mysqli_result) echo json_encode($result->fetch_all());
    """
    output = subprocess.check_output(php + ['-r', command, database], input=query, text=True)
    return json.loads(output) if output else None


class Form(HTMLParser):
    def __init__(self):
        super().__init__()
        self.options = {}
        self.token = None
        self.disabled = False

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'option':
            self.options[attrs['value']] = 'selected' in attrs
        if attrs.get('name') == 'u5scrtytknfrfrms':
            self.token = attrs['value']
        if attrs.get('type') == 'submit':
            self.disabled = 'disabled' in attrs


def request(data=None):
    endpoint = 'ishomepage.php' if data is None else 'ishomepage2.php'
    body = None if data is None else urllib.parse.urlencode(data).encode()
    req = urllib.request.Request(base+'/u5admin/'+endpoint, data=body, headers={'Authorization':'Basic '+auth})
    try:
        response = urllib.request.urlopen(req, timeout=30)
    except urllib.error.HTTPError as error:
        response = error
    with response:
        return response.status, response.read().decode('cp1252')


def form():
    status, body = request()
    assert status == 200, (status, body[:200])
    parsed = Form()
    parsed.feed(body)
    assert parsed.token
    return parsed


def selected():
    return sql("SELECT name FROM resources WHERE ishomepage=1 ORDER BY name")


# Keep this fixture independent of all subsequent rename tests.
sql('CREATE TABLE homepage_saved_flags AS SELECT name,deleted,ishomepage,hidden FROM resources')
try:
    sql("INSERT INTO resources (name,typ) VALUES ('hpteststart','p')")
    sql("UPDATE resources SET ishomepage=(name='hpteststart' AND deleted=0)")
    columns = ', '.join('content_'+str(i) for i in range(1,6))
    texts = ', '.join("REPEAT('"+lang+" ',4096)" for lang in ['Deutsch','francais','italiano','rumantsch','English'])
    sql(f"INSERT INTO resources (name,typ,hidden,deleted,{columns}) SELECT CONCAT('hptest',seq),'p',0,0,{texts} FROM seq_1_to_350")
    parsed = form()
    assert parsed.options['hpteststart'] and 'hptest350' in parsed.options and '' not in parsed.options
    def save(name):
        return request({'name':name,'u5scrtytknfrfrms':parsed.token})[0]
    assert save('hptest350') == 200 and selected() == [['hptest350']]
    assert form().options['hptest350'] and save('hptest350') == 200
    sql("UPDATE resources SET hidden=1 WHERE name='hptest1'")
    sql("UPDATE resources SET deleted=1 WHERE name='hptest2'")
    sql("UPDATE resources SET typ='f' WHERE name='hptest3'")
    for invalid in ['', '-', 'missing', 'hptest1', 'hptest2', 'hptest3', '../hpteststart']:
        assert save(invalid) == 400 and selected() == [['hptest350']], invalid
    sql("UPDATE resources SET hidden=1 WHERE typ='p'")
    empty = form()
    assert empty.disabled and not empty.options
    assert save('hpteststart') == 400 and selected() == [['hptest350']]
    sql("RENAME TABLE resources TO homepage_unavailable")
    try:
        for data in [None, {'name':'homepage','u5scrtytknfrfrms':parsed.token}]:
            status, body = request(data)
            assert status == 500 and 'Could not' in body, (status,body[:200])
    finally:
        sql("RENAME TABLE homepage_unavailable TO resources")
    assert selected() == [['hptest350']]
finally:
    sql("DELETE FROM resources WHERE name LIKE 'hptest%'")
    sql("UPDATE resources r JOIN homepage_saved_flags s USING(name,deleted) SET r.ishomepage=s.ishomepage,r.hidden=s.hidden")
    sql("DROP TABLE homepage_saved_flags")
print('Homepage HTTP regression passed with 350 five-language pages.')
