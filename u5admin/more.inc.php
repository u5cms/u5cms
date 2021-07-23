<span style="<?php echo $cssbackendspecialchars ?>">
<div style="background:#eee;width:98%;height:122px;margin:3px 3px 3px 0px;position:absolute;top:0;display:none" id="ut8div<?php echo $lanhere ?>">
<div style="float:right;color:white;background:black;padding:1px;cursor:pointer;height:20px;padding-top:100px" onclick="document.getElementById('ut8div<?php echo $lanhere ?>').style.display='none'" title="close">x</div>
<iframe height="120" width="97%" id="mutf8<?php echo $lanhere ?>" frameborder="0"></iframe>
</div>

<?php
$xc=array
('%u202F+'                       ,'%u2013+' ,'%B2%B3+'    ,'%u201E%u201C%u201D %u201A%u2018%u2019','%u00AB%u202F_%u202F%u00BB %u2039%u202F_%u202F%u203A','%BD+'        ,'%u0153+' ,'%u2192+','%B1+');
$xt=array
('narrow non-breaking space'      ,'en dash' ,'2 sup 3 sup', 'quotation marks'                     ,'guillemets'                                         , '1/2'        ,'oe ligature','rightward arrow','plus or minus',);


$noshow1=array('+','%u202F_%u202F',' ','%u202F');
$noshow2=array('','','','ns');

for ($xi=0;$xi<count($xc);$xi++) {
$mu8='';
if (strpos($xc[$xi],'+')>0) $mu8='&nbsp;<span title="simile" style="font-size:80%;color:white;background:black;cursor:pointer" onclick="document.getElementById(\'mutf8'.$lanhere.'\').src=\'chr.php?l='.$lanhere.'&c='.$xc[$xi].'\';document.getElementById(\'ut8div'.$lanhere.'\').style.display=\'block\';">+</span>';
echo '<a style="cursor:pointer;color:blue" title="'.$xt[$xi].'" onclick="doins(\''.$lanhere.'\',\''.str_replace('+','',$xc[$xi]).'\')"><script>document.write(unescape(\''.str_replace($noshow1,$noshow2,$xc[$xi]).'\'))</script></a>'.$mu8;
echo '<span style="font-size:90%"> </span><span style="color:white;font-weight:bold">|</span><span style="font-size:90%"> </span>'; 
}
echo '<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.$_COOKIE['mc1'].'\')"><script>document.write(unescape(\''.$_COOKIE['mc1'].'\'))</script></a>';
echo ' <span style="color:white;font-weight:bold">|</span> ';
echo '<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.$_COOKIE['mc2'].'\')"><script>document.write(unescape(\''.$_COOKIE['mc2'].'\'))</script></a>';
echo ' <span style="color:white;font-weight:bold">|</span> ';
echo '<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.$_COOKIE['mc3'].'\')"><script>document.write(unescape(\''.$_COOKIE['mc3'].'\'))</script></a>';
echo ' <span style="color:white;font-weight:bold">|</span> ';
echo '<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.$_COOKIE['mc4'].'\')"><script>document.write(unescape(\''.$_COOKIE['mc4'].'\'))</script></a>';
echo ' <span style="color:white;font-weight:bold">|</span> ';
echo '<a style="cursor:pointer;color:blue" onclick="doins(\''.$lanhere.'\',\''.$_COOKIE['mc5'].'\')"><script>document.write(unescape(\''.$_COOKIE['mc5'].'\'))</script></a>';
echo ' <span style="color:white;font-weight:bold">|</span> ';
?>
<a href="javascript:void(0)" onclick="u1=window.open('characters.php?w='+window.outerWidth+'&h='+window.outerHeight,'_blank','toolbar=0,location=0,status=1,screenX=0,screenY=0,top=0,left=0,menubar=0,scrollbars=1,resizable=1,width=640,height=640');" title="[Ctrl+m] show more special characters">more</a>

<script>
function putdoins<?php echo $lanhere ?>(putdstr) {
doins('<?php echo $lanhere ?>',putdstr);
}
</script>

</span>