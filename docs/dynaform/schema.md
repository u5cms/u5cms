# DynaForm: exact fields, object names and complete shared code

[Step-by-step room-handover tutorial](../dynaform.md) · [All component sources](example/README.md)

## Summary

The tutorial contains a complete small form builder. This reference explains each stored definition field and supplies its full shared library. It also distinguishes the conventions found in existing composition applications from the concrete names of this exercise.

## Definition fields

| Field in `df!edit` | Example | Meaning |
| --- | --- | --- |
| `position_mandatory` | `20` | Numeric display order; ties use definition record ID. |
| `name_mandatory` | `condition_mandatory` | Stable answer key before the composer's `N_` prefix. Unique per form family. |
| `label` | `Condition at handover` | Visible label. |
| `type` | `SELECT` | INPUT, TEXTAREA, SELECT or PROLOGUEONLY. |
| `attributes` | `rows="4" maxlength="1000"` | Trusted control attributes. Keep `name`, `id`, labels and form handling with the composer. |
| `values` | `Ready==ready|Needs attention==attention` | INPUT/TEXTAREA default, or the SELECT label/code pairs. |
| `placeholder` | `For example, Blue room` | Optional hint alongside the persistent label. |
| `prologue` | Introductory CMS text | Trusted content rendered before the control; PROLOGUEONLY has no answer control. |
| `help` | `Tell us about doors, alarms or keys.` | Plain help associated with the control. |
| `epilogue` | Additional CMS text | Trusted content rendered afterwards. |

The configuration field `name_mandatory` stores the question's name. It does not make every generated answer mandatory. In this example, choosing the question key `room_mandatory` produces the answer control `N_room_mandatory`; its suffix requests the native mandatory check. `accessnotes` produces optional `N_accessnotes`.

## Names and records through one complete trip

| Stage | Concrete page or record | What it contains |
| --- | --- | --- |
| Shared configuration source | `df!edit` | The form's reusable source, included by each family's edit wrapper. |
| Alice configures a question | `df!room!edit` | The actual form page used for the submission. |
| Saved definition | `formdata.formname = 'df!room!edit'` | For example name `room_mandatory`, type INPUT, position `10`. |
| Composer reads that definition | `df!form` included in `df!room!rdr` | One input named `N_room_mandatory`. |
| Bob saves Blue room | `formdata.formname = 'df!room!rdr'` | `N_room_mandatory` contains Blue room; `authuser` records Bob's normalised CMS identity. |
| Bob reads his saved handover | `df!room!pub` | Selects Bob's current row and displays its named values as text. |
| Bob revises the handover | `df!room!rdr`, native MF | Loads his eligible response; native storage retains its former version. |

`formdata.id` identifies a stored row. A question name identifies an answer field. The room name is ordinary answer data. These identifiers are not interchangeable.

## Relation to existing component conventions

The inspected composition application uses a shared `s!edit` definition form, family-specific `!edit` wrappers, a shared `s!form` composer and `!rdr` answering pages. An `!rdrr` wrapper leads to a separate history display. Other publication workflows use `!edit` to maintain content and `!pub` to display it. An `!inc` object can be only an inclusion alias to that `!pub` page. Consequently, a suffix must be read with its actual source and callers.

| Existing convention | What to establish before reuse | This tutorial's complete counterpart |
| --- | --- | --- |
| `s!edit` | Fields collected, completion destination and write permissions | `df!edit` plus `df!room!edit` |
| `s!form` | Definition family, control names, language handling and dependencies | `df!form` plus `df!library` |
| `family!rdr` | Generated answering form and response form name | `df!room!rdr` |
| `family!pub` | Which saved content/answers it selects and who may see them | `df!room!pub`, explicitly the current user's read-only handover |
| `family!inc` | Actual included source, possibly just an alias | `df!room!inc`, explicitly the introduction |
| Notes-based ordering and a renumbering helper | Which system column is changed and how authorisation is checked | `position_mandatory` plus deterministic numeric sorting; no separate writer |
| `I_`, `N_`, `O_`, `S_` field prefixes | Which custom output parser consumes boundary markers/display labels | This small reader needs only `N_`; SELECT labels come from stable codes in the current definition set. |
| Custom case identity, history and file wrappers | Their login/key policy and all included components | Personal MF plus native answer history; see the separate upload guide. |

`[_pagename!_]` is a core placeholder: it removes the final `!` segment from the current `c=` page name. For `df!room!edit` it becomes `df!room`. It does not infer permissions or install the named object. `[$$$:df!form]` includes a page's content; `[Open the form:df!room!rdr]` makes a link to a page. Keep these operations distinct.

## Complete shared library

Create the page object **`df!library`** and paste this complete source into its first language field. Its eight companion sources are in the [manifest](example/README.md). These functions use the CMS's existing database connection and identity normalisation; no connection password belongs in a page object.

```text
[h:]
<?php
// Shared by all pages in this exercise. No database writes happen here.
if (!function_exists('u5dfExampleBase')) {
    function u5dfExampleBase() {
        $page = (string) ($_GET['c'] ?? $_GET['n'] ?? '');
        foreach (array('df!room', 'df!kit') as $base) {
            foreach (array('edit', 'design', 'rdr', 'pub', 'inc') as $suffix) {
                if ($page === $base.'!'.$suffix) return $base;
            }
        }
        throw new RuntimeException('Open a named DynaForm example page.');
    }

    function u5dfExampleEscape($value) {
        return htmlspecialchars((string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE, 'WINDOWS-1252', false);
    }

    function u5dfExampleValues($row) {
        $head = explode(';', (string) $row['headcsv']);
        $data = explode(';', (string) $row['datacsv']);
        $values = array();
        foreach ($head as $index => $key) {
            if ($key === '') continue;
            // Each packed cell starts with one CMS marker byte.
            $key = str_replace(',.', ';', substr($key, 1));
            $values[$key] = str_replace(',.', ';',
                substr((string) ($data[$index] ?? ''), 1));
        }
        return $values;
    }

    function u5dfExampleText($text) {
        // First configured site language, then second, then third.
        $parts = array_pad(explode('|||', (string) $text), 3, '');
        return def(trim($parts[0]), trim($parts[1]), trim($parts[2]));
    }

    function u5dfExampleOptions($text) {
        $options = array();
        foreach (explode('|', u5dfExampleText($text)) as $option) {
            $pair = explode('==', $option, 2);
            if (count($pair) !== 2 || trim($pair[0]) === ''
                || !preg_match('/\A[A-Za-z0-9_+.-]+\z/', trim($pair[1]))) {
                throw new RuntimeException('Use Label==code for every SELECT option.');
            }
            $code = trim($pair[1]);
            if (array_key_exists($code, $options)) {
                throw new RuntimeException('Duplicate SELECT code: '.$code);
            }
            $options[$code] = trim($pair[0]);
        }
        return $options;
    }

    function u5dfExampleDefinitions($base) {
        $query = "SELECT id,headcsv,datacsv FROM formdata WHERE formname='"
            .mysql_real_escape_string($base.'!edit')."' AND status=1 ORDER BY id";
        $result = mysql_query($query);
        if ($result === false) throw new RuntimeException('Cannot read field definitions.');
        $definitions = array();
        $names = array();
        while ($row = mysql_fetch_array($result)) {
            $field = u5dfExampleValues($row);
            $name = trim((string) ($field['name_mandatory'] ?? ''));
            if (!preg_match('/\A[a-z][a-z0-9_]*\z/', $name)
                || isset($names[$name])) {
                throw new RuntimeException('Invalid or duplicate field name: '.$name);
            }
            $type = (string) ($field['type'] ?? '');
            if (!in_array($type, array('INPUT', 'TEXTAREA', 'SELECT', 'PROLOGUEONLY'), true)) {
                throw new RuntimeException('Unknown field type: '.$type);
            }
            $position = (string) ($field['position_mandatory'] ?? '');
            if (!ctype_digit($position)) {
                throw new RuntimeException('Give every field a whole-number position.');
            }
            $names[$name] = true;
            $field['key'] = $name;
            $field['rowid'] = (int) $row['id'];
            $field['position'] = (int) $position;
            if ($type === 'SELECT') u5dfExampleOptions($field['values'] ?? '');
            $definitions[] = $field;
        }
        usort($definitions, static function ($left, $right) {
            return ($left['position'] <=> $right['position'])
                ?: ($left['rowid'] <=> $right['rowid']);
        });
        return $definitions;
    }

    function u5dfExampleAnswer($base) {
        $user = u5flatidnlower((string) ($_SERVER['PHP_AUTH_USER'] ?? ''));
        if ($user === '') return array();
        $result = mysql_query("SELECT headcsv,datacsv FROM formdata WHERE formname='"
            .mysql_real_escape_string($base.'!rdr')."' AND status=1 AND authuser='"
            .mysql_real_escape_string($user)."' ORDER BY id DESC LIMIT 1");
        if ($result === false) throw new RuntimeException('Cannot read the saved answer.');
        $row = mysql_fetch_array($result);
        return is_array($row) ? u5dfExampleValues($row) : array();
    }
}
?>
[:h]
```

The packed `headcsv` and `datacsv` are matched by position after removing each cell's leading CMS marker byte. `,.` represents an escaped semicolon in this legacy storage. They are not ordinary exported CSV and their field names are not separate SQL columns. Use the CMS's normal export for exchanging responses.

The library permits only the fixed `df!room` and `df!kit` families. It does not accept an arbitrary definition source, SQL fragment or account name from the URL. It reads each family's active definitions, validates names/types/options, then sorts the numeric position with record ID as a tie-breaker. A malformed configuration stops composition with a diagnostic so Alice can correct it.

## Languages and stable values

For the first run, use one plain text value per field. Later, separate up to three variants with `|||` in the configured site-language order. On a site configured DE, FR, EN, a label can be `Raum|||Salle|||Room`. Complete the whole dropdown list for one language before the next:

```text
Bereit==ready|Handlungsbedarf==attention|||Prêt==ready|Intervention nécessaire==attention|||Ready==ready|Needs attention==attention
```

The stored codes remain `ready` and `attention` in every language. `|` and `==` are delimiters in this small implementation; do not use them inside a label/code. The third configured language is not automatically English. The native `def()` function supplies its normal fallback when a variant is empty. `attributes` is trusted code and is used verbatim; keep it language-neutral.

## Changing an existing application

The example adds its own `df!` namespace. It does not rename existing `s!form`/`s!edit` objects, rewrite a site's response prefixes, change MF into MFS or replace a private writer. An existing application's names, response keys, ordering fields and upload numbers may be part of its stored-data contract. Inventory them before adapting it.

This teaching reader uses current question definitions. Retain prior definitions when a business process needs the wording which applied to an older response. Native answer history alone does not version the definition set or make a custom reader select it automatically.
