# Accounts, permissions and SELF records

[Documentation home](README.md)

## Summary

Start by identifying what access you want to grant. Signing in identifies an account; backend administration, protected-page access and ownership of a submitted form record are separate permission decisions. A trusted browser keeps the account's existing permissions rather than adding another permission level.

**Audience:** installation administrators and form authors. **Version:** SELF domain scope from 12.9.5.3; exercised on 12.10.0. **Scope:** record access in one installation.

## Choose the relevant permission

| Requirement | Where to look |
| --- | --- |
| Edit website content | Existing backend account and object permissions. |
| Use privileged administration functions | Higher administration rights, called HIADRI, and the installation's corresponding settings. |
| Read an intranet or closed-group page | That page's existing access rules and membership. |
| Work with a person's own submitted records | The form's SELF controls and ownership scope. |
| Share submitted records within an organisation | Explicit domain scope, following the procedure below. |

Use the account-management area for backend accounts and the appropriate page/group mechanism for protected content. Giving an account a trusted-browser allowance does not create either type of permission.

## Personal ownership: the default

```php
$u5onlyminescope = 'user';
$u5onlymineadmindomains = array();
```

SELF checks compare the complete authenticated CMS identifier. An absent or unrecognised scope also selects personal ownership. A colleague with the same email domain does not inherit the person's records in this mode.

## Share within an organisation

```php
$u5onlyminescope = 'domain';
$u5onlymineadmindomains = array();
```

Use this only where the admission process verifies the email identity. The CMS compares the complete normalised domain after `@`; subdomains remain separate. Internationalised domains use their DNS A-label form under UTS #46. Original record owners are retained, so changing the access scope does not rewrite the stored ownership history.

The setting applies to SELF listing and add/edit/delete controls. Target form, record state and authenticated identity are still checked. A displayed hash or a guessed record ID does not grant access.

## An exercise with four records

Create a disposable fixture with the following active records in one form:

| Record | Stored owner |
| --- | --- |
| 1 | `alice@office.example` |
| 2 | `bob@office.example` |
| 3 | `charlie@sub.office.example` |
| 4 | `dana@other.example` |

The documentation's independent SQL exercise observed:

| Authenticated identity and configuration | SELF records selected |
| --- | --- |
| Alice, personal scope | 1 |
| Alice, domain scope | 1, 2 |
| Charlie, domain scope | 3 |
| `administrator@central.example`, no central allowance | None |
| Same identity, `central.example` explicitly allowed | 1, 2, 3, 4 |
| `administrator@sub.central.example`, only `central.example` allowed | None |
| Alice after returning to personal scope | 1 |

All stored owners were unchanged after the exercise. Another form's record remained outside the selected form, and an unverified identity could not use the central allowance. The lesson is that changing scope changes an access decision, while the underlying submission continues to record its original owner.

## Central SELF administration

An installation can deliberately allow specific domains to work across organisations:

```php
$u5onlyminescope = 'domain';
$u5onlymineadmindomains = array('central.example');
```

This is an exact-domain allowance for authenticated identities in domain mode. It does not include subdomains or grant general backend administration rights. Keep the list empty unless that cross-organisation access is intended.

## Before accepting the change

Test a person's own record, a colleague's record, another organisation, a subdomain, a different form and a disallowed record state. Check both the visible list and the actual edit/delete action. A filtered display alone does not establish authorisation.

The [laboratory](labs/README.md) explains how to repeat the nine-case ownership exercise. [SELF technical details](onlymine.md) cover the DAT filter and optional site writer; [forms](forms.md) covers submission and storage behaviour.
