/* PHP/browser normalization and original-text ranges must agree. No browser needed. */
'use strict';
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const { spawnSync } = require('node:child_process');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '..');
const highlight = require(path.join(root, 'js/search-highlight.js'));
const mapping = JSON.parse(fs.readFileSync(path.join(root, 'search-normalization.json'), 'utf8'));
const php = process.env.U5CMS_TEST_PHP || 'php';
const cases = [
    { text: 'Before Wort (Wort Wort) Wort after', query: '"Wort (Wort Wort) Wort"' },
    { text: 'Wort Wort Wort Wort', query: '"Wort (Wort Wort) Wort"' },
    { text: 'Stra\u00dfe and \u1e9e and ss', query: 'strasse ss' },
    { text: '\u0152uvre \u00c6ther \u0141\u00f3d\u017a', query: 'oeuvre aether lodz' },
    { text: 'e\u0301 \u00e9 \u00c9', query: 'e' },
    { text: '\u00bc\u0301', query: '1' },
    { text: '\u00df\u0301', query: 's' },
    { text: 'a\u00a0\u202f b a-b a\u2014b', query: '"a b"' },
    { text: 'ab\u00adc\u200bd', query: 'abcd' },
    { text: '\ud83d\ude00 then \ud83d\ude80', query: '\ud83d\ude80' },
    { text: '\u041f\u0440\u0438\u0432\u0435\u0442', query: '\u043f\u0440\u0438\u0432\u0435\u0442' },
    { text: 'A[B](C).+?^$|\\%_', query: '"A[B](C).+?^$|\\%_"' },
    { text: 'say "hello"', query: '"say \\"hello\\""' },
    { text: 'foobar and foo', query: 'foo foobar' },
    { text: '&#128512; and \ud83d\ude00', query: '"&#128512;"' },
    { text: '\u30d1 and \u30cf', query: '\u30d1' }
];
const input = { cases, tableText: Object.keys(mapping.map).join('|') };
if (process.argv[2] === '--write-input') {
    fs.writeFileSync(process.argv[3], JSON.stringify(input), 'utf8');
    process.exit(0);
}
let expected;
if (process.argv[2] === '--php-results') {
    expected = JSON.parse(fs.readFileSync(process.argv[3], 'utf8'));
} else {
    const temporary = fs.mkdtempSync(path.join(os.tmpdir(), 'u5-search-parity-'));
    const inputPath = path.join(temporary, 'input.json');
    fs.writeFileSync(inputPath, JSON.stringify(input), 'utf8');
    let result;
    try {
        result = spawnSync(php, [path.join(root, 'tests/search-core-regression.php'), '--parity-input', inputPath], { encoding: 'utf8', maxBuffer: 4 * 1024 * 1024, timeout: 10000 });
    } finally {
        fs.rmSync(temporary, { recursive: true, force: true });
    }
    if (result.error) throw result.error;
    assert.equal(result.status, 0, result.stderr || result.stdout);
    expected = JSON.parse(result.stdout);
}
cases.forEach((fixture, index) => {
    const server = expected.cases[index];
    assert.equal(highlight.normalizeText(fixture.text, mapping), server.normalized, 'normalization fixture ' + index);
    const client = highlight.findRanges(highlight.foldWithOffsets(fixture.text, mapping), server.terms);
    assert.deepEqual(client.ranges.map(range => fixture.text.slice(range.start, range.end)), server.pieces, 'original-text ranges fixture ' + index);
});
assert.equal(highlight.normalizeText(input.tableText, mapping), expected.tableText, 'complete Unicode fold table');
console.log('Search PHP/JavaScript parity: ' + cases.length + ' fixtures and all ' + Object.keys(mapping.map).length + ' folds passed.');
