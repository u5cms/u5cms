<?php ignore_user_abort(true); require_once('connect.inc.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script src="shortcut.js"></script>
<script>
shortcut.add("Ctrl+S",function() {
if(document.getElementById('sacabu'))if(document.getElementById('sacabu').style.display=='block')saca();
})
</script>
<?php require('backendcss.php'); ?></head>
<body>
<div style="display:none;padding:3px;background:#FF9;font-size:80%;margin-right:20px;margin-bottom:7px" id="lsym"></div>
<div style="display:none">
<?php include('metaiorig.php'); ?>
<iframe name="ametai" src="metai.php?<?php echo $_SERVER['QUERY_STRING'] ?>&typ=a"></iframe>
</div>
<br>
<table id="table">
<script>tov='>>>';</script>
<?php
$txparam='style="display:none;width:98%;min-width:222px;height:22px" onfocus="tov=this.value" onchange="if(this.value.indexOf(\'>>>\')>0)this.value=this.value.replace(/>>>/g,\'>&#8203;>&#8203;>\');if(tov!=this.value){tov=this.value;this.style.background=\'#d3eaff\';if(document.getElementById(\'sacabu\'))document.getElementById(\'sacabu\').style.display=\'block\'}" onkeyup="this.onchange()" onmouseout="if(this===activeElement)this.onchange()" onmouseover="if(this===activeElement)this.onchange()"';
if($_COOKIE['trstat']=='off') {
$remstart='<!--';
$remend='-->';	
}
else {
$remstart='';
$remend='';	
}


$sql_a="SELECT lastmut FROM resources WHERE name='".mysql_real_escape_string($_GET['name'])."'";
$result_a=mysql_query($sql_a);
if ($result_a==false) echo 'SQL_a-Query did not work!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
$row_a = mysql_fetch_array($result_a);
$lastmut=$row_a['lastmut'];

$_GET['name']=basename($_GET['name']);
$totalsizes=0;
$i=0;
$echo='';
if (file_exists('../r/'.$_GET['name'])) {

     $path='../r/'.$_GET['name'];
	 $maxfile=0;    
	 if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 
     
    if (str_replace('.','',$file)!='') {
    $lastfile=explode('_',$file);
	$lastfile=$lastfile[1];
	$lastfile=explode('.',$lastfile);
	$lastfile=$lastfile[0];
	if($lastfile>$maxfile)$maxfile=$lastfile;
	}
	 }}


     if ($handle = @opendir($path))  { 
     while (false !== ($file = readdir($handle)))  { 
     
    if (str_replace('.','',$file)!='') {
	$i++;

	$fileno=explode('_',$file);
	$fileno=$fileno[1];
	$fileno=explode('.',$fileno);
	$fileno=$fileno[0];

$thefilesize=filesize($path.'/'.$file);
$totalsizes+=$thefilesize;
$filesize = round($thefilesize/1024);
$mass='kB';
if ($filesize>1023) {
$mass='MB';
$filesize=round(($filesize/1024*10))/10;
}

////////////////////////////////////////////////////////
if($_GET['resize']==1) {
include('../config.php');
if (($limitjpgsizetobytes>0 && filesize($path.'/'.$file)>$limitjpgsizetobytes) && (strpos($path.'/'.$file,'.jpg')>0 || strpos($path.'/'.$file,'.JPG')>0 || strpos($path.'/'.$file,'.jpeg')>0 || strpos($path.'/'.$file,'.JPEG')>0)) {

include('resizedlongedgepx.inc.php');

// Creating the Canvas 
$tn= imagecreatetruecolor($modwidth, $modheight); 
$source = imagecreatefromjpeg($path.'/'.$file); 

// Resizing our image to fit the canvas 
imagecopyresampled($tn, $source, 0, 0, 0, 0, $modwidth, $modheight, $width, $height); 

// Outputs a jpg image, you could change this to gif or png if needed
unlink($path.'/'.$file);
if(!is_numeric($resamplingquality))$resamplingquality=70;
imagejpeg($tn,$path.'/'.$file,$resamplingquality);  
}
}
////////////////////////////////////////////////////////



                    if ($file[0] != '.') $echo .= '|||<!--' . $file . '--><tr class="trA" id="trA'.$fileno.'" bgcolor="#eeeeee" onmouseover="this.style.background=\'lightyellow\';if(document.getElementById(\'trB'.$fileno.'\'))document.getElementById(\'trB'.$fileno.'\').style.background=\'lightyellow\'" onmouseout="this.style.background=\'#eeeeee\';if(document.getElementById(\'trB'.$fileno.'\'))document.getElementById(\'trB'.$fileno.'\').style.background=\'#eeeeee\'">
<td rowspan="2">
<a target="_blank" href="' . str_replace('r/../r/','r/',('../f.php?f=r/' . $path)) . '/' . $file . '?t=' . filemtime($path . '/' . $file).'&s='.$lastmut . '">
<img class="img" src="../thumb.php?w=100&t=' . filemtime($path . '/' . $file).'&s='.$lastmut . '&f=' . str_replace('../', '', $path) . '/' . $file . '" /></td>
</a>
<td>'.mksel($file,$fileno).'

<div class="div" style="display:none;font-size:60%;background:white;opacity:0.7;text-align:center" onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=0.7">
' . $filesize . $mass . '
<a title="delete" style="text-decoration:none;color:red" href="javascript:void(0)" onclick="if(document.getElementById(\'sacabu\').style.display==\'block\')alert(\'You have to save your changes before resorting.\');else{cf=confirm(\'Do you really want to delete ' . $file . '\');if (cf) location.replace(\'deletefilea.php?name=' . $_GET['name'] . '&f=' . $path . '/' . $file . '\')}">&#10008;</a>
</div>

</td>
<td class="td"><a target="_blank" href="' . str_replace('r/../r/','r/',('../f.php?f=r/' . $path)) . '/' . $file . '?t=' . filemtime($path . '/' . $file).'&s='.$lastmut . '">' . $file . '</a></td>
<td class="td">' . date('Y/m/d H:i', filemtime($path . '/' . $file)) . '</td>
<td class="td">&nbsp;&nbsp;&nbsp;' . $filesize . $mass . '&nbsp;&nbsp;&nbsp;</td>
<td class="td"><a href="javascript:void(0)" onclick="if(document.getElementById(\'sacabu\').style.display==\'block\')alert(\'You have to save your changes before resorting.\');else{cf=confirm(\'Do you really want to delete ' . $file . '\');if (cf) location.replace(\'deletefilea.php?name=' . $_GET['name'] . '&f=' . $path . '/' . $file . '\')}">delete</a></td>
<td class="td">
&nbsp;&nbsp;&nbsp;
<a style="text-decoration:none;font-size:140%;color:blue;" href="javascript:void(0)" onclick="if(document.getElementById(\'sacabu\').style.display==\'block\')alert(\'You have to save your changes before resorting.\');else{parent.albumsort.location.href=\'albumsort.php?d=u&n=' . $_GET['name'] . '&f=' . $file . '\'}">&uarr;</a>
<span style="font-size:1px"> </span>

<a style="text-decoration:none;font-size:140%;color:blue;" href="javascript:void(0)" onclick="if(document.getElementById(\'sacabu\').style.display==\'block\')alert(\'You have to save your changes before resorting.\');else{parent.albumsort.location.href=\'albumsort.php?d=d&n=' . $_GET['name'] . '&f=' . $file . '\'}">&darr;</a>
</td>'.$remstart.'</tr><tr class="trB" id="trB'.$fileno.'" bgcolor="#eeeeee" onmouseover="this.style.background=\'lightyellow\';document.getElementById(\'trA'.$fileno.'\').style.background=\'lightyellow\'" onmouseout="this.style.background=\'#eeeeee\';document.getElementById(\'trA'.$fileno.'\').style.background=\'#eeeeee\'" align="left">'.$remend.'
    <td colspan="6"><span class="tx">
	<textarea lang="'.$lan1na.'" placeholder="'.strtoupper($lan1na).' caption" class="tex txlan1 '.strtoupper($lan1na).'" id="tx1'.$fileno.'" '.$txparam.'></textarea>
	<textarea lang="'.$lan2na.'" placeholder="'.strtoupper($lan2na).' caption" class="tex txlan2 '.strtoupper($lan2na).'" id="tx2'.$fileno.'" '.$txparam.'></textarea>
	<textarea lang="'.$lan3na.'" placeholder="'.strtoupper($lan3na).' caption" class="tex txlan3 '.strtoupper($lan3na).'" id="tx3'.$fileno.'" '.$txparam.'></textarea>
	<textarea lang="'.$lan4na.'" placeholder="'.strtoupper($lan4na).' caption" class="tex txlan4 '.strtoupper($lan4na).'" id="tx4'.$fileno.'" '.$txparam.'></textarea>
	<textarea lang="'.$lan5na.'" placeholder="'.strtoupper($lan5na).' caption" class="tex txlan5 '.strtoupper($lan5na).'" id="tx5'.$fileno.'" '.$txparam.'></textarea>
	</span></td>
  </tr>
';

} }
     }

}
if($i>1)$s='s';
else $s='';
if ($i==0) echo 'none<script>if(parent)if(parent.document.getElementById("i"))parent.document.getElementById("i").innerHTML="Files already uploaded here"</script>';
else echo '<script>if(parent)if(parent.document.getElementById("i"))parent.document.getElementById("i").innerHTML="'.$i.' file'.$s.' already uploaded here"</script>';


$echo=explode('|||',$echo);

rsort($echo);

$echo=implode('',$echo);

echo $echo;

if(trim($lan1name)!='') echo "<script>e=document.getElementsByClassName('txlan1');for(i=0;i<e.length;i++){e[i].style.display='block'}</script>";
if(trim($lan2name)!='') echo "<script>e=document.getElementsByClassName('txlan2');for(i=0;i<e.length;i++){e[i].style.display='block'}</script>";
if(trim($lan3name)!='') echo "<script>e=document.getElementsByClassName('txlan3');for(i=0;i<e.length;i++){e[i].style.display='block'}</script>";
if(trim($lan4name)!='') echo "<script>e=document.getElementsByClassName('txlan4');for(i=0;i<e.length;i++){e[i].style.display='block'}</script>";
if(trim($lan5name)!='') echo "<script>e=document.getElementsByClassName('txlan5');for(i=0;i<e.length;i++){e[i].style.display='block'}</script>";

?>
</table>
<?php if($_GET['resize']==1) { ?>
<iframe name="totalsizes" width="1" height="1" frameborder="0" src="totalsizes.php?name=<?php echo $_GET['name'] ?>&t=<?php echo $totalsizes ?>"></iframe>
<?php } 
else { ?>
<iframe name="resize" width="1" height="1" frameborder="0" src="filesa.php?name=<?php echo $_GET['name'] ?>&resize=1"></iframe>
<?php  } ?>

<?php
function mksel($file,$fileno) {
global $maxfile;	

$return='<select onfocus="selectoldvalue=this.value" onmouseover="this.style.opacity=0.9" onmouseout="this.style.opacity=0.7" class="select" onchange="if(document.getElementById(\'sacabu\').style.display==\'block\'){this.value=selectoldvalue;alert(\'You have to save your changes before resorting.\')}else{parent.albumsort.location.href=\'albumsort.php?t=\'+this.value+\'&n=' . $_GET['name'] . '&f=' . $file . '\'}" '.$selected.' value="'.$i.'" id="i'.$file.'">';	
for($i=$maxfile;$i>99;$i--) {
if($i==$fileno)$selected='selected="selected"';
else $selected='';
$return.='<option '.$selected.'>'.$i.'</option>';	
}
$return.='</select>';
return $return;
}
?>
<input type="hidden" id="pro_1" />
<input type="hidden" id="epi_1" />
<input type="hidden" id="pro_2" />
<input type="hidden" id="epi_2" />
<input type="hidden" id="pro_3" />
<input type="hidden" id="epi_3" />
<input type="hidden" id="pro_4" />
<input type="hidden" id="epi_4" />
<input type="hidden" id="pro_5" />
<input type="hidden" id="epi_5" />

<script>
document.getElementById('pro_1').value=document.form1.content_1.value.split('[ca:]')[0];
document.getElementById('pro_2').value=document.form1.content_2.value.split('[ca:]')[0];
document.getElementById('pro_3').value=document.form1.content_3.value.split('[ca:]')[0];
document.getElementById('pro_4').value=document.form1.content_4.value.split('[ca:]')[0];
document.getElementById('pro_5').value=document.form1.content_5.value.split('[ca:]')[0];

if(document.form1.content_1.value.indexOf('[:ca]')>-1)document.getElementById('epi_1').value=document.form1.content_1.value.split('[:ca]')[1];
if(document.form1.content_2.value.indexOf('[:ca]')>-1)document.getElementById('epi_2').value=document.form1.content_2.value.split('[:ca]')[1];
if(document.form1.content_3.value.indexOf('[:ca]')>-1)document.getElementById('epi_3').value=document.form1.content_3.value.split('[:ca]')[1];
if(document.form1.content_4.value.indexOf('[:ca]')>-1)document.getElementById('epi_4').value=document.form1.content_4.value.split('[:ca]')[1];
if(document.form1.content_5.value.indexOf('[:ca]')>-1)document.getElementById('epi_5').value=document.form1.content_5.value.split('[:ca]')[1];

statics=document.getElementById('pro_1').value.trim()+document.getElementById('pro_2').value.trim()+document.getElementById('pro_3').value.trim()+document.getElementById('pro_4').value.trim()+document.getElementById('pro_5').value.trim();
statics+=document.getElementById('epi_1').value.trim()+document.getElementById('epi_2').value.trim()+document.getElementById('epi_3').value.trim()+document.getElementById('epi_4').value.trim()+document.getElementById('epi_5').value.trim();

fixedparts='Edit the fixed parts of <?php echo htmlentities($_GET['name']) ?>\'s captions in its metadata section (cf. textarea «long caption» there). Access the metadata section by clicking the M link at the top of the page at hand.\n\nNOTICE: If a language contains fixed parts but no per-image-captions, these empty per-image-captions won\'t fall back to filled per-image-captions of another language!\n\nWHAT ARE FIXED PARTS? In the aforementioned metadata section\'s textarea «long caption», per-image-captions are represented as pieces of text between the syntax elements [ca:] and [:ca]. Characters outside of [ca:] ... [:ca] are so called fixed parts: they will be part of the caption of EVERY image of the respective album. If the very first character of the capion is the number sign #, this will be replaced by image number slash total images.';

if(statics.trim()!='')document.write('<small><br>The system detected that your captions contain fixed parts; <a href="javascsript:void(0)" onclick="alert(fixedparts)">please read this info!</a></small>');

if(document.form1.content_1.value.indexOf('[ca]')>-1&&document.form1.content_1.value.indexOf('>>>')>document.form1.content_1.value.indexOf('[ca]')) {
e=document.form1.content_1.value.split('[ca]');
for(i=1;i<e.length;i++) {
if(document.getElementById('tx1'+e[i].split('>>>')[0].replace(/\s/g,'')))document.getElementById('tx1'+e[i].split('>>>')[0].replace(/\s/g,'')).value=e[i].split('>>>')[1].split('[:ca]')[0].trim().substr(0,(e[i].split('>>>')[1].split('[:ca]')[0].trim().length)-3);
}
}
if(document.form1.content_2.value.indexOf('[ca]')>-1&&document.form1.content_2.value.indexOf('>>>')>document.form1.content_2.value.indexOf('[ca]')) {
e=document.form1.content_2.value.split('[ca]');
for(i=1;i<e.length;i++) {
if(document.getElementById('tx2'+e[i].split('>>>')[0].replace(/\s/g,'')))document.getElementById('tx2'+e[i].split('>>>')[0].replace(/\s/g,'')).value=e[i].split('>>>')[1].split('[:ca]')[0].trim().substr(0,(e[i].split('>>>')[1].split('[:ca]')[0].trim().length)-3);
}
}
if(document.form1.content_3.value.indexOf('[ca]')>-1&&document.form1.content_3.value.indexOf('>>>')>document.form1.content_3.value.indexOf('[ca]')) {
e=document.form1.content_3.value.split('[ca]');
for(i=1;i<e.length;i++) {
if(document.getElementById('tx3'+e[i].split('>>>')[0].replace(/\s/g,'')))document.getElementById('tx3'+e[i].split('>>>')[0].replace(/\s/g,'')).value=e[i].split('>>>')[1].split('[:ca]')[0].trim().substr(0,(e[i].split('>>>')[1].split('[:ca]')[0].trim().length)-3);
}
}
if(document.form1.content_4.value.indexOf('[ca]')>-1&&document.form1.content_4.value.indexOf('>>>')>document.form1.content_4.value.indexOf('[ca]')) {
    e=document.form1.content_4.value.split('[ca]');
    for(i=1;i<e.length;i++) {
        if(document.getElementById('tx4'+e[i].split('>>>')[0].replace(/\s/g,'')))document.getElementById('tx4'+e[i].split('>>>')[0].replace(/\s/g,'')).value=e[i].split('>>>')[1].split('[:ca]')[0].trim().substr(0,(e[i].split('>>>')[1].split('[:ca]')[0].trim().length)-3);
    }
}
if(document.form1.content_5.value.indexOf('[ca]')>-1&&document.form1.content_5.value.indexOf('>>>')>document.form1.content_5.value.indexOf('[ca]')) {
    e=document.form1.content_5.value.split('[ca]');
    for(i=1;i<e.length;i++) {
        if(document.getElementById('tx5'+e[i].split('>>>')[0].replace(/\s/g,'')))document.getElementById('tx5'+e[i].split('>>>')[0].replace(/\s/g,'')).value=e[i].split('>>>')[1].split('[:ca]')[0].trim().substr(0,(e[i].split('>>>')[1].split('[:ca]')[0].trim().length)-3);
    }
}

function nety(thatclass) {
ecd='';
e=document.getElementsByClassName(thatclass);
for(i=0;i<e.length;i++) {
ecd+=e[i].value.trim().replace(/\s/g,'');	;	
}
if(ecd.trim()=='') return false;
else return true;
}

function lansymreset() {
document.getElementById('lsym').style.display='none';
e=document.getElementsByClassName('tex');
for(i=0;i<e.length;i++){
if(e[i].value.trim().replace(/\s/g,'')=='')e[i].style.background='white';
else e[i].style.background='#ebfaeb';
}	
}

function lansym() {
ex1='NOT GOOD: Per-image caption translation count is asymmetric! For ';
ex2=' caption-positions are filled than for ';
ex3=' only ';
ex4=' either complete the captions or fully empty  the caption inputs of an (incomplete) language to trigger <a href="javascript:alert(\'Language fallback is not per-image-caption, it is overall, i.e. only if for a language there are no captions at all, the fallback will be triggered towards a/the language for which the captions are filled in.\')">language fallback</a>.';

countlan1='';
if('<?php echo trim($lan1name) ?>'!='') {
e=document.getElementsByClassName('txlan1');	
for(i=0;i<e.length;i++){
if(e[i].value.trim().replace(/\s/g,''))countlan1+=e[i].id.replace(/d/,'');	
}
}

countlan2='';
if('<?php echo trim($lan2name) ?>'!='') {
e=document.getElementsByClassName('txlan2');	
for(i=0;i<e.length;i++){
if(e[i].value.trim().replace(/\s/g,''))countlan2+=e[i].id.replace(/e/,'');		
}
}

countlan3='';
if('<?php echo trim($lan3name) ?>'!='') {
e=document.getElementsByClassName('txlan3');	
for(i=0;i<e.length;i++){
if(e[i].value.trim().replace(/\s/g,''))countlan3+=e[i].id.replace(/f/,'');	
}
}

document.getElementById('lsym').style.display='none';

lanA='<?php echo strtoupper($lan1na) ?>';
lanB='<?php echo strtoupper($lan2na) ?>';
countA=countlan1;
countB=countlan2;
lansymrender(lanA,lanB,countA,countB);

lanA='<?php echo strtoupper($lan1na) ?>';
lanB='<?php echo strtoupper($lan2na) ?>';
countA=countlan1;
countB=countlan3;
lansymrender(lanA,lanB,countA,countB);

lanA='<?php echo strtoupper($lan2na) ?>';
lanB='<?php echo strtoupper($lan1na) ?>';
countA=countlan2;
countB=countlan1;
lansymrender(lanA,lanB,countA,countB);

lanA='<?php echo strtoupper($lan2na) ?>';
lanB='<?php echo strtoupper($lan3na) ?>';
countA=countlan2;
countB=countlan3;
lansymrender(lanA,lanB,countA,countB);

lanA='<?php echo strtoupper($lan3na) ?>';
lanB='<?php echo strtoupper($lan1na) ?>';
countA=countlan3;
countB=countlan1;
lansymrender(lanA,lanB,countA,countB);

lanA='<?php echo strtoupper($lan3na) ?>';
lanB='<?php echo strtoupper($lan2na) ?>';
countA=countlan3;
countB=countlan2;
lansymrender(lanA,lanB,countA,countB);
}

function lansymrender(lanA,lanB,countA,countB) {
if(countB!=''&&countA!=''&&countB!=countA) {
document.getElementById('lsym').innerHTML= ex1+'<b><small>'+lanA+'</small> other </b>'+ex2+'<b><small>'+lanB+'</small></b>;'+ex4;
document.getElementById('lsym').style.display='block';	
e=document.getElementsByClassName(lanA);
for(i=0;i<e.length;i++){
if(e[i].value.trim().replace(/\s/g,'')=='')e[i].style.background='#ffff99';
if(document.getElementsByClassName('<?php echo strtoupper($lan1na)?>')[i].value.trim().replace(/\s/,'')==''&&document.getElementsByClassName('<?php echo strtoupper($lan2na)?>')[i].value.trim().replace(/\s/,'')==''&&document.getElementsByClassName('<?php echo strtoupper($lan3na)?>')[i].value.trim().replace(/\s/,'')=='') e[i].style.background='white';
}
}
}


function sacapre() {
if(document.getElementById('sacabu'))if(document.getElementById('sacabu').style.display=='block')saca();
}

function saca() {
lansymreset();
ametai.form1.content_1.value=document.getElementById('pro_1').value;
if(nety('txlan1')){
ametai.form1.content_1.value=ametai.form1.content_1.value+'[ca:]';
e=document.getElementsByClassName('txlan1');
for(i=0;i<e.length;i++) {
if(i==0)space5='';
else space5='     ';
ametai.form1.content_1.value+=space5+'[ca]'+e[i].id.replace(/tx1/,'')+'>>>'+e[i].value+'[/]'+"\n";
}
ametai.form1.content_1.value=ametai.form1.content_1.value+'[:ca]';
ametai.form1.content_1.value=ametai.form1.content_1.value+document.getElementById('epi_1').value;
}
else ametai.form1.content_1.value=document.getElementById('pro_1').value+document.getElementById('epi_1').value;

///

ametai.form1.content_2.value=document.getElementById('pro_2').value;
if(nety('txlan2')){
ametai.form1.content_2.value=ametai.form1.content_2.value+'[ca:]';
e=document.getElementsByClassName('txlan2');
for(i=0;i<e.length;i++) {
if(i==0)space5='';
else space5='     ';
ametai.form1.content_2.value+=space5+'[ca]'+e[i].id.replace(/tx2/,'')+'>>>'+e[i].value+'[/]'+"\n";
}
ametai.form1.content_2.value=ametai.form1.content_2.value+'[:ca]';
ametai.form1.content_2.value=ametai.form1.content_2.value+document.getElementById('epi_2').value;
}
else ametai.form1.content_2.value=document.getElementById('pro_2').value+document.getElementById('epi_2').value;

///

ametai.form1.content_3.value=document.getElementById('pro_3').value;
if(nety('txlan3')){
ametai.form1.content_3.value=ametai.form1.content_3.value+'[ca:]';
e=document.getElementsByClassName('txlan3');
for(i=0;i<e.length;i++) {
if(i==0)space5='';
else space5='     ';
ametai.form1.content_3.value+=space5+'[ca]'+e[i].id.replace(/tx3/,'')+'>>>'+e[i].value+'[/]'+"\n";
}
ametai.form1.content_3.value=ametai.form1.content_3.value+'[:ca]';
ametai.form1.content_3.value=ametai.form1.content_3.value+document.getElementById('epi_3').value;
}
else ametai.form1.content_3.value=document.getElementById('pro_3').value+document.getElementById('epi_3').value;

///

    ametai.form1.content_4.value=document.getElementById('pro_4').value;
    if(nety('txlan4')){
        ametai.form1.content_4.value=ametai.form1.content_4.value+'[ca:]';
        e=document.getElementsByClassName('txlan4');
        for(i=0;i<e.length;i++) {
            if(i==0)space5='';
            else space5='     ';
            ametai.form1.content_4.value+=space5+'[ca]'+e[i].id.replace(/tx4/,'')+'>>>'+e[i].value+'[/]'+"\n";
        }
        ametai.form1.content_4.value=ametai.form1.content_4.value+'[:ca]';
        ametai.form1.content_4.value=ametai.form1.content_4.value+document.getElementById('epi_4').value;
    }
    else ametai.form1.content_4.value=document.getElementById('pro_4').value+document.getElementById('epi_4').value;
///

    ametai.form1.content_5.value=document.getElementById('pro_5').value;
    if(nety('txlan5')){
        ametai.form1.content_5.value=ametai.form1.content_5.value+'[ca:]';
        e=document.getElementsByClassName('txlan5');
        for(i=0;i<e.length;i++) {
            if(i==0)space5='';
            else space5='     ';
            ametai.form1.content_5.value+=space5+'[ca]'+e[i].id.replace(/tx5/,'')+'>>>'+e[i].value+'[/]'+"\n";
        }
        ametai.form1.content_5.value=ametai.form1.content_5.value+'[:ca]';
        ametai.form1.content_5.value=ametai.form1.content_5.value+document.getElementById('epi_5').value;
    }
    else ametai.form1.content_5.value=document.getElementById('pro_5').value+document.getElementById('epi_5').value;

///
lansym();
ametai.form1.submit();
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = unescape(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function captions(noneblock) {

if(noneblock=='none')setCookie('shwcpt','off',365);
else setCookie('shwcpt','on',365);

e=document.getElementsByClassName('tx');
for(i=0;i<e.length;i++) {
e[i].style.display=noneblock;
}
if(noneblock=='none'){
	hidden='white';
    document.getElementById('Z').style.display='none';
}
else {
	hidden='#eee';
    document.getElementById('Z').style.display='block';
}
e=document.getElementsByClassName('trB');
for(i=0;i<e.length;i++) {
e[i].style.background=hidden;
}
}
toca=-1;

function tr(noneblock) {

if(noneblock=='inline')setCookie('trstat','off',365);
else setCookie('trstat','on',365);

if(noneblock=='inline') {
parent.window.moveTo(0,0);
parent.window.resizeTo(screen.availWidth, screen.availHeight);

e=document.getElementsByClassName('td');
for(i=0;i<e.length;i++) {
e[i].style.display='none';
}

e=document.getElementsByClassName('div');
for(i=0;i<e.length;i++) {
e[i].style.display='block';
}

e=document.getElementsByClassName('trA');
for(i=0;i<e.length;i++) {
e[i].style.verticalAlign='bottom';
e[i].style.background='white';
}

e=document.getElementsByClassName('select');
for(i=0;i<e.length;i++) {
e[i].style.opacity='0.7';
}

e=document.getElementsByClassName('img');
for(i=0;i<e.length;i++) {
e[i].style.marginTop='22px';
e[i].style.borderLeft='11px solid white';
e[i].style.borderBottom='11px solid white';
e[i].style.marginBottom='-16px';
e[i].style.marginRight='-57px';
e[i].style.marginLeft='-7px';
}

document.getElementById('table').style.marginTop='-12px';
}

else parent.location.href=parent.location.href;

e=document.getElementsByTagName('tr');
for(i=0;i<e.length;i++) {
if(noneblock=='inline'){
e[i].style.display='inline';	
}
}
}
trstat=-1;

function tx(that) {
if(that=='big') {
e=document.getElementsByTagName('textarea');
for(i=0;i<e.length;i++) {
e[i].style.height='111px';
}
}
else {
e=document.getElementsByTagName('textarea');
for(i=0;i<e.length;i++) {
e[i].style.height='22px';
}
}
}
txstat=-1;
</script>

<div style="border:1px solid #d3eaff;position:fixed;left:30%;padding:1px;background:#d3eaff;opacity:1;top:-4px;display:none" id="sacabu" onclick="saca()"><button style="width:444px" title="[Ctrl+s]">save<span style="color:red;">*</span><small> [Ctrl<small> </small>+<small> </small>S]</small></button></div>

<div style="position:fixed;left:97%;padding:7px;background:white;opacity:0.9;top:-3px;cursor:pointer;" title="Horizontal expand yes/no" onclick="if(document.getElementById('sacabu'))if(document.getElementById('sacabu').style.display=='block')alert('You have to save your changes before using the Horizontal expand switch.');else{trstat=trstat*-1;if(trstat==1){setCookie('trstat','off',365);location.href=location.href;}else tr('block')}">&#8461;</div>

<div style="position:fixed;left:97%;padding:7px;background:white;opacity:0.9;top:28px;cursor:pointer;" title="Caption input hide/show" onclick="toca=toca*-1;if(toca==1)captions('none');else captions('block')">&#8450;</div>

<div id="Z" style="position:fixed;left:97%;padding:7px;background:white;opacity:0.9;top:56px;cursor:pointer;" title="Zoom textarea yes/no" onclick="txstat=txstat*-1;if(txstat==1)tx('big');else tx('small')">&#8484;</div>


<script>
window.onbeforeunload = function() {
if(document.getElementById('sacabu'))if(document.getElementById('sacabu').style.display=='block'){self.focus();return('You have unsaved changes which will be lost if you are proceeding.');}
}

if(getCookie('shwcpt')=='off'){toca=toca*-1;captions('none')};
if(getCookie('trstat')=='off'){trstat=trstat*-1;tr('inline');};

window.onload=function readScroll()
{
 var hscroll2 = readCookie('scrollpos2h');
 var vscroll2 = readCookie('scrollpos2v');
 window.scrollTo(hscroll2,vscroll2);
}

function createCookie(name,value,days) {
 if (days) {
  var date = new Date();
  date.setTime(date.getTime()+(days*24*60*60*1000));
  var expires = "; expires="+date.toGMTString();
 }
 else var expires = "";
 document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
 var nameEQ = name + "=";
 var ca = document.cookie.split(';');
 for(var i=0;i < ca.length;i++) {
  var c = ca[i];
  while (c.charAt(0)==' ') c = c.substring(1,c.length);
  if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
 }
 return null;
}

function eraseCookie(name) {
 createCookie(name,"",-1);
}

function f_scrollLeft() {
 return f_filterResults (
  window.pageXOffset ? window.pageXOffset : 0,
  document.documentElement ? document.documentElement.scrollLeft : 0,
  document.body ? document.body.scrollLeft : 0
 );
}
function f_scrollTop() {
 return f_filterResults (
  window.pageYOffset ? window.pageYOffset : 0,
  document.documentElement ? document.documentElement.scrollTop : 0,
  document.body ? document.body.scrollTop : 0
 );
}
function f_filterResults(n_win, n_docel, n_body) {
 var n_result = n_win ? n_win : 0;
 if (n_docel && (!n_result || (n_result > n_docel)))
  n_result = n_docel;
 return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
}

window.onscroll=function saveScroll()
{
    var hscroll = 0, vscroll = 0;
   
 if( typeof( window.pageYOffset ) == 'number' ) {
     //Netscape compliant
     vscroll = window.pageYOffset;
     hscroll = window.pageXOffset;
    } else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
     //DOM compliant
     vscroll = document.body.scrollTop;
     hscroll = document.body.scrollLeft;
    } else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
     //IE6 standards compliant mode
     vscroll = document.documentElement.scrollTop;
     hscroll = document.documentElement.scrollLeft;
 }
 createCookie('scrollpos2h',hscroll,1);
 createCookie('scrollpos2v',vscroll,1);
}
lansymreset();
lansym();
</script>
<br><br><br><br><br><br><br>
</body>
</html>