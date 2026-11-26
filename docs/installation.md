# Install a new u5CMS site

[Documentation home](README.md) · [Configuration](configuration.md)

## Summary

A new installation needs application files, one initial database and its own `config.php`. Choose the standard site or the preconfigured conference site before importing the database. For a site that already contains editorial work, follow [Updates, backups and recovery](updates-backups.md). The initial database files contain commands that replace existing tables.

**For** the administrator setting up an empty site. **Version** stable u5CMS 12.10.5. Use files from the same release or development branch throughout.

## Prepare the site

1. Choose the release and a dedicated, empty database. The host needs PHP with MySQLi, MariaDB or MySQL, HTTPS and web-server support for the site's per-directory access rules. Keep the site restricted to the people setting it up until the initial account has been replaced.
2. Deploy the release into the site's web directory, keeping its directory structure and supplied access files. Give the PHP service account write access to the site's `r/` and `fileversions/` directories. These hold generated or uploaded files and their version history.
3. Import **one** of the following files from that release into the empty database.

   | Starting point | Database file |
   | --- | --- |
   | A multilingual website with example content | [u5standard.sql](../u5standard.sql) |
   | A conference site with example submission and review workflows | [u5conference.sql](../u5conference.sql) |

4. Copy [config.php.sample](../config.php.sample) to `config.php` in the CMS directory. Set `$host`, `$username`, `$password` and `$db` to that database's connection details. Set `$mymail` to the site's sender address and choose the [mail transport](email.md) for this installation. Keep this completed file private.

## Open the site and hand it over

1. Open the site's HTTPS address. You should see the example homepage from your chosen database. Open `/u5admin/` beneath the same installation path and sign in with the initial account described in the [installation notes supplied with the release](../___readme_howtoinstall_and_license.txt).
2. In the far-right column choose **A** (Accounts). Invite the first administrator at their own email address and grant **Higher Admin Rights**. In a separate browser profile, sign in to the backend with that account. Open **A** and check that the new administrator has the intended rights and that the initial `Temp` account has disappeared. Complete this handover before inviting other editors.
3. Create [a first page](first-page.md) and open it from the visitor side. Replace the sample content and choose the [homepage and menu](navigation.md) for the site. Retain a [matching database and file backup](updates-backups.md#identify-what-must-travel-together).

| What you see | Where to look |
| --- | --- |
| A hosting placeholder opens at the site's root | Check the selected web directory and whether the server serves an existing static index ahead of `index.php`. |
| The application opens but cannot load its content | Check which database received the import, then compare that name and its account with the settings in the installation's `config.php`. |
| The backend reports that a directory is unwritable | Give the PHP service account the required access to the directory named in the message. Check ownership and the hosting account's permissions. |
