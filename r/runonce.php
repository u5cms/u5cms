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

// START: Fix language columns in PHP-code in content

$sql_a="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'resources' AND COLUMN_NAME = 'content_d'";
$result_a=mysql_query($sql_a);
$row_a = mysql_fetch_array($result_a);



if($row_a['COLUMN_NAME']=='content_d') {

$legacyLangCols = array('content_d', 'content_e', 'content_f');
foreach ($legacyLangCols as $langCol) {
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'content_d', 'content_1');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'content_e', 'content_2');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'content_f', 'content_3');";
    $result_a=mysql_query($sql_a);

    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'title_d', 'title_1');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'title_e', 'title_2');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'title_f', 'title_3');";
    $result_a=mysql_query($sql_a);

    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'key_d', 'key_1');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'key_e', 'key_2');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'key_f', 'key_3');";
    $result_a=mysql_query($sql_a);

    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'desc_d', 'desc_1');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'desc_e', 'desc_2');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'desc_f', 'desc_3');";
    $result_a=mysql_query($sql_a);

    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'search_d', 'search_1');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'search_e', 'search_2');";
    $result_a=mysql_query($sql_a);
    $sql_a="UPDATE resources SET $langCol=REPLACE($langCol, 'search_f', 'search_3');";
    $result_a=mysql_query($sql_a);
}
}

$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'content_d', 'content_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'content_e', 'content_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'content_f', 'content_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'title_d', 'title_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'title_e', 'title_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'title_f', 'title_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'key_d', 'key_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'key_e', 'key_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'key_f', 'key_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'desc_d', 'desc_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'desc_e', 'desc_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'desc_f', 'desc_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'search_d', 'search_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'search_e', 'search_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET datacsv=REPLACE(datacsv, 'search_f', 'search_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'content_d', 'content_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'content_e', 'content_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'content_f', 'content_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'title_d', 'title_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'title_e', 'title_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'title_f', 'title_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'key_d', 'key_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'key_e', 'key_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'key_f', 'key_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'desc_d', 'desc_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'desc_e', 'desc_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'desc_f', 'desc_3');";
$result_a=mysql_query($sql_a);

$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'search_d', 'search_1');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'search_e', 'search_2');";
$result_a=mysql_query($sql_a);
$sql_a="UPDATE formdata SET headcsv=REPLACE(headcsv, 'search_f', 'search_3');";
$result_a=mysql_query($sql_a);
// END: Fix language columns in PHP-code in content

$sql_a="ALTER TABLE languages ADD IF NOT EXISTS lan4na varchar(255) DEFAULT NULL AFTER lan3na";
mysql_query($sql_a);
$sql_a="ALTER TABLE languages ADD IF NOT EXISTS lan5na varchar(255) DEFAULT NULL AFTER lan4na";
mysql_query($sql_a);

$sql_a="ALTER TABLE languages ADD IF NOT EXISTS lan4name varchar(255) DEFAULT NULL AFTER lan3name";
mysql_query($sql_a);
$sql_a="ALTER TABLE languages ADD IF NOT EXISTS lan5name varchar(255) DEFAULT NULL AFTER lan4name";
mysql_query($sql_a);

$sql_a="ALTER TABLE titlefixum CHANGE COLUMN IF EXISTS d `1` varchar(255) DEFAULT NULL";
mysql_query($sql_a);
$sql_a="ALTER TABLE titlefixum CHANGE COLUMN IF EXISTS e `2` varchar(255) DEFAULT NULL";
mysql_query($sql_a);
$sql_a="ALTER TABLE titlefixum CHANGE COLUMN IF EXISTS f `3` varchar(255) DEFAULT NULL";
mysql_query($sql_a);

$sql_a="ALTER TABLE titlefixum ADD IF NOT EXISTS `4` varchar(255) DEFAULT NULL AFTER `3`";
mysql_query($sql_a);
$sql_a="ALTER TABLE titlefixum ADD IF NOT EXISTS `5` varchar(255) DEFAULT NULL AFTER `4`";
mysql_query($sql_a);

$migrate_tables = array(
    'languages' => array(
        'term',
        'andhit',
        'andhits',
        'orhit',
        'orhits',
        'nohit',
        'recherche',
        'notpub',
        'picsfound',
        'morepics',
        'czoom'
    ),
    'loginglobals' => array(
        'logintitle',
        'loginintro',
        'username',
        'password',
        'loginbutton',
        'loginoutro',
        'logout',
        'wait'
    ),
    'resources' => array(
        'content',
        'title',
        'desc',
        'key',
        'search'
    ),
    'resources_log' => array(
        'content',
        'title',
        'desc',
        'key'
    )
);
$datatype_definition = array(
    'resources.content' => 'mediumtext',
    'resources.title' => 'varchar(1000)',
    'resources.desc' => 'text',
    'resources.search' => 'mediumtext',
    'resources_log.content' => 'mediumtext',
    'resources_log.desc' => 'text',
    'loginglobals.logintitle' => "varchar(255) NULL DEFAULT 'Login'",
    'loginglobals.loginintro' => 'text',
    'loginglobals.username' => "varchar(255) NULL DEFAULT 'Username'",
    'loginglobals.password' => "varchar(255) NULL DEFAULT 'Password'",
    'loginglobals.loginbutton' => "varchar(255) NULL DEFAULT 'OK'",
    'loginglobals.loginoutro' => 'text',
    'loginglobals.logout' => "varchar(255) NULL DEFAULT 'logout'",
    'loginglobals.wait' => "varchar(255) NULL DEFAULT 'Too many login attempts. Try again in'"
);

function get_data_type($table_name, $table_field) {
    global $datatype_definition;
    if (isset($datatype_definition[$table_name.'.'.$table_field])) {
        return $datatype_definition[$table_name.'.'.$table_field];
    }
    return 'varchar(255)';
}

function adapt_table_to_5languages($table_name, $table_fields) {
    foreach ($table_fields as $table_field) {
        $datatype = get_data_type($table_name, $table_field);
        $sql_a="ALTER TABLE $table_name CHANGE COLUMN IF EXISTS `{$table_field}_d` {$table_field}_1 $datatype";
        mysql_query($sql_a);
        $sql_a="ALTER TABLE $table_name CHANGE COLUMN IF EXISTS `{$table_field}_e` {$table_field}_2 $datatype";
        mysql_query($sql_a);
        $sql_a="ALTER TABLE $table_name CHANGE COLUMN IF EXISTS `{$table_field}_f` {$table_field}_3 $datatype";
        mysql_query($sql_a);

        $sql_a="ALTER TABLE $table_name ADD IF NOT EXISTS {$table_field}_4 ".$datatype." AFTER {$table_field}_3";
        mysql_query($sql_a);
        $sql_a="ALTER TABLE $table_name ADD IF NOT EXISTS {$table_field}_5 ".$datatype." AFTER {$table_field}_4";
        mysql_query($sql_a);
    }
}

foreach ($migrate_tables as $table_name => $table_fields) {
    adapt_table_to_5languages($table_name, $table_fields);
}

/*
$sql_a="UPDATE languages SET lan4na = 'it', lan5na = 'pt', lan4name = 'italian', lan5name = 'portuguese', term_4 = 'Inserisci il termine di ricerca', term_5 = 'Por favor, insira o termo de pesquisa', andhit_4 = 'hit contenente tutto il termine', andhit_5 = 'hit contendo todo o termo', andhits_4 = 'risultati contenenti tutto il termine', andhits_5 = 'hits contendo todo o termo', orhit_4 = 'hit contenente parte del termine', orhit_5 = 'hit contendo parte do termo', orhits_4 = 'risultati contenenti parte del termine', orhits_5 = 'hits contendo parte do termo', nohit_4 = 'Nessun risultato con la tua ricerca. Suggerimento:', nohit_5 = 'Nenhum resultado com sua pesquisa. Sugestão:', recherche_4 = 'ricerca', recherche_5 = 'procurar',  notpub_4 = 'Navigazione.Questa pagina non è attualmente pubblicata. Si prega di selezionare dal menu.', notpub_5 = 'Navegação. Esta página não está publicada no momento. Selecione no menu.', picsfound_1 = 'Bilder (klicken Sie auf das Bild)', picsfound_2 = 'images (click the image)', picsfound_3 = 'images (veuillez cliquer l''image)', picsfound_4 = 'immagini (clicca sull''immagine)', picsfound_5 = 'imagens (clique na imagem)', morepics_4 = 'Clique para mais imagens.', morepics_5 = 'Clique para mais imagens.', czoom_4 = 'clicca per ingrandire', czoom_5 = 'clique para ampliar' where lan1na = 'de'";
mysql_query($sql_a);


$sql_a="UPDATE titlefixum SET `4` = ' - notieren Sie hier Ihren Firmennamen', `5` = ' - notieren Sie hier Ihren Firmennamen' where `1` = ' - notieren Sie hier Ihren Firmennamen';";
mysql_query($sql_a);

$sql_a="UPDATE loginglobals SET logintitle_4 = 'Login', logintitle_5 = 'Login', loginintro_4 = '', loginintro_5 = '', username_4 = 'Username', username_5 = 'Username', password_4 = 'Password', password_5 = 'Password', loginbutton_4 = 'log in', loginbutton_5 = 'log in', loginoutro_4 = '', loginoutro_5 = '', logout_4 = 'log out', logout_5 = 'log out', wait_4 = 'Too many login attempts. Try again in', wait_5 = 'Too many login attempts. Try again in' where logintitle_1 = 'Login';";
mysql_query($sql_a);

*/ 

$sql_a="SELECT content_1 FROM resources WHERE deleted!=1 AND name='htmltemplate'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);
$c=explode('<body',$row_a['content_1']);
$c=$c[1];
$head='<!DOCTYPE html>
{{{meta}}}
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
<link rel="stylesheet" href="r/cssbase.css" type="text/css" />
<link href="favicon.ico" rel="shortcut icon" />
<link rel="stylesheet" href="js/jquery.fancybox.min.css" />
<script src="js/jquery.js"></script>
<script src="js/jquery.fancybox.min.js"></script>
<script src="js/u5_scripts.js" type="text/javascript"></script>
<script src="r/jsmobilespecific.css"></script>
</head>
<body';
$c=$head.$c;

$sql_a="UPDATE resources SET content_1='".mysql_real_escape_string($c)."' WHERE deleted!=1 AND name='htmltemplate' AND content_1 NOT LIKE '%js/jquery.fancybox.min.js%'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
file_put_contents('../fileversions/'.sha1($c).'.updatedone',file_get_contents('../r/runonce.php'));

$sql_a="UPDATE sizes SET smallimgL_h=50 WHERE smallimgL_h<1";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE sizes SET zoomedimg_w=1000, zoomedimg_h=1000";
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
  `logintitle_1` varchar(255)  DEFAULT 'Login',
  `logintitle_2` varchar(255)  DEFAULT 'Login',
  `logintitle_3` varchar(255)  DEFAULT 'Login',
  `loginintro_1` text ,
  `loginintro_2` text ,
  `loginintro_3` text ,
  `username_1` varchar(255)  DEFAULT 'Username',
  `username_2` varchar(255)  DEFAULT 'Username',
  `username_3` varchar(255)  DEFAULT 'Username',
  `password_1` varchar(255)  DEFAULT 'Password',
  `password_2` varchar(255)  DEFAULT 'Password',
  `password_3` varchar(255)  DEFAULT 'Password',
  `loginbutton_1` varchar(255)  DEFAULT 'OK',
  `loginbutton_2` varchar(255)  DEFAULT 'OK',
  `loginbutton_3` varchar(255)  DEFAULT 'OK',
  `loginoutro_1` text ,
  `loginoutro_2` text ,
  `loginoutro_3` text ,
  `logout_1` varchar(255)  DEFAULT 'logout',
  `logout_2` varchar(255)  DEFAULT 'logout',
  `logout_3` varchar(255)  DEFAULT 'logout'
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

$sql_a="INSERT INTO `loginglobals` (`logintitle_1`, `logintitle_2`, `logintitle_3`, `loginintro_1`, `loginintro_2`, `loginintro_3`, `username_1`, `username_2`, `username_3`, `password_1`, `password_2`, `password_3`, `loginbutton_1`, `loginbutton_2`, `loginbutton_3`, `loginoutro_1`, `loginoutro_2`, `loginoutro_3`, `logout_1`, `logout_2`, `logout_3`, `wait_1`, `wait_2`, `wait_3`) VALUES
('Login', 'Login', 'Login', '', '', '', 'Username', 'Username', 'Username', 'Password', 'Password', 'Password', 'log in', 'log in', 'log in', '', '', '', 'log out', 'log out', 'log out', 'Too many login attempts. Try again in', 'Too many login attempts. Try again in', 'Too many login attempts. Try again in');
";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$sql_a="ALTER TABLE `mailing` ADD IF NOT EXISTS `mailtested` TINYINT DEFAULT '0'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="DELETE FROM inserts";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="INSERT INTO `inserts` VALUES (1,'','','[lo:]','<script src=\"js/logo.php?name=','[:lo]','\"></script>','','','[lo:]denoteanobjectnamehere[:lo]',0,0),(2,'','','[l]','<div style=\"text-align:left\">','[-]','</div>','','','[l]writeyourtexthere[-]\r\n',0,1),(3,'','','[in:]','<iframe src=\"sendintranetlogin.php?','[:in]','\" frameborder=\"0\" scrolling=\"no\" width=\"100%\"></iframe>','','','',0,0),(4,'','','[au:]','<iframe style=\"display:inline\" width=\"100%\" height=\"20\" frameborder=\"0\" scrolling=\"no\" src=\"authuser.php?t=','[:au]','&c=[_pagename_]\"></iframe>','','','',0,0),(5,'ELEMENTS','','[:fi]','</fieldset>','[:fo]','</form>','','','',0,1),(6,'ELEMENTS','form','[fo:]','<iframe style=\"visibility:hidden\" width=\"0\" height=\"0\" frameborder=\"0\" name=\"fvifr\" src=\"fv.php\"></iframe><form class=\"unibeform\" name=\"u5form\" action=\"formsave.php?n=[_pagename_]&l=[_language_]\" method=\"post\" target=\"fvifr\">','[:fo]','</form>','[fi:],[:fi],[le:],[:le]','<fieldset class=\"ksl\">,</fieldset>,<legend>,</legend>,<label>,</label>','[fo:]\r\n[fi:]\r\n[le:]Your data[:le]\r\n[h:]\r\n\r\n<!--Hidden fields in your form. You should have them in your form!-->\r\n\r\n<input type=\"hidden\" name=\"ouremail\" value=\"enter@youremailaddress.here\" />\r\n<!-- THE ABOVE E-MAIL ADDRESS MUST HAVE THE SAME DOMAIN AS THIS WEBSITE AND IT MUST EXIST!!! -->\r\n\r\n<input type=\"hidden\" name=\"thanks\" value=\"thanks\" />\r\n<input type=\"hidden\" name=\"thankssubject\" value=\"Thank you for your message!\" />\r\n<input type=\"hidden\" name=\"thankstext\" value=\"Thank you for your message! Please find it quoted below:\" />\r\n<input type=\"hidden\" name=\"thanksgreetings\" value=\"Yours faithfully, John Smith\" />\r\n\r\n<!--Visible fields in your form displayed to the form user-->\r\n<label>Business*</label>\r\n <select name=\"business_mandatory\"> \r\n <option value=\"\">please select</option>\r\n <option value=\"education\">Education</option>\r\n <option value=\"engineering\">Engineering</option>\r\n <option value=\"medical\">Medical</option>\r\n <option value=\"other\">other</option>\r\n </select>\r\n\r\n<label>Name*</label><input type=\"text\" name=\"name_mandatory\" />\r\n \r\n<label>Firstname (optional)</label><input type=\"text\" name=\"firstname\" />\r\n \r\n<label>E-Mail*</label><input type=\"text\" name=\"youremail_mandatory\" />\r\n\r\n<br><label>Your CV* (pdf, doc, docx)</label><script src=\"upload_mandatory\"></script>\r\n\r\n<br><label>Your photo (optional)</label><script src=\"upload\"></script>\r\n \r\n<label>Message*</label><textarea rows=\"3\" style=\"width:98%\" type=\"text\" name=\"message_mandatory\"></textarea>\r\n\r\n<label>How much is hundred minus one (enter digits)? [whythis]</label><input type=\"text\" name=\"cliving_mandatory\" />\r\n\r\n<label>&nbsp;</label><input type=\"submit\" value=\"send\" />\r\n\r\n<!-- FOR MORE INFORMATION ON THE PSEUDO-CAPTCHA cliving_mandatory AND OTHER SPECIAL FUNCTIONS FOR YOUR FORMS, CONSULT https://yuba.ch/forms -->\r\n\r\n[:h]\r\n[:fi]\r\n[:fo]\r\n',0,1),(7,'ELEMENTS','goto','[go:]','<script>location.replace(\'?c=','[:go]','\')</script>','','','[go:]denoteatargetpagenamehere[:go]\r\n\r\n',0,0),(8,'ELEMENTS','html','[h:]','','[:h]','','','','[h:]writeyourtexthere[:h]',0,0),(9,'ELEMENTS','image','','[:::::',']',']','','','[:::::denoteanobjectnamehere]',0,0),(10,'ELEMENTS','incl','[','[',']',']','','','[$$$:denoteasourcepagenamehere]',0,0),(11,'ELEMENTS','line','[_]','<div class=\"separator\"></div>','','','','','[_]\r\n',0,0),(12,'ELEMENTS','list123','[o:]','<ol>','[:o]','</ol>','','','[o:]\r\n---firstitem---\r\n---seconditem---\r\n---andnextitems---\r\n[:o]\r\n',0,1),(13,'ELEMENTS','list•••','[u:]','<ul>','[:u]','</ul>','','','[u:]\r\n---firstitem---\r\n---seconditem---\r\n---andnextitems---\r\n[:u]\r\n',0,1),(14,'ELEMENTS','mark','[!]','<div class=\"mark\">','[-]','</div>','','','[!]writeyourtexthere[-]\r\n',0,1),(15,'ELEMENTS','table','[t:]','<table class=\"table\">','[:t]','</table>','*||;,*|*,||*,||;,||,|*,*|,|','</th></tr>,</th><th>,<tr><th>,</td></tr>,<tr><td>,\r\n</td><th>,</th><td>,</td><td>','[t:]\r\n||        |* ZH *|* BE *|* VD *|* GE *||;\r\n||* Temp *| 22°C | 21°C | 20°C | 19°C ||;\r\n||* Sun  *| 4h   | 5h   | 4h   |  6h  ||;\r\n||* Rain *| 0    | 0    | 2mm  |  0   ||;\r\n[:t]\r\n',0,0),(16,'ELEMENTS','url','[l:]','<script>s=\"','[:l]','\"</script><script src=\"js/url.js\"></script>','','','[l:]https://www.maarsen.ch/anydirectoriespagesfiles[:l]',0,0),(17,'INLINE','#','[#:]','<a href=\"#','[:#]','</a>','?#','\">','[#:]anchorname?#linktext[:#]',0,0),(18,'INLINE','#a','[#a:]','<a name=\"','[:#a]','\"></a>','','','[#a:]anchorname[:#a]',0,0),(19,'INLINE','<s>&nbsp;x&nbsp;</s>','[x]','<span class=\"crossout\">','[/]','</span>','','','[x]writehere[/]',0,0),(20,'INLINE','<span class=\"hot\">A</span>','[hot]','<span class=\"hot\">','[/]','</span>','','','[hot]writehere[/]',0,0),(21,'INLINE','<span title=\"larger font\">&lt;</span>','[&lt;]','<span class=\"larger\">','[/]','</span>','','','[<]writehere[/]',0,0),(22,'INLINE','<span title=\"smaller font\">&gt;</span>','[&gt;]','<span class=\"smaller\">','[/]','</span>','','','[>]writehere[/]',0,0),(23,'INLINE','<u>u</u>','[u]','<span class=\"underline\">','[/]','</span>','','','[u]writehere[/]',0,0),(24,'INLINE','abbr','[ab]','<abbr class=\"abbroracro\" title=\"','[/ab]','</abbr>','=','\" onclick=\"abbroracro(this)\">','[ab]Learning Management System=LMS[/ab]',0,0),(25,'INLINE','acro','[ac]','<acronym class=\"abbroracro\" title=\"','[/ac]','</acronym>','=','\" onclick=\"abbroracro(this)\">','[ac]Read Only Memory=ROM[/ac]',0,0),(26,'INLINE','bold','[b]','<span class=\"bold\">','[/]','</span>','','','[b]writehere[/]',0,0),(27,'INLINE','hilite','[h]','<span class=\"stabiloboss\">','[/]','</span>','','','[h]writehere[/]',0,0),(28,'INLINE','italic','[i]','<span class=\"italic\">','[/]','</span>','','','[i]writehere[/]',0,0),(29,'INLINE','mono','[m]','<span class=\"mono\">','[/]','</span>','','','[m]writehere[/]',0,0),(30,'INLINE','sub','[dn]','<span class=\"subscript\">','[/]','</span>','','','[dn]x[/]',0,0),(31,'INLINE','sup','[up]','<span class=\"superscript\">','[/]','</span>','','','[up]x[/]',0,0),(32,'INLINE','veil','[v]','<div class=\"veil\">','[/]','</div>','','','[v]writehere[/]',0,0),(33,'PARAGRAPH','<span title=\"larger font &amp; line\">&lt;:</span>','[&lt;:]','<div class=\"largerblock\">','[-]','</div>','','','[<:]writehere[-]',0,1),(34,'PARAGRAPH','<span title=\"smaller font &amp; line\">&gt;:</span>','[&gt;:]','<div class=\"smallerblock\">','[-]','</div>','','','[>:]writehere[-]',0,1),(35,'PARAGRAPH','?','[?:]','<a href=\"javascript:void(0)\" class=\"showblockinactivetab\" onclick=\"if (this.nextSibling.style.display==\'block\') showblockscript(this); else showblockscript(this,0)\">','[:?]','</div>','???','</a><div class=\"showblockdivinner\"><div class=\"showblockclose\" onclick=\"showblockscript(this.parentNode.previousSibling)\">×</div>','¶[?:]ClickHereToSeeXY ??? HereComesTheXYContentOUTSIDELeadingParagraphSymbolMayBeMandatory[:?]\n',0,1),(36,'PARAGRAPH','??','[??:]','<a href=\"javascript:void(0)\" class=\"showblock2inactivetab\" onclick=\"if (this.nextSibling.style.display==\'block\') showblock2script(this); else showblock2script(this,0)\">','[:??]','</div>','???','</a><div class=\"showblock2divinner\"><div class=\"showblock2close\" onclick=\"showblock2script(this.parentNode.previousSibling)\">×</div>','¶[??:]ClickHereToSeeXY ??? HereComesTheXYContentOUTSIDELeadingParagraphSymbolMayBeMandatory[:??] ',0,1),(37,'PARAGRAPH','align right','[r]','<div class=\"right\">','[-]','</div>','','','[r]writeyourtexthere[-]\r\n',0,1),(38,'PARAGRAPH','bquot','[bq:]','<blockquote>','[:bq]','</blockquote>','','','[bq:]writehere[:bq]\r\n',0,1),(39,'PARAGRAPH','center','[c]','<div class=\"center\">','[-]','</div>','','','[c]writeyourtexthere[-]\r\n',0,1),(40,'PARAGRAPH','justify','[j]','<div class=\"justify\">','[-]','</div>','','','[j]writeyourtexthere[-]\r\n',0,1),(41,'PARAGRAPH','note','[n]','<div class=\"note\">','[-]','</div>','','','[n]writehere[-]\r\n',0,1),(42,'PARAGRAPH','pre','[pr:]','<pre>','[:pr]','</pre>','','','[pr:]writehere[:pr]\n',0,1),(43,'PARAGRAPH','stay right','[s]','<div class=\"stayright\">','[-]','</div>','','','[s]writeyourtexthere[-]\r\n',0,1),(44,'TITLES&#8239;et&#8239;al','<span title=\"caption per image in albums\">xcaption</span>','[ca:]','','[:ca]','','[ca],&gt;&gt;&gt;,[/],</span><br />','','\r\nCUT AND PASTE THE FOLLOWING 3 LINES INTO THE LONG CAPTION OF YOUR ALBUM METADATA. EXTENSION IS ALLOWED (103, 104 ETC.)\r\n\r\n[ca:][ca]100>>>writecaptionforjpg100here[/]\r\n     [ca]101>>>writecaptionforjpg101here[/]\r\n     [ca]102>>>writecaptionforjpg102here[/][:ca]\r\n\r\n',0,0),(45,'TITLES&#8239;et&#8239;al','maintitle','[[[[','<h1>',']]]]','</h1>','','','[[[[writeyourtitlehere]]]]\r\n',0,1),(46,'TITLES&#8239;et&#8239;al','subtitle','[[[','<h2>',']]]','</h2>','','','[[[writeyourtitlehere]]]\r\n',0,1),(47,'TITLES&#8239;et&#8239;al','undersubtitle','[[','<h3>',']]','</h3>','','','[[writeyourtitlehere]]\r\n',0,1),(48,'X',NULL,'[:?]','</div>',NULL,NULL,NULL,NULL,NULL,0,1),(49,'X',NULL,'[:??]','</div>',NULL,NULL,NULL,NULL,NULL,0,1),(50,'X',NULL,'[/]','</span>',NULL,NULL,NULL,NULL,NULL,0,0),(51,'X',NULL,'[-]','</div>',NULL,NULL,NULL,NULL,NULL,0,1),(52,'X','','[:fo]','</form>','','','','','',0,1),(53,'X','','---','<li>','---<br />','</li>','','','',0,1),(54,'X','','[:u]','</ul>','','','','','',0,1),(55,'X','','[:o]','</ol>','','','','','',0,1),(56,'X','','[mf]','<input type=\"hidden\" name=\"ed2cu\" value=\"on\" /><iframe name=\"ifrmonofill\" width=\"0\" height=\"0\" style=\"visibility:hidden\"></iframe><script>locationhref=location.href;if(locationhref.indexOf(\'c=\')<0) locationhref+=\'&c=MF_NOT_ALLOWED_ON_YOUR_MAIN_PAGE\';if(locationhref.indexOf(\'id=\')<0) locationhref+=\'&id=0\';ifrmonofill.location.href=\'monofill.php?n=\'+locationhref.split(\'c=\')[1].split(\'&\')[0]+\'&id=\'+locationhref.split(\'id=\')[1].split(\'&\')[0];</script>','','','','','',0,0),(57,'X','','[mfs]','<input type=\"hidden\" name=\"ed2cu\" value=\"on\" /><iframe name=\"ifrmonofillshared\" width=\"0\" height=\"0\" style=\"visibility:hidden\"></iframe><script>locationhref=location.href;if(locationhref.indexOf(\'c=\')<0) locationhref+=\'&c=MF_NOT_ALLOWED_ON_YOUR_MAIN_PAGE\';if(locationhref.indexOf(\'id=\')<0) locationhref+=\'&id=0\';ifrmonofillshared.location.href=\'monofillshared.php?n=\'+locationhref.split(\'c=\')[1].split(\'&\')[0]+\'&id=\'+locationhref.split(\'id=\')[1].split(\'&\')[0];</script>','','','','','',0,0),(58,'X','<div style=\"display:none\"><span title=\"share in facebook/twitter\">xshare</span></div>','[s:]','<form method=\"post\" action=\"xshare.php\" target=\"xshareWIN\" onclick=\"xshfrm=this;this.xshareTEXT.value=this.innerHTML.split(\'<\'+\'xshare\'+\'>\')[1];xshfrm.xshareTITLE.value=document.title;xshfrm.xshareLOCATION.value=document.location.href\" onmouseover=\"xshfrm=this;this.xshareTEXT.value=this.innerHTML.split(\'<\'+\'xshare\'+\'>\')[1];xshfrm.xshareTITLE.value=document.title;xshfrm.xshareLOCATION.value=document.location.href\" onfocus=\"xshfrm=this;this.xshareTEXT.value=this.innerHTML.split(\'<\'+\'xshare\'+\'>\')[1];xshfrm.xshareTITLE.value=document.title;xshfrm.xshareLOCATION.value=document.location.href\" onkeydown=\"xshfrm=this;this.xshareTEXT.value=this.innerHTML.split(\'<\'+\'xshare\'+\'>\')[1];xshfrm.xshareTITLE.value=document.title;xshfrm.xshareLOCATION.value=document.location.href\"><input type=\"hidden\" name=\"xshareTEXT\" /><input type=\"hidden\" name=\"xshareTITLE\" /><input type=\"hidden\" name=\"xshareLOCATION\" /><input type=\"hidden\" name=\"xshareTYPE\" /><a href=\"javascript:void(0)\" onclick=\"xshfrm.xshareTYPE.value=\'f\';xshhdl=window.open(\'xshare.php\', \'xshareWIN\',\'toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=800,height=500\');xshfrm.submit();xshhdl.focus()\" title=\"Share on Facebook\"><img src=\"images/facebook.png\" border=\"0\" alt=\"Share on Facebook\"></a>&nbsp;<a href=\"javascript:void(0)\" onclick=\"xshfrm.xshareTYPE.value=\'t\';xshhdl=window.open(\'xshare.php\', \'xshareWIN\',\'toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=800,height=500\');xshfrm.submit();xshhdl.focus()\" title=\"Share on Twitter\"><img src=\"images/twitter.png\" border=\"0\" alt=\"Share on Twitter\"></a>&nbsp;<xshare>','[:s]','<xshare></xshare></xshare></form>','','','[s:]noteeventtosharehere[:s]',0,0),(59,'X','<span title=\"escaped square brackets\">[ ]</span>','(&uml;','&#91;','&uml;)','&#93;','','','(¨writeyourtexthere¨)',1,0),(60,'X','<span title=\"new cleared paragraph; type a backslash just before (\\¶) to achieve the opposit (suppress line break; caution: no succeeding spaces or characters)\">&para;</span>','&para;','<p class=\"clearing\"></p>','¶','<p class=\"clearing\"></p>','','','¶',1,1),(61,'X','<span title=\"non-breaking space\">nbsp</span>','&curren;','&nbsp;','','','','','¤',1,0),(62,'X','<span title=\"soft hyphen\">shy</span>','&cedil;','&shy;','','','','','¸',1,0);";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';



$sql_a="ALTER TABLE `mailing` CHANGE `mailsubject` `mailsubject` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginglobals` ADD IF NOT EXISTS `wait_1` VARCHAR(255)  DEFAULT 'Too many login attempts. Try again in' AFTER `logout_3`, ADD IF NOT EXISTS `wait_2` VARCHAR(255)  DEFAULT 'Too many login attempts. Try again in' AFTER `wait_1`, ADD IF NOT EXISTS `wait_3` VARCHAR(255)  DEFAULT 'Too many login attempts. Try again in' AFTER `wait_2`;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="CREATE TABLE IF NOT EXISTS `loginattempts` (
  `username` varchar(255) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL
) ENGINE=MyISAM, DEFAULT CHARSET=utf8;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginattempts` ADD IF NOT EXISTS `mailed` INT DEFAULT '0' ;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginattempts`
  ADD KEY IF NOT EXISTS `username` (`username`),
  ADD KEY IF NOT EXISTS `mailed` (`mailed`);";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `loginattempts`
  ADD KEY IF NOT EXISTS `timestamp` (`timestamp`);";

$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$search='@import url("../plugins/overlay/jquery.fancybox.css");';
$sql_a="UPDATE `resources` SET content_1=REPLACE(content_1,'$search','')";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$search="if (document.cookie.indexOf(\'u5cmsfrcthsvw=desktop\') > -1) document.getElementById(\'body\').innerHTML +=";
$replace="if (document.cookie.indexOf(\'u5cmsfrcthsvw=desktop\') > -1) document.getElementById(\'footer\').innerHTML +=";
$sql_a="UPDATE `resources` SET content_1=REPLACE(content_1,'$search','$replace')";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$search='r/cssbase.css?';
$replace='r/cssbase.css" "?';
$sql_a="UPDATE `resources` SET content_1=REPLACE(content_1,'$search','$replace')";
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

$sql_a="ALTER TABLE `mailing` ADD PRIMARY KEY IF NOT EXISTS (`id`);";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `mailing`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="ALTER TABLE `mailingcron`
 ADD PRIMARY KEY IF NOT EXISTS (`id`);";
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

$sql_a="UPDATE resources SET content_1=REPLACE(content_1,'window.onload = function() {','function u5mkmobile() {') WHERE name='jsmobilespecific';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_1=REPLACE(content_1,'<script>\r\nif(typeof u5mkmobile===\"function\" && !document.getElementById(\'nevermobile\'))void(0) //uXXX5mkmobile()DUPLICATE LINE REMOVE THIS LINE!!!;\r\n</script>\r\n
','') WHERE name='htmltemplate';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_1=REPLACE(content_1,'<script>if(typeof u5mkmobile===\"function\")u5mkmobile();</script>\r\n','<script>if(typeof u5mkmobile===\"function\" && !document.getElementById(\'nevermobile\'))u5mkmobile();</script>\r\n') WHERE name='htmltemplate';";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_1=REPLACE(content_1,'jquery-3.3.1.min.js','jquery.js');";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_2=REPLACE(content_2,'jquery-3.3.1.min.js','jquery.js');";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_3=REPLACE(content_3,'jquery-3.3.1.min.js','jquery.js');";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_4=REPLACE(content_4,'jquery-3.3.1.min.js','jquery.js');";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$sql_a="UPDATE resources SET content_5=REPLACE(content_5,'jquery-3.3.1.min.js','jquery.js');";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';


//////////////////////////////////////////////////////////////////////////////

$sql_a="SELECT * FROM `resources` WHERE content_1 LIKE '%smallerblock%' AND name='cssstyle'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if($num_a==0) {

$sql_a="UPDATE `resources` SET content_1=CONCAT(content_1,'
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

//////////////////////////////////////////////////////////////////////////////


$sql_a="SELECT * FROM `resources` WHERE content_1 LIKE '%crossout%' AND name='cssstyle'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$num_a = mysql_num_rows($result_a);
if($num_a==0) {

$sql_a="UPDATE `resources` SET content_1=CONCAT(content_1,'
.underline {
text-decoration:underline;
}
.crossout {
text-decoration:line-through;
}
.note {
padding:7px;
background:gold;
}
') WHERE name='cssstyle'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

//////////////////////////////////////////////////////////////////////////////

$sql_a="ALTER TABLE `sizes` ADD IF NOT EXISTS `tosquare` TINYINT NULL DEFAULT '1' AFTER `scrollingalbum_w`;";
$result_a=mysql_query($sql_a);

$sql_a="ALTER TABLE `sizes` ADD IF NOT EXISTS `cropedge` INT NULL DEFAULT '0' AFTER `tosquare`;";
$result_a=mysql_query($sql_a);

//////////////////////////////////////////////////////////////////////////////

$tables = ["languages", "loginglobals", "mailing"]; // List of tables to update
$excluded_columns = ["lan1na", "lan2na", "lan3na", "lan4na", "lan5na", "lan1name", "lan2name", "lan3name", "lan4name", "lan5name", "mailsavedop", "mailsentop"]; // List of columns that should NOT be changed

foreach ($tables as $table) {
    echo "\nProcessing table: $table\n";

    // Analyze table structure
    $query = "DESCRIBE `$table`";
    $result = mysql_query($query, $link);

    if (!$result) {
        echo "Error retrieving table structure for `$table`: " . mysql_error() . "\n";
        continue;
    }

    $alter_statements = [];
    while ($row = mysql_fetch_assoc($result)) {
        if (preg_match('/^varchar\(255\)$/i', $row['Type']) && !in_array($row['Field'], $excluded_columns)) {
            $column = $row['Field'];
            $alter_statements[] = "CHANGE COLUMN `$column` `$column` TEXT";
        }
    }

    // Execute ALTER TABLE if `VARCHAR(255)` columns exist
    if (!empty($alter_statements)) {
        $alter_sql = "ALTER TABLE `$table` " . implode(", ", $alter_statements);
        echo "Executing: $alter_sql\n";
        $alter_result = mysql_query($alter_sql, $link);

        if ($alter_result) {
            echo "Successfully updated `$table`!\n";
        } else {
            echo "Error updating `$table`: " . mysql_error() . "\n";
        }
    } else {
        echo "No `VARCHAR(255)` columns found in `$table`.\n";
    }
}

//////////////////////////////////////////////////////////////////////////////

?><!--
// 2022-05-27: update navigation CSS for navigaton fix
$sql_a=" UPDATE `resources` SET
  `content_1` = '/*\r\nThis CSS formats the navigation if you hav NOT A split navigation.\r\n\r\nIf you want a split navigation, you have to\r\n\r\n1: In PIDVESA''s S, htmltemplate, switch the #navTop on\r\n\r\n  <div id=\"navigationtop\">\r\n    <div id=\"navTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n  </div>\r\n\r\nby removing <!-- and -->\r\n\r\n2: Also in htmltemplate, change\r\n\r\n<div id=\"navLeft\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\nto\r\n\r\n<div id=\"navLeftSubTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\n*/\r\n\r\n\r\n/*NAVLEFT*/\r\n#navLeft {\r\n	padding-top: 100px;\r\n}\r\n\r\n/* anchor styling */\r\n#navLeft a {\r\n	display: block;\r\n	text-decoration: none;\r\n	color: #3F3F3F;\r\n}\r\n\r\n#navLeft a:hover {\r\n	text-decoration: none;\r\n	color: black;\r\n}\r\n\r\n#navLeft a.activeItem {\r\n	color: #e63320;\r\n	font-weight: bolder;\r\n}\r\n\r\n/* list stylings */\r\n#navLeft ul {\r\n    margin: 0;\r\n    padding: 0;\r\n}\r\n\r\n#navLeft ul ul {\r\n    margin-left: 1.5em;\r\n}\r\n\r\n#navLeft li {\r\n	list-style-type: none;\r\n	margin: 0;\r\n	margin-left: 0;\r\n	border-bottom: 1px solid #ddd;\r\n    line-height: 2em;\r\n}\r\n\r\n#navLeft li li {\r\n	font-size: 0.9em;\r\n    border-bottom: none;\r\n}\r\n\r\n#navLeft li.active {\r\n	color: #3F3F3F;\r\n	background-color: #f9f9f9;\r\n}\r\n\r\n#navLeft li.active>ul {\r\n 	padding: 0.1em 0 0.3em 0;\r\n}\r\n',
  `content_2` = '', `content_3` = '', `title_1` = '', `title_2` = '', `title_3` = '', `desc_1` = '', `desc_2` = '', `desc_3` = '', `key_1` = '', `key_2` = '', `key_3` = '', `search_1` = '', `search_2` = '', `search_3` = '',
  `logins` = '', `hidden` = 0, `operator` = 'Temp', `ip` = '::1', `lastmut` = 1653623611, `deleted` = 0, `typ` = 'c', `ishomepage` = 0
  WHERE `resources`.`name` = 'cssnavleft' AND `resources`.`deleted` = 0;";
$result_a=mysql_query($sql_a);

$sql_a=" UPDATE `resources` SET
  `content_1` = '/*\r\nThis CSS formats the second (vertical) level of a split navigation.\r\n\r\nIf you want a split navigation, you have to\r\n\r\n1: In PIDVESA''s S, htmltemplate, switch the #navTop on\r\n\r\n  <div id=\"navigationtop\">\r\n    <div id=\"navTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n  </div>\r\n\r\nby removing <!-- and -->\r\n\r\n2: Also in htmltemplate, change\r\n\r\n<div id=\"navLeft\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\nto\r\n\r\n<div id=\"navLeftSubTop\"><a name=\"navigation\"></a>{{{navigation}}}</div>\r\n\r\n*/\r\n\r\n/*NAVLEFTSUBTOP*/\r\n#navLeftSubTop {\r\n	padding-top: 100px;\r\n}\r\n\r\n/* anchor styling */\r\n#navLeftSubTop a {\r\n	display: none;\r\n	text-decoration: none;\r\n	color: #3F3F3F;\r\n}\r\n\r\n/* turn on visibility for second and subsequent levels only */\r\n#navLeftSubTop li li a {\r\n    display: block;\r\n}\r\n\r\n#navLeftSubTop a:hover {\r\n	text-decoration: none;\r\n	color: black;\r\n}\r\n\r\n#navLeftSubTop li a.activeItem {\r\n	color: #e63320;\r\n	font-weight: bolder;\r\n}\r\n\r\n#navLeftSubTop li li a.activeItem {\r\n	color: #ff0000;\r\n	font-weight: bold;\r\n}\r\n\r\n/* list stylings */\r\n#navLeftSubTop .inactive {\r\n    display: none;\r\n	padding-left: 7px;\r\n}\r\n\r\n#navLeftSubTop ul {\r\n    margin: 0;\r\n    padding: 0;\r\n}\r\n\r\n#navLeftSubTop li {\r\n	list-style-type: none;\r\n	margin: 0;\r\n	margin-left: 0;\r\n	border-bottom: none;\r\n    line-height: 0;\r\n}\r\n\r\n#navLeftSubTop li li {\r\n	padding: 0px 2px 0.1em 1em;\r\n    border-bottom: 1px solid #fff;\r\n    line-height: 1.6em;\r\n}\r\n\r\n#navLeftSubTop li li li {\r\n	font-size: 0.9em;\r\n    border-bottom: none;\r\n}\r\n\r\n#navLeftSubTop li.active {\r\n	color: #3F3F3F;\r\n	background-color: #f9f9f9;\r\n}\r\n',
  `content_2` = '', `content_3` = '', `title_1` = '', `title_2` = '', `title_3` = '', `desc_1` = '', `desc_2` = '', `desc_3` = '', `key_1` = '', `key_2` = '', `key_3` = '', `search_1` = '', `search_2` = '', `search_3` = '',
  `logins` = '', `hidden` = 0, `operator` = 'Temp', `ip` = '::1', `lastmut` = 1653623611, `deleted` = 0, `typ` = 'c', `ishomepage` = 0
  WHERE `resources`.`name` = 'cssnavleftsubtop' AND `resources`.`deleted` = 0;";
$result_a=mysql_query($sql_a);
-->

<?php
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

if (file_put_contents('../r/'.$row_a['name'].'.css',str_replace($beforets,$afterts,$row_a['content_1'].' '))) {
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
unlink('../js/jquery-3.3.1.min.js');
include('../u5admin/putcss.inc.php');
?>
<script>
setTimeout("alert('Your DB has been updated. If you are going to experience a loop now, delete the file runonce.php in the folder r of your u5CMS installation on your server!')",1111);
setTimeout("top.location.href=top.location.href",2222);
</script>