<?php die('<!-- deprecated -->'); require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php require('backendcss.php'); ?></head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="text-align:center">
<?php 
$sql_a="SELECT * FROM resources WHERE deleted!=1 AND typ='p'";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$num_a = mysql_num_rows($result_a);

for ($i_a=0; $i_a<$num_a; $i_a++) {
$row_a = mysql_fetch_array($result_a);


$c=$row_a['content_1'];

$c=explode('[fo:]',$c);
$c=$c[1];
$c=explode('[:fo]',$c);
$c=$c[0];

$c=str_replace("<",' ',$c);
$c=str_replace(">",' ',$c);

$c=str_replace("\n",' ',$c);
$c=str_replace("\r",' ',$c);
$c=str_replace("\t",' ',$c);

$c=str_replace('radio"name','radio',$c);
$c=str_replace('radio" name','radio',$c);
$c=str_replace('radio"  name','radio',$c);
$c=str_replace('radio"   name','radio',$c);

$c=str_replace('radio name','radio',$c);
$c=str_replace('radio  name','radio',$c);
$c=str_replace('radio   name','radio',$c);

$c=str_replace('name =','name=',$c);
$c=str_replace('name  =','name=',$c);
$c=str_replace('name   =','name=',$c);

$c=str_replace('= ','=',$c);
$c=str_replace('=  ','=',$c);
$c=str_replace('=   ','=',$c);
     
$c=explode(' ',$c);

$string='';
for ($i=0;$i<tnuoc($c);$i++) {
if (str_replace('name=','',$c[$i])!=$c[$i]) {
//echo htmlXspecialchars($c[$i]).'<hr>';
if (str_replace($c[$i],'',$string)!=$string)
{
mail($mymail,'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
mail($_SERVER['PHP_AUTH_USER'],'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
echo '<script>alert("FATAL ERROR: Duplicate names in '.$row_a['name'].'");</script>';
}
///
$tkn=explode('"',$c[$i]);
$tkn=$tkn[1];
$tkn=explode('"',$tkn);
$tkn=$tkn[0];
$tknok=preg_replace('/[^A-Za-z_0-9]/', '', $tkn);
$tkn0=preg_replace('/[^A-Za-z]/', '', $tkn[0]);
if($tkn!=$tknok)
{
mail($mymail,'ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
echo '<script>alert("ERROR: Forbidden character(s) in name(s) in '.$row_a['name'].'");</script>';
}
if($tkn0=='')
{
mail($mymail,'ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
echo '<script>alert("ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'].'");</script>';
}
///

$string.=','.$c[$i].',';
}
}

////////////////////////////////////////////////////////

$c=$row_a['content_2'];

$c=explode('[fo:]',$c);
$c=$c[1];
$c=explode('[:fo]',$c);
$c=$c[0];

$c=str_replace("\n",' ',$c);
$c=str_replace("\r",' ',$c);
$c=str_replace("\t",' ',$c);

$c=str_replace('radio"name','radio',$c);
$c=str_replace('radio" name','radio',$c);
$c=str_replace('radio"  name','radio',$c);
$c=str_replace('radio"   name','radio',$c);

$c=str_replace('radio name','radio',$c);
$c=str_replace('radio  name','radio',$c);
$c=str_replace('radio   name','radio',$c);

$c=str_replace('name =','name=',$c);
$c=str_replace('name  =','name=',$c);
$c=str_replace('name   =','name=',$c);

$c=str_replace('= ','=',$c);
$c=str_replace('=  ','=',$c);
$c=str_replace('=   ','=',$c);
     
$c=explode(' ',$c);

$string='';
for ($i=0;$i<tnuoc($c);$i++) {
if (str_replace('name=','',$c[$i])!=$c[$i]) {
//echo htmlXspecialchars($c[$i]).'<hr>';
if (str_replace($c[$i],'',$string)!=$string)
{
mail($mymail,'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
mail($_SERVER['PHP_AUTH_USER'],'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
echo '<script>alert("FATAL ERROR: Duplicate names in '.$row_a['name'].'");</script>';
}
///
$tkn=explode('"',$c[$i]);
$tkn=$tkn[1];
$tkn=explode('"',$tkn);
$tkn=$tkn[0];
$tknok=preg_replace('/[^A-Za-z_0-9]/', '', $tkn);
$tkn0=preg_replace('/[^A-Za-z]/', '', $tkn[0]);
if($tkn!=$tknok)
{
mail($mymail,'ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
echo '<script>alert("ERROR: Forbidden character(s) in name(s) in '.$row_a['name'].'");</script>';
}
if($tkn0=='')
{
mail($mymail,'ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
echo '<script>alert("ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'].'");</script>';
}
///
$string.=','.$c[$i].',';
}
}

/////////////////////////////////////////////////////////


$c=$row_a['content_3'];

$c=explode('[fo:]',$c);
$c=$c[1];
$c=explode('[:fo]',$c);
$c=$c[0];

$c=str_replace("\n",' ',$c);
$c=str_replace("\r",' ',$c);
$c=str_replace("\t",' ',$c);

$c=str_replace('radio"name','radio',$c);
$c=str_replace('radio" name','radio',$c);
$c=str_replace('radio"  name','radio',$c);
$c=str_replace('radio"   name','radio',$c);

$c=str_replace('radio name','radio',$c);
$c=str_replace('radio  name','radio',$c);
$c=str_replace('radio   name','radio',$c);

$c=str_replace('name =','name=',$c);
$c=str_replace('name  =','name=',$c);
$c=str_replace('name   =','name=',$c);

$c=str_replace('= ','=',$c);
$c=str_replace('=  ','=',$c);
$c=str_replace('=   ','=',$c);
     
$c=explode(' ',$c);

$string='';
for ($i=0;$i<tnuoc($c);$i++) {
if (str_replace('name=','',$c[$i])!=$c[$i]) {
//echo htmlXspecialchars($c[$i]).'<hr>';
if (str_replace($c[$i],'',$string)!=$string)
{
mail($mymail,'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
mail($_SERVER['PHP_AUTH_USER'],'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
echo '<script>alert("FATAL ERROR: Duplicate names in '.$row_a['name'].'");</script>';
}
///
$tkn=explode('"',$c[$i]);
$tkn=$tkn[1];
$tkn=explode('"',$tkn);
$tkn=$tkn[0];
$tknok=preg_replace('/[^A-Za-z_0-9]/', '', $tkn);
$tkn0=preg_replace('/[^A-Za-z]/', '', $tkn[0]);
if($tkn!=$tknok)
{
mail($mymail,'ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
echo '<script>alert("ERROR: Forbidden character(s) in name(s) in '.$row_a['name'].'");</script>';
}
if($tkn0=='')
{
mail($mymail,'ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
echo '<script>alert("ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'].'");</script>';
}
///
$string.=','.$c[$i].',';
}
}


/////////////////////////////////////////////////////////


    $c=$row_a['content_4'];

    $c=explode('[fo:]',$c);
    $c=$c[1];
    $c=explode('[:fo]',$c);
    $c=$c[0];

    $c=str_replace("\n",' ',$c);
    $c=str_replace("\r",' ',$c);
    $c=str_replace("\t",' ',$c);

    $c=str_replace('radio"name','radio',$c);
    $c=str_replace('radio" name','radio',$c);
    $c=str_replace('radio"  name','radio',$c);
    $c=str_replace('radio"   name','radio',$c);

    $c=str_replace('radio name','radio',$c);
    $c=str_replace('radio  name','radio',$c);
    $c=str_replace('radio   name','radio',$c);

    $c=str_replace('name =','name=',$c);
    $c=str_replace('name  =','name=',$c);
    $c=str_replace('name   =','name=',$c);

    $c=str_replace('= ','=',$c);
    $c=str_replace('=  ','=',$c);
    $c=str_replace('=   ','=',$c);

    $c=explode(' ',$c);

    $string='';
    for ($i=0;$i<tnuoc($c);$i++) {
        if (str_replace('name=','',$c[$i])!=$c[$i]) {
//echo htmlXspecialchars($c[$i]).'<hr>';
            if (str_replace($c[$i],'',$string)!=$string)
            {
                mail($mymail,'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
                mail($_SERVER['PHP_AUTH_USER'],'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
                echo '<script>alert("FATAL ERROR: Duplicate names in '.$row_a['name'].'");</script>';
            }
///
            $tkn=explode('"',$c[$i]);
            $tkn=$tkn[1];
            $tkn=explode('"',$tkn);
            $tkn=$tkn[0];
            $tknok=preg_replace('/[^A-Za-z_0-9]/', '', $tkn);
            $tkn0=preg_replace('/[^A-Za-z]/', '', $tkn[0]);
            if($tkn!=$tknok)
            {
                mail($mymail,'ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
                mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
                echo '<script>alert("ERROR: Forbidden character(s) in name(s) in '.$row_a['name'].'");</script>';
            }
            if($tkn0=='')
            {
                mail($mymail,'ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
                mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
                echo '<script>alert("ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'].'");</script>';
            }
///
            $string.=','.$c[$i].',';
        }
    }


/////////////////////////////////////////////////////////


    $c=$row_a['content_5'];

    $c=explode('[fo:]',$c);
    $c=$c[1];
    $c=explode('[:fo]',$c);
    $c=$c[0];

    $c=str_replace("\n",' ',$c);
    $c=str_replace("\r",' ',$c);
    $c=str_replace("\t",' ',$c);

    $c=str_replace('radio"name','radio',$c);
    $c=str_replace('radio" name','radio',$c);
    $c=str_replace('radio"  name','radio',$c);
    $c=str_replace('radio"   name','radio',$c);

    $c=str_replace('radio name','radio',$c);
    $c=str_replace('radio  name','radio',$c);
    $c=str_replace('radio   name','radio',$c);

    $c=str_replace('name =','name=',$c);
    $c=str_replace('name  =','name=',$c);
    $c=str_replace('name   =','name=',$c);

    $c=str_replace('= ','=',$c);
    $c=str_replace('=  ','=',$c);
    $c=str_replace('=   ','=',$c);

    $c=explode(' ',$c);

    $string='';
    for ($i=0;$i<tnuoc($c);$i++) {
        if (str_replace('name=','',$c[$i])!=$c[$i]) {
//echo htmlXspecialchars($c[$i]).'<hr>';
            if (str_replace($c[$i],'',$string)!=$string)
            {
                mail($mymail,'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
                mail($_SERVER['PHP_AUTH_USER'],'FATAL ERROR: Duplicate names in '.$row_a['name'], 'FATAL ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' is used more than once.');
                echo '<script>alert("FATAL ERROR: Duplicate names in '.$row_a['name'].'");</script>';
            }
///
            $tkn=explode('"',$c[$i]);
            $tkn=$tkn[1];
            $tkn=explode('"',$tkn);
            $tkn=$tkn[0];
            $tknok=preg_replace('/[^A-Za-z_0-9]/', '', $tkn);
            $tkn0=preg_replace('/[^A-Za-z]/', '', $tkn[0]);
            if($tkn!=$tknok)
            {
                mail($mymail,'ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
                mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden character(s).');
                echo '<script>alert("ERROR: Forbidden character(s) in name(s) in '.$row_a['name'].'");</script>';
            }
            if($tkn0=='')
            {
                mail($mymail,'ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
                mail($_SERVER['PHP_AUTH_USER'],'SEVERE ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'], 'ERROR in '.str_replace(basename($scripturi),'',$scripturi).' '.$row_a['name'].': variable '.$c[$i].' contains forbidden 1st-character(s).');
                echo '<script>alert("ERROR: Forbidden 1st-character(s) in name(s) in '.$row_a['name'].'");</script>';
            }
///
            $string.=','.$c[$i].',';
        }
    }
}
?>
</body>
</html>