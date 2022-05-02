<?php
$sql_a="SET storage_engine=MYISAM;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE accounts ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE formdata ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE inserts ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE intranetmembers ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE intranetmembers_log ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE intranetsalt ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE languages ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE loginattempts ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE loginglobals ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE mailing ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE mailingcron ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE resources ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE resources_log ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE sizes ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE titlefixum ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE trxlog ENGINE = MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);

$sql_a="SELECT content_d FROM resources WHERE deleted!=1 AND name='htmltemplate'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);
$c=explode('<body',$row_a['content_d']);
$c=$c[1];
$head='<!DOCTYPE html>
{{{meta}}}
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
<link rel="stylesheet" href="r/cssbase.css" type="text/css" />
<link href="favicon.ico" rel="shortcut icon" />
<link rel="stylesheet" href="js/jquery.fancybox.min.css" />
<script src="js/jquery-3.3.1.min.js"></script>
<script src="js/jquery.fancybox.min.js"></script>
<script src="js/u5_scripts.js" type="text/javascript"></script>
<script src="r/jsmobilespecific.css"></script>
</head>
<body';
$c=$head.$c;

$sql_a="UPDATE resources SET content_d='".mysql_real_escape_string($c)."' WHERE deleted!=1 AND name='htmltemplate' AND content_d NOT LIKE '%js/jquery.fancybox.min.js%'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
file_put_contents('../fileversions/'.sha1($c).'.updatedone',file_get_contents('../r/runonce.php'));
include('../u5admin/putcss.inc.php');

$sql_a="UPDATE sizes SET smallimgL_h=50 WHERE smallimgL_h<1";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE sizes SET zoomedimg_w=1000, zoomedimg_h=1000";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE inserts SET target1='<iframe style=\"display:inline\" width=\"100%\" height=\"20\" frameborder=\"0\" scrolling=\"no\" src=\"authuser.php?t=', target2='&c=[_pagename_]\"></iframe>' WHERE source1='[au:]'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="CREATE TABLE IF NOT EXISTS `mailingcron` (
`id` int(11),
  `mailingid` int(11) DEFAULT '0',
  `nextmail` int(11) DEFAULT '0',
  `sqla` mediumtext,
  `numa` int(11) DEFAULT '0',
  `lastcall` int(11) DEFAULT '0',
  `done` tinyint(4) DEFAULT '0'
) ENGINE=MyISAM, DEFAULT CHARSET=utf8  DEFAULT CHARSET=utf8";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="CREATE TABLE IF NOT EXISTS `loginglobals` (
  `logintitle_d` varchar(255)  DEFAULT 'Login',
  `logintitle_e` varchar(255)  DEFAULT 'Login',
  `logintitle_f` varchar(255)  DEFAULT 'Login',
  `loginintro_d` text ,
  `loginintro_e` text ,
  `loginintro_f` text ,
  `username_d` varchar(255)  DEFAULT 'Username',
  `username_e` varchar(255)  DEFAULT 'Username',
  `username_f` varchar(255)  DEFAULT 'Username',
  `password_d` varchar(255)  DEFAULT 'Password',
  `password_e` varchar(255)  DEFAULT 'Password',
  `password_f` varchar(255)  DEFAULT 'Password',
  `loginbutton_d` varchar(255)  DEFAULT 'OK',
  `loginbutton_e` varchar(255)  DEFAULT 'OK',
  `loginbutton_f` varchar(255)  DEFAULT 'OK',
  `loginoutro_d` text ,
  `loginoutro_e` text ,
  `loginoutro_f` text ,
  `logout_d` varchar(255)  DEFAULT 'logout',
  `logout_e` varchar(255)  DEFAULT 'logout',
  `logout_f` varchar(255)  DEFAULT 'logout'
) ENGINE=MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="SELECT * FROM `loginglobals`";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);

if($num_a!=1) {
$sql_a="DELETE FROM `loginglobals`";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="INSERT INTO `loginglobals` (`logintitle_d`, `logintitle_e`, `logintitle_f`, `loginintro_d`, `loginintro_e`, `loginintro_f`, `username_d`, `username_e`, `username_f`, `password_d`, `password_e`, `password_f`, `loginbutton_d`, `loginbutton_e`, `loginbutton_f`, `loginoutro_d`, `loginoutro_e`, `loginoutro_f`, `logout_d`, `logout_e`, `logout_f`, `wait_d`, `wait_e`, `wait_f`) VALUES
('Login', 'Login', 'Login', '', '', '', 'Username', 'Username', 'Username', 'Password', 'Password', 'Password', 'log in', 'log in', 'log in', '', '', '', 'log out', 'log out', 'log out', 'Too many login attempts. Try again in', 'Too many login attempts. Try again in', 'Too many login attempts. Try again in');
";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$sql_a="ALTER TABLE `mailing` ADD `mailtested` TINYINT DEFAULT '0'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="INSERT INTO `resources` (`name`, `content_d`, `content_e`, `content_f`, `title_d`, `title_e`, `title_f`, `desc_d`, `desc_e`, `desc_f`, `key_d`, `key_e`, `key_f`, `logins`, `hidden`, `operator`, `ip`, `lastmut`, `deleted`, `typ`, `ishomepage`, `search_d`, `search_e`, `search_f`) VALUES
('cssauthuser', '@import url(//fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic&subset=latin,cyrillic,latin-ext,vietnamese,greek,greek-ext,cyrillic-ext);\r\n\r\n.authuser_welcomemessage {\r\nfont-size:90%;\r\nfont-family:Arimo;\r\n}\r\n\r\n.authuser_username {\r\nfont-size:90%;\r\nfont-family:Arimo;\r\n}\r\n\r\n.authuser_logoutbutton {\r\nfont-size:80%;\r\nbackground:lightyellow;\r\n}\r\n\r\n', '', '', '', '', '', '', '', '', '', '', '', '', 0, 'Temp', '::1', 1469085797, 0, 'c', 0, ' @import url(//fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic&subset=latin,cyrillic,latin-ext,vietnamese,greek,greek-ext,cyrillic-ext);  .authuser_welcomemessage {  font-size:90%;  font-family:Arimo;  }  .authuser_username {  font-size:90%;  font-family:Arimo;  }  .authuser_logoutbutton {  font-size:80%;  background:lightyellow;  }', ' @import url(//fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic&subset=latin,cyrillic,latin-ext,vietnamese,greek,greek-ext,cyrillic-ext);  .authuser_welcomemessage {  font-size:90%;  font-family:Arimo;  }  .authuser_username {  font-size:90%;  font-family:Arimo;  }  .authuser_logoutbutton {  font-size:80%;  background:lightyellow;  }', ' @import url(//fonts.googleapis.com/css?family=Arimo:400,700,400italic,700italic&subset=latin,cyrillic,latin-ext,vietnamese,greek,greek-ext,cyrillic-ext);  .authuser_welcomemessage {  font-size:90%;  font-family:Arimo;  }  .authuser_username {  font-size:90%;  font-family:Arimo;  }  .authuser_logoutbutton {  font-size:80%;  background:lightyellow;  }');";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `mailing` CHANGE `mailsubject` `mailsubject` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginglobals` ADD `wait_d` VARCHAR(255)  DEFAULT 'Too many login attempts. Try again in' AFTER `logout_f`, ADD `wait_e` VARCHAR(255)  DEFAULT 'Too many login attempts. Try again in' AFTER `wait_d`, ADD `wait_f` VARCHAR(255)  DEFAULT 'Too many login attempts. Try again in' AFTER `wait_e`;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="CREATE TABLE IF NOT EXISTS `loginattempts` (
  `username` varchar(255) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL
) ENGINE=MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginattempts` ADD `mailed` INT DEFAULT '0' ;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginattempts`
  ADD KEY `username` (`username`),
  ADD KEY `mailed` (`mailed`);";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginattempts`
  ADD KEY `timestamp` (`timestamp`);";

$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$search='@import url("../plugins/overlay/jquery.fancybox.css");';
$sql_a="UPDATE `resources` SET content_d=REPLACE(content_d,'$search','')";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$search="if (document.cookie.indexOf(\'u5cmsfrcthsvw=desktop\') > -1) document.getElementById(\'body\').innerHTML +=";
$replace="if (document.cookie.indexOf(\'u5cmsfrcthsvw=desktop\') > -1) document.getElementById(\'footer\').innerHTML +=";
$sql_a="UPDATE `resources` SET content_d=REPLACE(content_d,'$search','$replace')";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$search='r/cssbase.css?';
$replace='r/cssbase.css" "?';
$sql_a="UPDATE `resources` SET content_d=REPLACE(content_d,'$search','$replace')";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="CREATE TABLE IF NOT EXISTS `mailing` (
`id` int(11) NOT NULL,
  `mailfrom` varchar(255) DEFAULT NULL,
  `mailto` mediumtext,
  `mailcc` mediumtext,
  `mailbcc` mediumtext,
  `mailsubject` varchar(70) DEFAULT NULL,
  `mailtext` mediumtext,
  `mailsaved` int(11) DEFAULT NULL,
  `mailsavedop` varchar(255) DEFAULT NULL,
  `mailsent` int(11) DEFAULT NULL,
  `mailsentop` varchar(255) DEFAULT NULL,
  `maildeleted` tinyint(4) DEFAULT NULL,
  `mailsentto` mediumtext,
  `mailsentts` mediumtext
) ENGINE=MyISAM, DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `mailing` ADD PRIMARY KEY(`id`);";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `mailing`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `mailingcron`
 ADD PRIMARY KEY (`id`);";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `mailingcron`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `resources` CHANGE `logins` `logins` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `resources_log` CHANGE `logins` `logins` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_d=REPLACE(content_d,'window.onload = function() {','function u5mkmobile() {') WHERE name='jsmobilespecific';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_d=REPLACE(content_d,'<script>if(typeof u5mkmobile===\"function\")u5mkmobile();</script>\r\n','') WHERE name='htmltemplate';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_d=REPLACE(content_d,'<script>if(typeof u5mkmobile===\"function\")u5mkmobile();</script>','') WHERE name='htmltemplate';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_d=REPLACE(content_d,'u5mkmobile()','//uXXX5mkmobile()DUPLICATE LINE REMOVE THIS LINE!!!') WHERE name='htmltemplate';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_d=REPLACE(content_d,'</body>','<script>if(typeof u5mkmobile===\"function\")u5mkmobile();</script>\r\n</body>') WHERE name='htmltemplate';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="DELETE FROM `inserts` WHERE source1='[mfs]'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="INSERT INTO `inserts` (`cat`, `human`, `source1`, `target1`, `source2`, `target2`, `betweensources`, `betweentargets`, `insstring`, `ischaracter`, `isblockelement`) VALUES
('X', '', '[mfs]', '<input type=\"hidden\" name=\"ed2cu\" value=\"on\" /><iframe name=\"ifrmonofillshared\" width=\"0\" height=\"0\" style=\"visibility:hidden\"></iframe><script>locationhref=location.href;if(locationhref.indexOf(''c='')<0) locationhref+=''&c=MF_NOT_ALLOWED_ON_YOUR_MAIN_PAGE'';if(locationhref.indexOf(''id='')<0) locationhref+=''&id=0'';ifrmonofillshared.location.href=''monofillshared.php?n=''+locationhref.split(''c='')[1].split(''&'')[0]+''&id=''+locationhref.split(''id='')[1].split(''&'')[0];</script>', '', '', '', '', '', 0, 0);";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';


$sql_a="SELECT * FROM `inserts` WHERE source1 LIKE '".mysql_real_escape_string('[&gt;:]')."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if($num_a==0) {
$sql_a="INSERT INTO `inserts` (`cat`, `human`, `source1`, `target1`, `source2`, `target2`, `betweensources`, `betweentargets`, `insstring`, `ischaracter`, `isblockelement`) VALUES ('BLOCKFORMATS', '<span title=\"smaller font &amp; line\">&gt;:</span>', '[&gt;:]', '<div class=\"smallerblock\">', '[-]', '</div>', '', '', '[>:]writehere[-]', 0, 1)";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$sql_a="SELECT * FROM `inserts` WHERE source1 LIKE '".mysql_real_escape_string('[&lt;:]')."'";;
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if($num_a==0) {
$sql_a="INSERT INTO `inserts` (`cat`, `human`, `source1`, `target1`, `source2`, `target2`, `betweensources`, `betweentargets`, `insstring`, `ischaracter`, `isblockelement`) VALUES ('BLOCKFORMATS', '<span title=\"larger font &amp; line\">&lt;:</span>', '[&lt;:]', '<div class=\"largerblock\">', '[-]', '</div>', '', '', '[<:]writehere[-]', 0, 1)";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$sql_a="SELECT * FROM `resources` WHERE content_d LIKE '%smallerblock%' AND name='cssstyle'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if($num_a==0) {

$sql_a="UPDATE `resources` SET content_d=CONCAT(content_d,'
.largerblock {
	font-size: 130%;
}
.smallerblock {
	font-size: 70%;
	line-height: 130%;
}
') WHERE name='cssstyle'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$sql_a="ALTER TABLE `sizes` ADD `tosquare` TINYINT NULL DEFAULT '1' AFTER `scrollingalbum_w`;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE `sizes` ADD `cropedge` INT NULL DEFAULT '0' AFTER `tosquare`;";
$result_a=mysql_query($sql_a);

// 2022-05-27: update navigation CSS for navigaton fix
$sql_a=" UPDATE `resources` SET
  `content_d` = '/*\r\nThis CSS formats the navigation if you hav NOT A split navigation.\r\n\r\nIf you want a split navigation, you have to\r\n\r\n1: In PIDVESA''s S, htmltemplate, switch the #navTop on\r\n\r\n  <div id=\"navigationtop\">\r\n    <div id=\"navTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n  </div>\r\n\r\nby removing <!-- and -->\r\n\r\n2: Also in htmltemplate, change\r\n\r\n<div id=\"navLeft\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\nto\r\n\r\n<div id=\"navLeftSubTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\n*/\r\n\r\n\r\n/*NAVLEFT*/\r\n#navLeft {\r\n	padding-top: 100px;\r\n}\r\n\r\n/* anchor styling */\r\n#navLeft a {\r\n	display: block;\r\n	text-decoration: none;\r\n	color: #3F3F3F;\r\n}\r\n\r\n#navLeft a:hover {\r\n	text-decoration: none;\r\n	color: black;\r\n}\r\n\r\n#navLeft a.activeItem {\r\n	color: #e63320;\r\n	font-weight: bolder;\r\n}\r\n\r\n/* list stylings */\r\n#navLeft ul {\r\n    margin: 0;\r\n    padding: 0;\r\n}\r\n\r\n#navLeft ul ul {\r\n    margin-left: 1.5em;\r\n}\r\n\r\n#navLeft li {\r\n	list-style-type: none;\r\n	margin: 0;\r\n	margin-left: 0;\r\n	border-bottom: 1px solid #ddd;\r\n    line-height: 2em;\r\n}\r\n\r\n#navLeft li li {\r\n	font-size: 0.9em;\r\n    border-bottom: none;\r\n}\r\n\r\n#navLeft li.active {\r\n	color: #3F3F3F;\r\n	background-color: #f9f9f9;\r\n}\r\n\r\n#navLeft li.active>ul {\r\n 	padding: 0.1em 0 0.3em 0;\r\n}\r\n',
  `content_e` = '', `content_f` = '', `title_d` = '', `title_e` = '', `title_f` = '', `desc_d` = '', `desc_e` = '', `desc_f` = '', `key_d` = '', `key_e` = '', `key_f` = '', `search_d` = '', `search_e` = '', `search_f` = '',
  `logins` = '', `hidden` = 0, `operator` = 'Temp', `ip` = '::1', `lastmut` = 1653623611, `deleted` = 0, `typ` = 'c', `ishomepage` = 0
  WHERE `resources`.`name` = 'cssnavleft' AND `resources`.`deleted` = 0;";
$result_a=mysql_query($sql_a);

$sql_a=" UPDATE `resources` SET
  `content_d` = '/*\r\nThis CSS formats the second (vertical) level of a split navigation.\r\n\r\nIf you want a split navigation, you have to\r\n\r\n1: In PIDVESA''s S, htmltemplate, switch the #navTop on\r\n\r\n  <div id=\"navigationtop\">\r\n    <div id=\"navTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n  </div>\r\n\r\nby removing <!-- and -->\r\n\r\n2: Also in htmltemplate, change\r\n\r\n<div id=\"navLeft\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\nto\r\n\r\n<div id=\"navLeftSubTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\n*/\r\n\r\n/*NAVLEFTSUBTOP*/\r\n#navLeftSubTop {\r\n	padding-top: 100px;\r\n}\r\n\r\n/* anchor styling */\r\n#navLeftSubTop a {\r\n	display: none;\r\n	text-decoration: none;\r\n	color: #3F3F3F;\r\n}\r\n\r\n/* turn on visibility for second and subsequent levels only */\r\n#navLeftSubTop li li a {\r\n    display: block;\r\n}\r\n\r\n#navLeftSubTop a:hover {\r\n	text-decoration: none;\r\n	color: black;\r\n}\r\n\r\n#navLeftSubTop li a.activeItem {\r\n	color: #e63320;\r\n	font-weight: bolder;\r\n}\r\n\r\n#navLeftSubTop li li a.activeItem {\r\n	color: #ff0000;\r\n	font-weight: bold;\r\n}\r\n\r\n/* list stylings */\r\n#navLeftSubTop .inactive {\r\n    display: none;\r\n	padding-left: 7px;\r\n}\r\n\r\n#navLeftSubTop ul {\r\n    margin: 0;\r\n    padding: 0;\r\n}\r\n\r\n#navLeftSubTop li {\r\n	list-style-type: none;\r\n	margin: 0;\r\n	margin-left: 0;\r\n	border-bottom: none;\r\n    line-height: 0;\r\n}\r\n\r\n#navLeftSubTop li li {\r\n	padding: 0px 2px 0.1em 1em;\r\n    border-bottom: 1px solid #fff;\r\n    line-height: 1.6em;\r\n}\r\n\r\n#navLeftSubTop li li li {\r\n	font-size: 0.9em;\r\n    border-bottom: none;\r\n}\r\n\r\n#navLeftSubTop li.active {\r\n	color: #3F3F3F;\r\n	background-color: #f9f9f9;\r\n}\r\n',
  `content_e` = '', `content_f` = '', `title_d` = '', `title_e` = '', `title_f` = '', `desc_d` = '', `desc_e` = '', `desc_f` = '', `key_d` = '', `key_e` = '', `key_f` = '', `search_d` = '', `search_e` = '', `search_f` = '',
  `logins` = '', `hidden` = 0, `operator` = 'Temp', `ip` = '::1', `lastmut` = 1653623611, `deleted` = 0, `typ` = 'c', `ishomepage` = 0
  WHERE `resources`.`name` = 'cssnavleftsubtop' AND `resources`.`deleted` = 0;";
$result_a=mysql_query($sql_a);

$t=time();
$beforets=array('.css"'       ,  '.css)'       ,  '.jpg)'       ,  '.jpe)'       ,  '.jpeg)'       ,  '.gif)'       ,  '.htm)'       ,  '.html)'       ,  '.svg)'       ,  '.ttf)'       ,  '.woff)'           );
$afterts= array('.css?'.$t.'"',  '.css?'.$t.')',  '.jpg?'.$t.')',  '.jpe?'.$t.')',  '.jpeg?'.$t.')',  '.gif?'.$t.')',  '.htm?'.$t.')',  '.html?'.$t.')',  '.svg?'.$t.')',  '.ttf?'.$t.')',  '.woff?'.$t.')'    );


$sql_a="SELECT * FROM resources WHERE typ='c' AND deleted!=1";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);

if (file_put_contents('../r/'.$row_a['name'].'.css',str_replace($beforets,$afterts,$row_a['content_d'].' '))) {
echo '<!--w ../r/ ok -->';
}
else echo '<script>alert("PROBLEM: The server can store your data but not write the consequent output file '.$row_a['name'].'.\n\nEFFECTS: The data you typed is stored but you won\'t see any effects in the layout.\n\nSOLUTION: CHMOD the folder named \'r\' RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>';
}

$configphp=file_get_contents('../config.php');
if(strpos($configphp,'$resamplingquality=100;')>0&&(strpos($configphp,'$resizedlongedgepx=1000;')>0||strpos($configphp,'$resizedwidth=1000;')>0)){
$configphp=str_replace('$resizedwidth=;','$resizedlongedgepx=;',$configphp);
$configphp=str_replace('$resamplingquality=100;','$resamplingquality=70;',$configphp);
if(strpos($configphp,'$resamplingquality=70;')>0)$configphp=str_replace('$resizedlongedgepx=1000;','$resizedlongedgepx=2000;',$configphp);
file_put_contents('../config.php',$configphp);
}

 function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (is_dir($dir."/".$object))
           rrmdir($dir."/".$object);
         else
           unlink($dir."/".$object); 
       } 
     }
     rmdir($dir); 
   } 
 }
rrmdir('../fancybox');
rrmdir('../lib');
unlink('../v.js');
unlink('../c.js');
?>
<script>
setTimeout("alert('Your DB has been updated. If you are going to experience a loop now, delete the file runonce.php in the folder r of your u5CMS installation on your server!')",1111);
setTimeout("top.location.href=top.location.href",2222);
</script>
