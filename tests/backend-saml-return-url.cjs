'use strict';

// Exercise the actual backend SAML button, without a browser, credentials or DB.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');

const root = process.env.U5CMS_SOURCE_ROOT || path.resolve(__dirname, '..');
const file = process.argv[2] || path.join(root, 'u5admin/usercheck.inc.php');
const source = process.env.U5_TEST_PHP
    ? execFileSync(process.env.U5_TEST_PHP, [path.join(__dirname, 'backend-saml-login-fixture.php')], {
        encoding: 'utf8',
        env: { ...process.env, U5CMS_SOURCE_ROOT: root, U5CMS_BACKEND_SOURCE: file },
    })
    : fs.readFileSync(file, 'utf8');
const match = source.match(/id="samllogin" onclick="([^"]+)"/);
assert.ok(match, 'Backend SAML login button is missing');
// Source-only runs decode the PHP string; PHP-enabled runs use the actual HTML.
const handler = process.env.U5_TEST_PHP ? match[1] : match[1].replace(/\\(['\\])/g, '$1');

const targets = [
    'https://cms.example/u5admin/metai.php?typ=i&name=example5',
    'https://cms.example/u5admin/metai.php?name=example5&typ=i',
    'https://cms.example/u5admin/metai.php?typ=a&name=animation',
    'https://cms.example/u5admin/metad.php?typ=d&name=document&l=fr',
    'https://cms.example/u5admin/metav.php?typ=v&name=video&l=en',
    'https://cms.example/u5admin/localize.php?name=example&typ=p',
    'https://cms.example/u5admin/formdataedit.php?n=!survey&id=27&l=de',
    'https://cms.example/u5admin/metai.php?typ=i&name=example5#caption-2',
    'https://cms.example/u5admin/metai.php?typ=i&name=a%26b&next=%2Findex.php%3Fc%3Dfaq%26l%3Dde',
    'https://cms.example/u5admin/metai.php?typ=i&name=gr%C3%BCn&caption=a+b%20c',
    'https://cms.example/u5admin/metai.php?typ=i&name=example&literal=%26amp%3B&named=%26quot%3B',
    'https://cms.example/u5admin/metai.php?typ=i&name=example&literal=AT&amp;T&quot;quoted',
    'https://cms.example/c/u5admin/metai.php?typ=i&name=example&empty=',
    'http://cms.example:8080/u5admin/metai.php?typ=i&name=example',
    'https://cms.example/u5admin/',
    'https://cms.example/u5admin/index.php',
];

const requests = [];
for (const target of targets) {
    const top = { location: { href: '' } };
    vm.runInNewContext(handler, { top, location: { href: target } }, { timeout: 1000 });
    const login = new URL(top.location.href, target);
    assert.equal(login.origin, new URL(target).origin, 'Login must stay on the same origin');
    assert.ok(login.pathname.endsWith('/loginsave.php'), 'Login endpoint changed');
    assert.deepEqual([...login.searchParams.keys()], ['u'], 'Target parameters escaped into the login query');
    assert.equal(login.searchParams.get('u'), target, 'Return target was truncated or decoded more than once');
    assert.equal(login.hash, '', 'Target fragment escaped into the login URL');
    requests.push({ login: login.href, target });
}

if (process.env.U5_TEST_PHP) {
    const output = execFileSync(process.env.U5_TEST_PHP, [path.join(__dirname, 'login-return-roundtrip.php')], {
        encoding: 'utf8', input: JSON.stringify(requests), env: { ...process.env, U5CMS_SOURCE_ROOT: root },
    });
    const result = JSON.parse(output);
    assert.equal(result.round_trips, targets.length * 2);
    assert.equal(result.denials, 26);
    console.log(`Full SAML chain: ${result.round_trips} round trips and ${result.denials} redirect denials passed.`);
}

// The metadata guard must still reject a resource/type mismatch.
const guard = fs.readFileSync(path.join(root, 'u5admin/checktyp.inc.php'), 'utf8');
assert.match(guard, /if\(\$row_a\['typ'\]!==?\$_GET\['typ'\]\)die\(/);
console.log(`Backend SAML return URLs: ${targets.length} round trips passed (${process.env.U5_TEST_PHP ? 'rendered PHP' : 'source handler'}); metadata type guard retained.`);
