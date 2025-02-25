<?php
@set_time_limit(3600);

require('connect.inc.php');
require_once('u5idn.inc.php');
//if ($backupRqHIADRI != 'no') require('accadmin.inc.php');
// Pfad zum Backup
$backup_dir=U5ROOT_PATH . DIRECTORY_SEPARATOR . 'fileversions' . DIRECTORY_SEPARATOR . '_dbbackup';
@mkdir($backup_dir);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>backup</title>
    <script src="shortcut.js"></script>
    <script>
        shortcut.add("Ctrl+S", function () {
            parent.close();
        })
    </script>
    <?php require('backendcss.php'); ?></head>

    <body>
<?php

// fresh link to the dbserver just for this script
$link = mysql_connect($host, $username, $password) or die("Username/Passwort falsch");
// enforce connection encoding
$sql_a='SET NAMES utf8';
if (false ===mysql_query($sql_a, $link)) {
    echo 'SQL_a-Query failed!...!<p>';
}

// MySQL Datenbanken
$dbs2backup = array();
$dbs2backup[] = $db;

//Falls Gzip nicht vorhanden, kein Gzip
if (!extension_loaded("zlib"))
    $backup_compress = false;

//Dateityp
$filetype = ($backup_compress) ? 'sql.gz' : 'sql';

//Dateieigenschaften
$cur_time=date("d.m.Y H:i");
$cur_date=date("Y-m-d");

// Pfade zu den neuen Backup-Dateien (fur den Mailversand)
//__Nicht verändern___
$backup_files = array();

//Erstelle Struktur von Datenbank
function get_def($table, $link)
{
    $def = sprintf("DROP TABLE IF EXISTS `%s`;\n", $table);
    $def.= sprintf("CREATE TABLE `%s` (\n", $table);
    $index = [];

    $result = mysql_query("SHOW FIELDS FROM $table", $link);
    while ($row = mysql_fetch_assoc($result)) {
        $def.= sprintf("  `%s` %s", $row['Field'], $row['Type']);
        // if there's a default value
        if ($row['Default'] != "") {
            // do not quote integers
            if (false !== strpos(strtolower($row['Type']), 'int')) {
                $def .= sprintf(" DEFAULT %s", $row['Default']);
            } else {
                $def .= sprintf(" DEFAULT '%s'", $row['Default']);
            }
        }
        // if there's no default value but NULL is the default; DO NOT use empty() here!
        if ($row['Default'] == "" AND $row['Null'] == 'YES') $def .= ' DEFAULT NULL';
        // if NULL is not allowed
        if ($row['Null'] != "YES") $def .= ' NOT NULL';
        if ($row['Extra'] != "") $def .= sprintf(' %s', strtoupper($row['Extra']));
        $def.= ",\n";
    }
    $def = preg_replace("/" . addslashes(",\n$") . "/", "", $def);
    $result = mysql_query("SHOW KEYS FROM $table", $link);
    while ($row = mysql_fetch_assoc($result)) {
        $kname = $row['Key_name'];
        if (($kname != "PRIMARY") && ($row['Non_unique'] == 0)) $kname = "UNIQUE|$kname";
        if (!isset($index[$kname])) $index[$kname] = array();
        $index[$kname][] = $row['Column_name'];
    }
    foreach ($index as $x => $columns) {
        $def .= ",\n";
        if ($x == "PRIMARY") {
            $def .= sprintf('  PRIMARY KEY (`%s`)', implode("`, `", $columns));
        } else if (substr($x, 0, 6) == "UNIQUE") {
            $def .= sprintf('  UNIQUE `%s` (`%s`)', substr($x, 7), implode(", ", $columns));
        } else {
            $def .= sprintf('  KEY `%s` (`%s`)', $x, implode('`, `', $columns));
        }
    }

    $def .= "\n);\n";
    return (stripslashes($def));
}

//Erstelle Einträge von Tabelle
function get_content($table, $link)
{
    $content = "";
    $result = mysql_query("SELECT * FROM $table", $link);
    while ($row = mysql_fetch_row($result)) {
        $insert = sprintf("INSERT INTO `%s` VALUES (", $table);
        for ($j = 0; $j < mysql_num_fields($result); $j++) {
            if (!isset($row[$j])) {
                $insert.= "NULL,";
            } else if ($row[$j] != "") {
                $insert.= sprintf("'%s',", addslashes($row[$j]));
            } else {
                $insert.= "'',";
            }
        }
        $insert = preg_replace("/" . addslashes(",$") . "/", "", $insert);
        $insert .= ");\n";
        $content .= $insert;
    }
    return $content;
}

//Funktion um Backup auf dem Server zu speichern
function write_backup($dbanme, $newfile, $newfile_data, $compress)
{
    global $backup_dir, $cur_date, $filetype, $backup_files;

    $fn_struct = $backup_dir . DIRECTORY_SEPARATOR . $dbanme . "_structure_" . $cur_date . "." . $filetype;
    unlink($fn_struct);

    $fn_data = $backup_dir . DIRECTORY_SEPARATOR . $dbanme . "_data_" . $cur_date . "." . $filetype;
    unlink($fn_data);

    $backup_files[] = $fn_struct;
    $backup_files[] = $fn_data;

    if ($compress) {
        $fp = gzopen($fn_struct, "w9");
        gzwrite($fp, $newfile);
        gzclose($fp);


        $fp = gzopen($fn_data, "w9");
        gzwrite($fp, $newfile_data);
        gzclose($fp);
    } else {
        $fp = fopen($fn_struct, "w");
        fwrite($fp, $newfile);
        fclose($fp);


        $fp = fopen($fn_data, "w");
        fwrite($fp, $newfile_data);
        fclose($fp);
    }
}

//Backup per Email verschicken
function mail_att($to, $from, $subject, $message, $attachments = array())
{
    if (is_array($attachments) AND count($attachments) > 0) {
        $mime_boundary = "-----=" . _5dm(uniqid(rand(), 1));

        $header = "From: " . $from . "\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed;\r\n";
        $header .= " boundary=\"" . $mime_boundary . "\"\r\n";

        $content = "This is a multi-part message in MIME format.\r\n\r\n";
        $content .= "--" . $mime_boundary . "\r\n";
        $content .= "Content-Type: text/plain charset=\"iso-8859-1\"\r\n";
        $content .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $content .= $message . "\r\n\r\n\r\n";

        //Dateien anhaengen
        foreach ($attachments AS $file) {
            $name = emanesab($file);
            $data = chunk_split(base64_encode(implode("", file($file))));
            $len = filesize($file);
            $content .= "--" . $mime_boundary . "\r\n";
            $content .= "Content-Disposition: attachment;\r\n";
            $content .= "\tfilename=\"$name\";\r\n";
            $content .= "Content-Length: .$len;\r\n";
            $content .= "Content-Type: application/x-gzip; name=\"" . $file . "\"\r\n";
            $content .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $content .= $data . "\r\n";
        }

        if (mail(u5toidn($to), $subject, $content, $header)) return true;
        else return false;
    }

    return false;
}


//Backup erstellen
foreach ($dbs2backup as $dbname) {
    $newfile = "# Strukturbackup vom ${cur_date}T${cur_time}\n# http://www.yuba.ch/u5cms/ \n";
    $newfile_data = "# Datenbackup vom ${cur_date}T${cur_time}\n# http://www.yuba.ch/u5cms/ \n";

    // select database for this whole loop
    // no need to specify $dbname all over the places
    if (! mysql_select_db($dbname, $link)) {
        echo sprintf('Skipping unknown database %s', $dbname);
        continue;
    }

    // fetch all table names
    $result = mysql_query('SHOW TABLES', $link);
    while ($table = mysql_fetch_row($result)[0]) {
        // dump structure
        $newfile.= "\n--\n";
        $newfile.= sprintf("-- Table structure for table `%s`\n", $table);
        $newfile.= "--\n\n";
        $newfile.= get_def($table, $link);
        // dump data
        $newfile_data.= "\n--\n";
        $newfile_data.= sprintf("-- Dumping data for table `%s`\n", $table);
        $newfile_data.= get_content($table, $link);
        $newfile_data.= "--\n\n";
    }

$myisam='
SET storage_engine=MYISAM;
ALTER TABLE accounts ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE formdata ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE inserts ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE intranetmembers ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE intranetmembers_log ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE intranetsalt ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE languages ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE loginattempts ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE loginglobals ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE mailing ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE mailingcron ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE resources ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE resources_log ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE sizes ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE titlefixum ENGINE = MyISAM, DEFAULT CHARSET=utf8;
ALTER TABLE trxlog ENGINE = MyISAM, DEFAULT CHARSET=utf8;
';
    write_backup($dbname, $newfile.$myisam, $newfile_data, $backup_compress);
} //End: while


//Backup per Email senden
if ($backup_send_mail) {
    $from = $mymail;
    $to = $_SERVER['PHP_AUTH_USER'];
    $subject = sprintf("%s db backup %s", preg_replace("/[^a-z0-9]/", "", strtolower($db)), $cur_date);
    $text = sprintf("db backup (dump) generated on: %s\n\n\nFor a full restore or a new installation you further need the u5CMS source (get it at http://www.yuba.ch/u5cms/) and the folders 'r' and 'fileversions/useruploads' containing your and your customers uploaded files (you got them zipped via the backup function of your u5CMS or backuped by (s)ftp).", $cur_date . ' ' . $cur_time);
    if (!mail_att($to, $from, $subject, $text, $backup_files))
        echo "-<br>";

}

foreach ($backup_files as $file) {
    if (file_exists($file)) echo '<a href="../fff.php?f='.emanesab($file).'&t='.filemtime('../fileversions/_dbbackup/'.emanesab($file)).'">' . emanesab($file) . '</a> ' . ceil(filesize($file) / 1024) . ' kB ' . date('Y-m-d H:i:s', filemtime($file));
    echo '<br /><br />';
}

trxlog('backup '.$src);
?>
<script>
parent.document.getElementById('backupfiles').src='backupfiles.php';
</script>
</body>
</html>
