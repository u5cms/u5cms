# Trusted Device: working with an AI agent

[Documentation home](../README.md) · [Trusted Device](../trusted-browsers.md)

## Summary

Register the browser profile the authorised agent will actually use, after completing the ordinary CMS login. Give the agent a clear task, scope and publication rules. The agent operates with the registered account's existing permissions. Continued access depends on that profile retaining its cookies; replacing the profile requires another login and registration.

Trusted Device can let an authorised agent return to u5CMS in a deliberately registered browser profile. It uses the same browser mechanism as human operation. It does not provide an agent API, create a service account or give the agent additional permissions.

## Prepare the profile

1. The installation administrator enables the feature for an existing CMS account and assigns a browser allowance.
2. Open the browser profile that the agent will actually use. A registration in your desktop browser does not register a separate cloud browser.
3. Complete the normal CMS login in that profile, including any identity-provider interaction that requires you.
4. Open the trusted-browser page, verify the account and register the profile.
5. Give the agent the CMS address, the permitted task, the publication rules and the expected completion report.

People working independently should normally use their own CMS accounts. Several profiles registered to one account all have that account's permissions and do not provide distinct CMS identities for their operators.

## Example: a constrained content-editing task

After the administrator has configured the account and you have registered the intended browser, you can give the agent a task such as:

```text
Work in https://cms.example.org/u5admin/ using the browser profile
already registered for alice@example.org.

Task: correct spelling and grammar on the page named contact, in its
English version only. Preserve the meaning, links, object names,
formatting commands, forms and other language versions.

Before editing, confirm the current CMS host, signed-in account,
page name and language. Read the existing page and the relevant
u5CMS formatting help. Use the normal editor and preview.

You may save the corrected English text. Report the changes and
check the rendered page afterwards. Do not change permissions,
accounts, configuration or other pages.

If the browser requires a fresh login, ask me to complete it in
this profile. If a trust registration or account is unexpected,
stop and report what is shown.

Keep this profile registered for my next task. After saving and
checking the page, close the tab without using CMS logout.
```

Replace the host, account and page with your own details. The instruction authorises the stated content changes; the browser's technical ability to access other areas does not expand that task. If the work includes publishing, moving or deleting content, specify that scope explicitly.

## What the agent should check

| Situation | Response |
| --- | --- |
| Expected account and page are available | Perform the authorised task through the normal CMS interface. |
| Normal login is required | Ask the account holder to complete it in the same intended profile. |
| Trusted-browser page returns 404 | Have the installation administrator check configuration and account eligibility. |
| Browser allowance is full | Ask the account holder to identify an obsolete registration for revocation. |
| Cloud browser has been replaced | Complete a normal login and register the replacement under the account's allowance. |
| Task is complete | Report the actual changes and verification; retain or revoke the profile as instructed. |

The agent should not copy authentication cookies, request configuration secrets, add a login bypass, weaken authentication checks or invent a helper endpoint. Use the existing management page and the administrator's configuration process.

## Persistence and identity limits

u5CMS recognises the secret held by the registered profile. It cannot determine whether the browser is currently controlled by ChatGPT, another agent or a person. Possession of the profile is the access boundary.

A new conversation does not guarantee a new browser profile, and keeping a conversation open does not guarantee that a cloud provider will preserve one. Schedule or conversation instructions alone cannot guarantee uninterrupted authenticated access. After profile loss, use normal login and registration again; revoke the stale place if necessary.

## When the work ends

For continuing work, preserve the dedicated profile and its cookies. To withdraw the agent's persistent access, use **Revoke** for that registration or perform CMS logout in that profile. Consider existing ordinary sessions separately if access must end immediately. Do not distribute or export the profile as a way to transfer authorisation.
