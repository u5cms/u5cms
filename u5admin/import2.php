<?php require('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Import 2</title>
<?php require('backendcss.php'); ?></head>
<body>
<?php
if (file_put_contents('../fileversions/x','x')) {
echo '<!--w ../r/ ok -->';
unlink('../fileversions/x');
}
else echo '<script>alert("PROBLEM: The server does not have the right to write into the folder named \'fileversions\'.\n\nEFFECTS: Data import not possible.\n\nSOLUTION: CHMOD this folder RECURSIVELY (incl. all its files, subfolders a.s.o.) e. g. to 777 e. g. with FileZilla.");</script>';

$filename=sha1($csv).'.csv';
$csv=str_replace(';',',.',$_POST['csv']);
file_put_contents('../fileversions/'.$filename,$csv);

function subone(&$item1) {
$item1=str_replace("\r","THS!S!M!HL!PRVT!LNBRK",str_replace("\n","THS!S!M!HL!PRVT!LNBRK",str_replace("\r\n","THS!S!M!HL!PRVT!LNBRK",$item1)));
}

$importer = new CsvImporter("../fileversions/$filename",true);
$fp = fopen('../fileversions/_'.$filename, 'w');
while($data = $importer->get())
{
array_walk($data,subone);
$for=0;
foreach ($data as $fields) {
if($for==0){
$keys=array_keys($fields);
}
$for++;
fputcsv($fp, $fields,';');
}

}
fclose($fp);

array_walk($keys,nice);
$keys=implode(';',$keys);
$keys=$keys.';';

function nice(&$item1) {
$item1=clean($item1);
}

function clean($string) {
   $string = str_replace(' ', '', $string); 
   $string=str_replace('*','_mandatory',$string);
   
$badascii = array(" ",":","¦","|","@" ,"&","¢","£","¤","¥","€","§","¨","©","ª","¬","®","¯","°","±","´","µ","¶","…","·","¸","º","÷","†","ß" ,"„","“","”","‚","‘","’","«","»","‹","›","¡","¿","–","À","Á","Â","Ã","Ä" ,"Å","Æ" ,"Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ñ","Ò","Ó","Ô","Õ","#","Ö", "Ø","Œ" ,"Ù","Ú","Û","Ü", "à","á","â","ã","ä", "å","æ" ,"ç","è","é","ê","ë","ì","í","î","ï","ñ","ò","ó","ô","õ","#","ö", "ø","œ", "ù","ú","û","ü", "ÿ","'","%","/","\\","<",">",   ",",   ";","+","=","(",")","[","]","{","}","*","?","!","`","\"");
$goodascii= array("_","", "", "", "AT","u","c","L","", "Y","E","S","","c","a","N","R","", "o","pm","", "u","P","", "", "", "o","", "", "ss","", "", "", "", "", "", "", "", "", "", "", "", "", "A","A","A","A","Ae","A","Ae","C","E","E","E","E","I","I","I","I","N","O","O","O","O","","Oe","O","Oe","U","U","U","Ue","a","a","a","a","ae","a","ae","c","e","e","e","e","i","i","i","i","n","o","o","o","o","","oe","o","oe","u","u","u","ue","y","", "", "", "",  "", "",     "",    "","u","", "", "", "", "", "", "", "x","", "", "", "_");
$string=str_replace($badascii,$goodascii,$string);
   
   $string = preg_replace('/[^A-Za-z_0-9]/', '', $string); // Removes special chars.
   //$string=str_replace('mandatory','_mandatory',$string);
   if(is_numeric($string[0])) $string='i'.$string;
   return '·'.$string;
}

$sql_a="SELECT headcsv FROM formdata WHERE formname='".mysql_real_escape_string($_POST['target'])."' AND status!=5 AND status!=7 ORDER BY status DESC, id DESC LIMIT 0,1";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

$row_a = mysql_fetch_array($result_a);

$headcsv=$row_a['headcsv'];

$num_a = mysql_num_rows($result_a);

if($num_a==0 || $_POST['h']==2) $headcsv=$keys;
?>




<?php
class CsvImporter
{
    private $fp;
    private $parse_header;
    private $header;
    private $delimiter;
    private $length;
    //--------------------------------------------------------------------
    function __construct($file_name, $parse_header=false, $delimiter="\t", $length=8000)
    {
        $this->fp = fopen($file_name, "r");
        $this->parse_header = $parse_header;
        $this->delimiter = $delimiter;
        $this->length = $length;
        $this->lines = $lines;

        if ($this->parse_header)
        {
           $this->header = fgetcsv($this->fp, $this->length, $this->delimiter);
        }

    }
    //--------------------------------------------------------------------
    function __destruct()
    {
        if ($this->fp)
        {
            fclose($this->fp);
        }
    }
    //--------------------------------------------------------------------
    function get($max_lines=0)
    {
        //if $max_lines is set to 0, then get all the data

        $data = array();

        if ($max_lines > 0)
            $line_count = 0;
        else
            $line_count = -1; // so loop limit is ignored

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter)) !== FALSE)
        {
            if ($this->parse_header)
            {
                foreach ($this->header as $i => $heading_i)
                {
                    $row_new[$heading_i] = $row[$i];
                }
                $data[] = $row_new;
            }
            else
            {
                $data[] = $row;
            }

            if ($max_lines > 0)
                $line_count++;
        }
        return $data;
    }
    //--------------------------------------------------------------------

}

?>

<?php
$c=trim(file_get_contents("../fileversions/_".$filename));
$c=str_replace("\r","",$c);
$c=explode("\n",$c);

$m=$_POST['m'];
$n=$_POST['n'];

for($i=0;$i<tnuoc($c);$i++) {
$d=explode(';',$c[$i]);
$h=explode(';',$headcsv);

//var_dump( $headcsv).'<hr>';

$row='';
$notes='';
$authuser='';
$sent='';
$ip='';

if($m==1) {
$notes=$_POST['fixnotes'];
$authuser=$_POST['fixauthuser'];
$start=0;
}

if($m==2) {
$notes=$d[0];
$authuser=$_POST['fixauthuser'];
$start=1;
unset($h[0]);
}

if($m==3) {
$notes=$d[0];
$authuser=$d[1];
$start=2;
unset($h[0]);
unset($h[1]);
}

if($n==2) {
$end=2;
$sent=$d[tnuoc($d)-2];
$ip=$d[tnuoc($d)-1];
unset($h[tnuoc($h)]);
unset($h[tnuoc($h)]);
}

else {
$end=0;
$sent=date('Y.m.d H:i:s');
$ip=$_SERVER['REMOTE_ADDR'].' '.$_SERVER['AUTH_USER'].' imprtd';
}

$h=implode(';',$h);


$datacsv='';
for($ii=$start;$ii<tnuoc($d)-$end;$ii++) {
////////////////////////////////////////
//echo $h;
$datacsv.=str_replace("THS!S!M!HL!PRVT!LNBRK","\r\n",'·'.$d[$ii]).';';
//echo $d;
////////////////////////////////////////
}
///


$datacsv=explode(';',$datacsv);
for($iii=0;$iii<tnuoc($datacsv);$iii++) {
if ($datacsv[$iii][1]=='"') {
$datacsv[$iii]='·'.substr($datacsv[$iii],2);
if ($datacsv[$iii][strlen($datacsv[$iii])-1]=='"') $datacsv[$iii]=substr($datacsv[$iii],0,-1);
}
}
$datacsv=implode(';',$datacsv);


$formname=mysql_real_escape_string($_POST['target']);
$myheadcsv=mysql_real_escape_string($h);
$datacsv=mysql_real_escape_string($datacsv);
$time=mysql_real_escape_string(mystrtotime(str_replace('THS!S!M!HL!PRVT!LNBRK'," ",$sent)));
$ip=mysql_real_escape_string(str_replace('THS!S!M!HL!PRVT!LNBRK',' ',$ip));
$status=mysql_real_escape_string(7);
$notes=mysql_real_escape_string(str_replace('THS!S!M!HL!PRVT!LNBRK',"\r\n",$notes));
$lastmut=mysql_real_escape_string(0);
$authuser=mysql_real_escape_string(str_replace('THS!S!M!HL!PRVT!LNBRK'," ",$authuser));
$humantime=mysql_real_escape_string(str_replace('THS!S!M!HL!PRVT!LNBRK'," ",$sent));

$sql_a="INSERT INTO formdata (formname,headcsv,datacsv,time,ip,status,notes,lastmut,authuser,humantime) VALUES ('$formname','$myheadcsv','$datacsv','$time','$ip','$status','$notes','$lastmut','$authuser','$humantime')";
$result_a=mysql_query($sql_a);

if ($result_a==false) echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';

///

//	echo '<hr>';
}


function mystrtotime($t) {
$d=explode(' ',$t);
$t=$d[1];
$d=$d[0];

$d=explode('.',$d);
$t=explode(':',$t);

return mktime($t[0],$t[1],$t[2],$d[1],$d[2],$d[0]);

}

unlink("../fileversions/$filename");
unlink("../fileversions/_".$filename);

trxlog('impcsv '.$_POST['target']);
?>
<script>location.replace('formdata2.php?n=<?php echo $_POST['target']?>&s=7');</script>
</body>
</html>