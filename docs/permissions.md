# Accounts, permissions and SELF records

[Documentation home](README.md)

## Summary

Start by identifying what access you want to grant. Signing in identifies an account; backend administration, protected-page access and ownership of a submitted form record are separate permission decisions. A trusted browser keeps the account's existing permissions rather than adding another permission level.

**For** installation administrators and form authors. **Version** SELF domain scope from 12.9.5.3; exercised on 12.10.0. **Scope** record access in one installation.

For a page's availability or search listing, see [Set page visibility](navigation.md#set-page-visibility).

## Choose the relevant permission

| Requirement | Where to look |
| --- | --- |
| Edit website content | Existing backend account and object permissions. |
| Use privileged administration functions | Higher administration rights, called HIADRI, and the installation's corresponding settings. |
| Read an intranet or closed-group page | That page's existing access rules and membership. |
| Work with a person's own submitted records | The form's SELF controls and ownership scope. |
| Share submitted records within an organisation | Explicit domain scope, following the procedure below. |

Use the account-management area for backend accounts and the appropriate page/group mechanism for protected content. Giving an account a trusted-browser allowance does not create either type of permission.

## Give a page reader access without a backend account

For the equipment-list exercise, a contributor needs to open two pages and edit their own records. They do not need permission to edit website source.

1. Open `loanentry` in the page editor. Choose **i → Publicity status** and select **public, no indexing** for the practice form. In the same controls, find **Usernames & passwords for all language versions of this page**.
2. Enter the reader credential in the literal format `?username:password;`, one pair per line. For the exercise's first identity use username `alice@office.example`; choose its password privately. The initial `?`, colon and final semicolon are required delimiters. Use a different private password for each test identity.
3. Save with **s**. Repeat the same intended reader pairs on `myloans`. These page credentials are not backend accounts and are not invitations sent by email. Existing backend accounts retain their administrative access to protected pages.
4. In a separate browser profile, open the visitor URL `index.php?c=loanentry` and sign in with that reader credential. Submit a sample item, return to `myloans`, follow its **Edit** link and save a correction. In a signed-out profile, opening either URL must request login.

A page whose logins field is just `?` requires a valid existing backend account without adding a page-reader pair. A page name beginning with `!` uses the separate intranet membership mechanism under **A**; its editor does not accept these local page-reader pairs. On a federated installation, use its established verified-identity admission process instead of introducing local credentials for this exercise.

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

Use the complete [equipment-list exercise](forms/manage-entries.md#1-create-the-entry-form-and-the-management-list) to create the protected `loanentry` form and `myloans` list on a disposable site. Keep personal scope for the first pass. Set up the four verified test identities below through the site's normal reader-account process. In a separate browser profile for each identity, open `loanentry` and submit one item: **Tripod**, **Projector**, **Screen** and **Lectern**, respectively. Enter **Library** as each collection point.

In **S → special funcs → form data → loanentry → html**, check there are four active records and that their stored owners match the accounts used. The numbers in this table are labels for the exercise, rather than database IDs to type into a form:

| Record | Stored owner |
| --- | --- |
| 1 | `alice@office.example` |
| 2 | `bob@office.example` |
| 3 | `charlie@sub.office.example` |
| 4 | `dana@other.example` |

Open `myloans` in each profile. With personal scope Alice sees Tripod alone. In the practice site's existing `config.php`, change the single `$u5onlyminescope` assignment to `'domain'`, leaving the central allowance empty. Reopen Alice's list: it now shows Tripod and Projector. Open Charlie's list: it shows Screen alone. Return the scope to `'user'` and Alice again sees Tripod alone. The original owner values in **Form data** remain unchanged.

The corresponding independent SQL exercise observed:

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

For a worked example on real pages, build the [personal course list and detail view](dat.md#show-each-contributor-their-own-courses).

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
