HOW TO INSTALL THE u5CMS (license see bottom of this page)
              (How to update see bottom of page)

 1.
 You need a web server offering MySQL and PHP and further supporting .htaccess.
 
 2. 
 Extract the u5cms.zip in a way that the folder structure will be recreated.

 3.
 Create a database and import u5standard.sql OR import u5conference.sql if you want 
 a preconfigured conference system with review process, PayPal a.s.o.
 
 4.
 In config.php, adjust the MySQL connection
 parameters in config.php to the ones needed in your environment (ask your provider). Also adjust
 your e-mail address in this file.

 5.
 Transfer (e. g. by FTP) all files into a surfable directory (e. g. www, htdocs, public_html)
 of your webhosting account. Do not forget to remove index.html and/or index.htm if these
 files are existing there!
 
 6.
 Surf to the dummy homepage on http://yoururl/ (or http://yoururl/subdir if you 
 installed the CMS not top of your surfable directory).

 7.
 To access the u5CMS backend, doubleclick the upper left corner of the dummy homepage according to the instructions 
 in the video http://www.yuba.ch/u5cms. Alternatively you can use the URL http://.../u5admin/ to access the backend.

 8.
 Your first login to the backend is 

 username: Temp 
 password: FirstPassword7

 9. 
 If you get some alerts after the login saying that the server is not allowed to write files, you have to chmod 
 the subdirectories 'fileversions' and 'r' RECURSIVELY to 777 for that your server is allowed to write there.
 Maybe you need to chmod other directories to executable and other files to readable.

 10.
 Go to the account administration (You'll find it in the u5CMS backend on 'A' of the 
 PIDVESA-radiobuttons). Invite yourself as new backend user and DO NOT FORGET 
 to give yourself access to all restricted areas (switch the Higher Admin Rights radio to 'y')!

 11.
 Close all browser windows or open a new browser and surf again to the dummy homepage and
 login to the CMS again, this time with your new personal login (you got it in an alert window and may be by e-mail).

 12.
 If you want to change the look of the dummy homepage, alter the CSS's and the htmltemplate
 which you'll find in the CMS on 'S' of the PIDVESA-radiobuttons. 
 There click the 'Code'-button. ATTENTION: You are only allowed to make changes there (in
 the CMS). From there files are exported to the r-folder; never touch the files directly!



TROUBLE SHOOTING
 
 a)
 If visible backslashes are added to apostrophes after saving text, set $quotehandling='on' in config.php

 b) 
 Logins might not work if you have $usesessioninsteadofbasicauth='no'; in config.inc.php. If you really want this, and PHP on your server is running as CGI and not as Module, http basic auth needs the following help:
  1. Set $autoresolvefastcgiproblems='yes' in config.php
  2. Activate the file fastcgi.htaccess in the cms's top directory by renaming it to .htaccess (the first character MUST be a period!)
  3. Check the variable $basicauthvarname in config.php




HOW TO UPDATE AN EXISTING U5CMS INSTALLATION

1. On your server, go to the files of your old (current) u5CMS installation and copy config.php to config.old.php
2. Download the ZIP file https://yuba.ch/u5cmszip/u5cmszip_en.zip, extract it in a way that the folder structure will be recreated
3. Delete favicon.ico in the extracted files
4. Further, delete all contents in folder r EXCEPT runonce.php
5. Transfer the remaining files and folders from the ZIP file over the existing installation (FTP upload)
6. Now, on your server, copy the database connection parameters $host $username $password $db from config.old.php to config.php
7. If there are other dedicated settings in config.old.php, set the same in config.php (compare parameter by parameter)
8. You must now log in to the backend of your (now updated) u5CMS installation via https://urltoyouru5cmsfrontend/u5admin for that the database will be updated too

TROUBLE SHOOTING
A) If the layout does not look right after having done step 8, go to https://urltoyouru5cmsfrontend/u5admin and open a css-object (e. g. csslayout) in PIDVESA's S special function's code (htmltemplate & css) section and safe it!
B) If after A) the layout still does not look richt, change the doctype <!DOCTYPE html> to the old one
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">




COPYRIGHT AND LICENSE

    u5CMS Content Management System & Conference Management
    Copyright (C) 2010-2024 Stefan P. Minder

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
