<?php 
//require_once ('connect.inc.php');
$sql_a="SELECT * FROM languages";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
}

$row_a = mysql_fetch_array($result_a);

$recherche_d=ehtml($row_a['recherche_d']);
$recherche_e=ehtml($row_a['recherche_e']);
$recherche_f=ehtml($row_a['recherche_f']);
$term_d=ehtml($row_a['term_d']);
$term_e=ehtml($row_a['term_e']);
$term_f=ehtml($row_a['term_f']);
$andhit_d=ehtml($row_a['andhit_d']);
$andhit_e=ehtml($row_a['andhit_e']);
$andhit_f=ehtml($row_a['andhit_f']);
$andhits_d=ehtml($row_a['andhits_d']);
$andhits_e=ehtml($row_a['andhits_e']);
$andhits_f=ehtml($row_a['andhits_f']);
$orhit_d=ehtml($row_a['orhit_d']);
$orhit_e=ehtml($row_a['orhit_e']);
$orhit_f=ehtml($row_a['orhit_f']);
$orhits_d=ehtml($row_a['orhits_d']);
$orhits_e=ehtml($row_a['orhits_e']);
$orhits_f=ehtml($row_a['orhits_f']);
$nohit_d=ehtml($row_a['nohit_d']);
$nohit_e=ehtml($row_a['nohit_e']);
$nohit_f=ehtml($row_a['nohit_f']);
$notpub_d=ehtml($row_a['notpub_d']);
$notpub_e=ehtml($row_a['notpub_e']);
$notpub_f=ehtml($row_a['notpub_f']);
$czoom_d=ehtml($row_a['czoom_d']);
$czoom_e=ehtml($row_a['czoom_e']);
$czoom_f=ehtml($row_a['czoom_f']);
$picsfound_d=ehtml($row_a['picsfound_d']);
$picsfound_e=ehtml($row_a['picsfound_e']);
$picsfound_f=ehtml($row_a['picsfound_f']);
$morepics_d=ehtml($row_a['morepics_d']);
$morepics_e=ehtml($row_a['morepics_e']);
$morepics_f=ehtml($row_a['morepics_f']);


$lan1na=$row_a['lan1na'];
$lan2na=$row_a['lan2na'];
$lan3na=$row_a['lan3na'];

$lan1name=ehtml($row_a['lan1name']);
$lan2name=ehtml($row_a['lan2name']);
$lan3name=ehtml($row_a['lan3name']);


if (str_replace(' ','',$lan1na)=='') $lan1na='10';
if (str_replace(' ','',$lan2na)=='') $lan2na='20';
if (str_replace(' ','',$lan3na)=='') $lan3na='30';


$sql_a="SELECT * FROM sizes";
$result_a=mysql_query($sql_a);

if ($result_a==false) {
echo 'SQL_a-Query failed!<p>'.mysql_error().'<p><font color=red>'.$sql_a.'</font><p>';
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
?>