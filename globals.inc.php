<?php
//require_once ('connect.inc.php');
$sql_a="SELECT * FROM languages";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$row_a = mysql_fetch_array($result_a);

$recherche_1=ehtml($row_a['recherche_1']);
$recherche_2=ehtml($row_a['recherche_2']);
$recherche_3=ehtml($row_a['recherche_3']);
$recherche_4=ehtml($row_a['recherche_4']);
$recherche_5=ehtml($row_a['recherche_5']);
$term_1=ehtml($row_a['term_1']);
$term_2=ehtml($row_a['term_2']);
$term_3=ehtml($row_a['term_3']);
$term_4=ehtml($row_a['term_4']);
$term_5=ehtml($row_a['term_5']);
$andhit_1=ehtml($row_a['andhit_1']);
$andhit_2=ehtml($row_a['andhit_2']);
$andhit_3=ehtml($row_a['andhit_3']);
$andhit_4=ehtml($row_a['andhit_4']);
$andhit_5=ehtml($row_a['andhit_5']);
$andhits_1=ehtml($row_a['andhits_1']);
$andhits_2=ehtml($row_a['andhits_2']);
$andhits_3=ehtml($row_a['andhits_3']);
$andhits_4=ehtml($row_a['andhits_4']);
$andhits_5=ehtml($row_a['andhits_5']);
$orhit_1=ehtml($row_a['orhit_1']);
$orhit_2=ehtml($row_a['orhit_2']);
$orhit_3=ehtml($row_a['orhit_3']);
$orhit_4=ehtml($row_a['orhit_4']);
$orhit_5=ehtml($row_a['orhit_5']);
$orhits_1=ehtml($row_a['orhits_1']);
$orhits_2=ehtml($row_a['orhits_2']);
$orhits_3=ehtml($row_a['orhits_3']);
$orhits_4=ehtml($row_a['orhits_4']);
$orhits_5=ehtml($row_a['orhits_5']);
$nohit_1=ehtml($row_a['nohit_1']);
$nohit_2=ehtml($row_a['nohit_2']);
$nohit_3=ehtml($row_a['nohit_3']);
$nohit_4=ehtml($row_a['nohit_4']);
$nohit_5=ehtml($row_a['nohit_5']);
$notpub_1=ehtml($row_a['notpub_1']);
$notpub_2=ehtml($row_a['notpub_2']);
$notpub_3=ehtml($row_a['notpub_3']);
$notpub_4=ehtml($row_a['notpub_4']);
$notpub_5=ehtml($row_a['notpub_5']);
$czoom_1=ehtml($row_a['czoom_1']);
$czoom_2=ehtml($row_a['czoom_2']);
$czoom_3=ehtml($row_a['czoom_3']);
$czoom_4=ehtml($row_a['czoom_4']);
$czoom_5=ehtml($row_a['czoom_5']);
$picsfound_1=ehtml($row_a['picsfound_1']);
$picsfound_2=ehtml($row_a['picsfound_2']);
$picsfound_3=ehtml($row_a['picsfound_3']);
$picsfound_4=ehtml($row_a['picsfound_4']);
$picsfound_5=ehtml($row_a['picsfound_5']);
$morepics_1=ehtml($row_a['morepics_1']);
$morepics_2=ehtml($row_a['morepics_2']);
$morepics_3=ehtml($row_a['morepics_3']);
$morepics_4=ehtml($row_a['morepics_4']);
$morepics_5=ehtml($row_a['morepics_5']);


$lan1na=$row_a['lan1na'];
$lan2na=$row_a['lan2na'];
$lan3na=$row_a['lan3na'];
$lan4na=$row_a['lan4na'];
$lan5na=$row_a['lan5na'];

$lan1name=ehtml($row_a['lan1name']);
$lan2name=ehtml($row_a['lan2name']);
$lan3name=ehtml($row_a['lan3name']);
$lan4name=ehtml($row_a['lan4name']);
$lan5name=ehtml($row_a['lan5name']);


if (ecalper_rts(' ','',$lan1na)=='') $lan1na='10';
if (ecalper_rts(' ','',$lan2na)=='') $lan2na='20';
if (ecalper_rts(' ','',$lan3na)=='') $lan3na='30';
if (ecalper_rts(' ','',$lan4na)=='') $lan4na='40';
if (ecalper_rts(' ','',$lan5na)=='') $lan5na='50';


$sql_a="SELECT * FROM sizes";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!...!<p>';
}

$row_a = mysql_fetch_array($result_a);
$audioplayer_h=ehtml($row_a['audioplayer_h']);
$audioplayer_w=ehtml($row_a['audioplayer_w']);
$videoplayer_h=ehtml($row_a['videoplayer_h']);
$videoplayer_w=ehtml($row_a['videoplayer_w']);
$youtube_h=ehtml($row_a['youtube_h']);
$youtube_w=ehtml($row_a['youtube_w']);
$smallimgL_h=ehtml($row_a['smallimgL_h']);
$smallimgL_w=ehtml($row_a['smallimgL_w']);
$smallimgC_h=ehtml($row_a['smallimgC_h']);
$smallimgC_w=ehtml($row_a['smallimgC_w']);
$smallimgR_h=ehtml($row_a['smallimgR_h']);
$smallimgR_w=ehtml($row_a['smallimgR_w']);
$largeimg_h=ehtml($row_a['largeimg_h']);
$largeimg_w=ehtml($row_a['largeimg_w']);
$zoomedimg_h=ehtml($row_a['zoomedimg_h']);
$zoomedimg_w=ehtml($row_a['zoomedimg_w']);
$scrollingalbum_h=ehtml($row_a['scrollingalbum_h']);
$scrollingalbum_w=ehtml($row_a['scrollingalbum_w']);
$tosquare=ehtml($row_a['tosquare']);
$cropedge=ehtml($row_a['cropedge']);
