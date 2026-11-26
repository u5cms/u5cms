'use strict';
// Generated, deterministic return references; no browser or credentials.
const assert = require('node:assert/strict');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
if (!process.env.U5_TEST_PHP) throw new Error('U5_TEST_PHP must name the PHP CLI');
let seed = 261126;
function next(maximum) { seed = (Math.imul(seed, 1664525) + 1013904223) >>> 0; return seed % maximum; }
const words = ['plain', 'Grüsse', '&amp;', 'a+b c', 'a&b=c', 'quote"single\'', '50%', 'question?', 'hash#value', '', '日本語'];
const requests = [];
for (let i = 0; i < 256; i++) {
    const query = new URLSearchParams();
    for (let field = 0, count = 1 + next(6); field < count; field++) query.append('field' + next(4), words[next(words.length)]);
    const prefix = ['https://cms.example/index.php', 'index.php', 'a', '/', ''][next(5)];
    let target = prefix + '?' + query;
    if (next(2)) target += '#' + ['section', 'section#detail', 'a?b#c', ''][next(4)];
    requests.push({login:'https://cms.example/loginsave.php?u=' + encodeURIComponent(target), target});
}
const output = execFileSync(process.env.U5_TEST_PHP, [path.join(__dirname, 'login-return-roundtrip.php')], {input:JSON.stringify(requests),encoding:'utf8',env:process.env});
const result = JSON.parse(output);
assert.equal(result.round_trips, 512);
assert.equal(result.denials, 26);
console.log('Generated login corpus: 512 round trips and 26 denials passed (256 deterministic references).');
