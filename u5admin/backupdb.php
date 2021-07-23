<?php
@set_time_limit(0);
require('../config.php');
require('connect.inc.php');
require_once('u5idn.inc.php');
if ($backupRqHIADRI != 'no') require('accadmin.inc.php');
@mkdir('../fileversions/_dbbackup');
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
$sql_a = "SET NAMES utf8";
$result_a = mysql_query($sql_a);
if ($result_a == false) echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';


//Verbindung zur Datenbank
$verbindung = mysql_connect($host, $username, $password) or die("Username/Passwort falsch");

// MySQL Datenbanken
$dbname = array();
$dbname[] = $db;

// 0: Normale Datei
// 1: GZip-Datei
$compression = 1;

//Falls Gzip nicht vorhanden, kein Gzip
if (!extension_loaded("zlib"))
    $compression = 0;

// Pfad zur aktuellen Datei
$path = @preg_replace("/" . addslashes("\\\\") . "/", "/", __FILE__);
$path = dirname($path);
$path = trim($path);

// Pfad zum Backup
$path .= "/../fileversions/_dbbackup/";

//Speicherart
//0: Nur Server speichern
//1: Zusätzlich per Email versenden
$send = 1;

//Email-Adresse f&uuml;r Backup
$email = $_SERVER['PHP_AUTH_USER'];


//Dateityp
if ($compression == 1) $filetype = "sql.gz";
else $filetype = "sql";

//Dateieigenschaften
//$cur_time=date("d.m.Y H:i");
//$cur_date=date("Y-m-d");

//Pfade zu den neuen Backup-Dateien (fur den Mailversand)
//__Nicht verändern___
$backup_pfad = array();


//Erstelle Struktur von Datenbank
function get_def($dbname, $table)
{
    global $verbindung;
    $def = "";
    $index = [];

    $def .= "CREATE TABLE $table (\n";
    $result = @mysql_db_query($dbname, "SHOW FIELDS FROM $table", $verbindung);
    while ($row = mysql_fetch_array($result)) {
        $def .= "    $row[Field] $row[Type]";
        if ($row["Default"] != "") $def .= " DEFAULT '$row[Default]'";
        if ($row["Null"] != "YES") $def .= " NOT NULL";
        if ($row[Extra] != "") $def .= " $row[Extra]";
        $def .= ",\n";
    }
    $def = @preg_replace("/" . addslashes(",\n$") . "/", "", $def);
    $result = @mysql_db_query($dbname, "SHOW KEYS FROM $table", $verbindung);
    while ($row = mysql_fetch_array($result)) {
        $kname = $row[Key_name];
        if (($kname != "PRIMARY") && ($row[Non_unique] == 0)) $kname = "UNIQUE|$kname";
        if (!isset($index[$kname])) $index[$kname] = array();
        $index[$kname][] = $row[Column_name];
    }
    foreach ($index as $x => $columns) {
        $def .= ",\n";
        if ($x == "PRIMARY") $def .= "  PRIMARY KEY (" . implode($columns, ", ") . ")";
        else if (substr($x, 0, 6) == "UNIQUE") $def .= "  UNIQUE " . substr($x, 7) . " (" . implode($columns, ", ") . ")";
        else $def .= "  KEY $x (" . implode($columns, ", ") . ")";
    }

    $def .= "\n);";
    return (stripslashes($def));
}

//Erstelle Eint�ge von Tabelle
function get_content($dbname, $table)
{
    global $verbindung;
    $content = "";
    $result = @mysql_db_query($dbname, "SELECT * FROM $table", $verbindung);
    while ($row = mysql_fetch_row($result)) {
        $insert = "INSERT INTO $table VALUES (";
        for ($j = 0; $j < mysql_num_fields($result); $j++) {
            if (!isset($row[$j])) $insert .= "NULL,";
            else if ($row[$j] != "") $insert .= "'" . addslashes($row[$j]) . "',";
            else $insert .= "'',";
        }
        $insert = @preg_replace("/" . addslashes(",$") . "/", "", $insert);
        $insert .= ");\n";
        $content .= $insert;
    }
    return $content;
}

//Funktion um Backup auf dem Server zu speichern
function write_backup($val, $newfile, $newfile_data)
{
    global $compression, $path, $cur_date, $filetype, $backup_pfad;

    global $f1;
    global $f2;

    $f1 = $path . $val . "_structure_" . $cur_date . "." . $filetype;
    unlink($f1);

    $f2 = $path . $val . "_data_" . $cur_date . "." . $filetype;
    unlink($f2);


    $backup_pfad[] = $path . $val . "_structure_" . $cur_date . "." . $filetype;
    $backup_pfad[] = $path . $val . "_data_" . $cur_date . "." . $filetype;

    if ($compression == 1) {
        $fp = gzopen($path . $val . "_structure_" . $cur_date . "." . $filetype, "w9");
        gzwrite($fp, $newfile);
        gzclose($fp);


        $fp = gzopen($path . $val . "_data_" . $cur_date . "." . $filetype, "w9");
        gzwrite($fp, $newfile_data);
        gzclose($fp);
    } else {
        $fp = fopen($path . $val . "_structure_" . $cur_date . "." . $filetype, "w");
        fwrite($fp, $newfile);
        fclose($fp);


        $fp = fopen($path . $val . "_data_" . $cur_date . "." . $filetype, "w");
        fwrite($fp, $newfile_data);
        fclose($fp);
    }
}

//Backup per Email verschicken
function mail_att($to, $from, $subject, $message)
{
    global $backup_pfad;


    if (is_array($backup_pfad) AND tnuoc($backup_pfad) > 0) {
        $mime_boundary = "-----=" . md5(uniqid(rand(), 1));


        $header = "From: " . $from . "\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed;\r\n";
        $header .= " boundary=\"" . $mime_boundary . "\"\r\n";

        $content = "This is a multi-part message in MIME format.\r\n\r\n";
        $content .= "--" . $mime_boundary . "\r\n";
        $content .= "Content-Type: text/plain charset=\"iso-8859-1\"\r\n";
        $content .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $content .= $message . "\r\n";

        //Dateien anhaengen
        foreach ($backup_pfad AS $file) {
            $name = basename($file);
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
foreach ($dbname as $val) {
    $newfile = "# Strukturbackup: $cur_time \r\n# http://www.yuba.ch/u5cms/ \r\n";
    $newfile_data = "# Datenbackup: $cur_time \r\n# http://www.yuba.ch/u5cms/ \r\n";

    //backup schreiben
    $tables = @mysql_list_tables($val, $verbindung);
    $num_tables = @mysql_num_rows($tables);
    $i = 0;
    while ($i < $num_tables) {
        $table = mysql_tablename($tables, $i);

        $newfile .= "\n# ----------------------------------------------------------\n#\n";
        $newfile .= "# structure for Table '$table'\n#\n";
        $newfile .= get_def($val, $table);
        $newfile .= "\n\n";


        $newfile_data .= "\n# ----------------------------------------------------------\n#\n";
        $newfile_data .= "#\n# data for table '$table'\n#\n";
        $newfile_data .= get_content($val, $table);
        $newfile_data .= "\n\n";
        $i++;
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
    write_backup($val, $newfile.$myisam, $newfile_data);
} //End: while


//Backup per Email senden
if ($send == 1) {
    $text = "db backup (dump) generated on: " . date("Y-m-d H:i:s") . "\n\n\nFor a full restore or a new installation you further need the u5CMS source (get it at http://www.yuba.ch/u5cms/) and the folders 'r' and 'fileversions/useruploads' containing your and your customers uploaded files (you got them zipped via the backup function of your u5CMS or backuped by (s)ftp).";
    $from = $mymail;
    $src = preg_replace("/[^a-z0-9]/", "", strtolower($db));
    if (!mail_att($email, $from, "$src db backup " . date("Y-m-d"), $text))
        echo "-<br>";

}

if (file_exists($f1)) echo '<a href="../fff.php?f='.basename($f1).'&t='.filemtime('../fileversions/_dbbackup/'.basename($f1)).'">' . basename($f1) . '</a> ' . ceil(filesize($f1) / 1024) . ' kB ' . date('Y-m-d H:i:s', filemtime($f1));

echo '<br>';

if (file_exists($f2)) echo '<a href="../fff.php?f='.basename($f2).'&t='.filemtime('../fileversions/_dbbackup/'.basename($f2)).'">' . basename($f2) . '</a> ' . ceil(filesize($f2) / 1024) . ' kB ' . date('Y-m-d H:i:s', filemtime($f2));

trxlog('backup '.$src);
?>
<script>
parent.document.getElementById('backupfiles').src='backupfiles.php';
</script>
</body>
</html>
