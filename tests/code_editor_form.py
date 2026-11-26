"""Exercise the HTML form offered by the code editor in a disposable CMS."""
from html.parser import HTMLParser
from urllib.parse import urljoin


class CodeForm(HTMLParser):
    def __init__(self, html):
        super().__init__()
        self.action = None
        self.fields = {}
        self.textarea = None
        self.in_form = False
        self.feed(html)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'form':
            self.in_form = attrs.get('name') == 'form1'
            if self.in_form:
                self.action = attrs['action']
        if not self.in_form or not attrs.get('name') or 'disabled' in attrs:
            return
        name = attrs['name']
        if tag == 'input' and attrs.get('type', 'text') not in ('submit', 'button'):
            self.fields[name] = attrs.get('value', '')
        elif tag == 'textarea':
            self.textarea = name
            self.fields[name] = ''

    def handle_data(self, text):
        if self.textarea:
            self.fields[self.textarea] += text

    def handle_endtag(self, tag):
        if tag == 'textarea':
            self.textarea = None
        if tag == 'form':
            self.in_form = False


def check_code_editor(site, request, sql):
    typed = (site / 'codeassets.inc.php').is_file()
    name = 'editorsample!css' if typed else 'editorsample'
    path = '/u5admin/coding.php?name=' + name
    where = " WHERE name='" + name + "'"
    original = '/* initial */'
    sql("INSERT INTO resources(name,typ,deleted,lastmut,content_1,title_2) "
        "VALUES('" + name + "','c',0,100,'" + original + "','retained title')")

    def open_form():
        status, html = request(path)
        assert status == 200
        form = CodeForm(html)
        assert form.action and form.fields['name'] == name
        return form

    def submit(form, text):
        form.fields['content_1'] = text
        form.fields['changes'] = '1'
        return request(urljoin(path, form.action), form.fields)

    old_form = open_form()
    assert old_form.fields['content_1'] == original
    assert 'title_2' not in old_form.fields
    # A rapid save can advance the stored revision past the current wall clock.
    sql("UPDATE resources SET lastmut=UNIX_TIMESTAMP()+30" + where)
    config = site / 'config.php'
    previous_config = config.read_bytes()
    try:
        config.write_bytes(previous_config + b"\n$checksaveversionconflictinbackend='all';\n")
        for text in ('/* CSS saved through the real form */', ''):
            current = open_form()
            stored_revision = sql('SELECT lastmut FROM resources' + where)[0]['lastmut']
            assert int(current.fields['coco']) == int(stored_revision)
            status, body = submit(current, text)
            assert status == 200 and 'saved' in body and open_form().fields['content_1'] == text
            row = sql('SELECT content_1,title_2 FROM resources' + where)[0]
            assert row == {'content_1': text, 'title_2': 'retained title'}
            version = sql('SELECT content_1,title_2 FROM resources_log' + where +
                          ' ORDER BY lastmut DESC LIMIT 1')[0]
            assert version == row
            output = (site / 'r' / 'editorsample.css').read_bytes()
            if text:
                assert text.encode() in output
            else:
                assert not output.removeprefix(b'\xef\xbb\xbf').strip()
        status, body = submit(old_form, '/* stale edit */')
        assert status == 200 and 'CONFLICT' in body
        assert open_form().fields['content_1'] == ''
        attempts = sql('SELECT title_2 FROM resources_log' + where +
                       " AND content_1='/* stale edit */' AND operator LIKE '%(!)'")
        assert attempts == [{'title_2': 'retained title'}]
    finally:
        config.write_bytes(previous_config)
