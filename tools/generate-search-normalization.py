#!/usr/bin/env python3
"""Generate the one character-fold table shared by PHP and the browser.

Run from any directory with Python 3. The committed JSON is the runtime input;
neither PHP nor JavaScript needs ICU, Python or a Unicode-normalization extension.
"""

import json
from pathlib import Path
import unicodedata


ROOT = Path(__file__).resolve().parents[1]
EXPLICIT = {
    "\u00e6": "ae", "\u0153": "oe", "\u00f8": "o", "\u0142": "l",
    "\u0111": "d", "\u00f0": "d", "\u00fe": "th", "\u0131": "i",
}
DASHES = set("-\u058a\u05be\u1400\u1806\u2010\u2011\u2012\u2013\u2014\u2015\u2212\u2e17\u2e1a\u2e3a\u2e3b\u2e40\u301c\u3030\u30a0\ufe31\ufe32\ufe58\ufe63\uff0d")


def ignored(character):
    cp = ord(character)
    # Remove diacritics without deleting vowel signs or Japanese voicing marks.
    return (
        unicodedata.category(character) == "Cf"
        or 0x0300 <= cp <= 0x036F
        or 0x1AB0 <= cp <= 0x1AFF
        or 0x1DC0 <= cp <= 0x1DFF
        or 0x20D0 <= cp <= 0x20FF
        or 0xFE20 <= cp <= 0xFE2F
        or 0xFE00 <= cp <= 0xFE0F
        or 0xE0100 <= cp <= 0xE01EF
    )


def fold(character):
    if character in DASHES or character.isspace():
        return " "
    if ignored(character):
        return ""
    # Hangul is already searchable as composed text; its decomposition would
    # account for most of the table and is not needed for accent folding.
    if 0xAC00 <= ord(character) <= 0xD7A3:
        return character
    decomposed = unicodedata.normalize("NFKD", character.casefold())
    result = []
    for piece in decomposed:
        if ignored(piece):
            continue
        if piece in DASHES or piece.isspace():
            result.append(" ")
        else:
            result.append(EXPLICIT.get(piece, piece))
    return "".join(result)


mapping = {}
for codepoint in range(0x110000):
    if 0xD800 <= codepoint <= 0xDFFF:
        continue
    character = chr(codepoint)
    result = fold(character)
    if result != character:
        mapping[character] = result

# Values are final: a second mapping pass must not change them. Some compatibility
# decompositions introduce upper-case characters, so resolve those as well.
for character, result in list(mapping.items()):
    for unused in range(4):
        final = "".join(mapping.get(piece, piece) for piece in result)
        if final == result:
            break
        result = final
    mapping[character] = result

output = {
    "version": 1,
    "unicodeVersion": unicodedata.unidata_version,
    "description": "Map each Unicode codepoint once, then collapse ASCII space runs. Values are final folds. Preserve source offsets before folding.",
    "map": mapping,
}
destination = ROOT / "search-normalization.json"
destination.write_text(json.dumps(output, ensure_ascii=False, separators=(",", ":")) + "\n", encoding="utf-8")
print("Wrote %d character folds (%d bytes) to %s" % (len(mapping), destination.stat().st_size, destination))
