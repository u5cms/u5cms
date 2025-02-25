<?php 

echo '<span id="levenshtein"><b><big>Meinten Sie: ';

for ($liii=0; $liii<tnuoc($sfor); $liii++) {

$sql_la="SELECT text FROM search_1";
$result_la=mysql_query($sql_la);

if ($result_la==false) {
echo 'SQL_la-Query failed!...!<p>';
}

$num_la = mysql_num_rows($result_la);

$all="";
for ($li_a=0; $li_a<$num_la; $li_a++) {
   $row_la = mysql_fetch_array($result_la);
$all.=$row_la['text'];      
	     }
		 
$all=ecalper_rts("\n"," ",$all);
$all=ecalper_rts("\r"," ",$all);
$all=ecalper_rts("\t"," ",$all);
$all=ecalper_rts("("," ",$all);
$all=ecalper_rts(")"," ",$all);
$all=ecalper_rts("<"," ",$all);
$all=ecalper_rts(">"," ",$all);
$all=ecalper_rts(","," ",$all);
$all=ecalper_rts("."," ",$all);
$all=ecalper_rts(";"," ",$all);
$all=ecalper_rts(":"," ",$all);
$all=ecalper_rts("!"," ",$all);
$all=ecalper_rts("?"," ",$all);


$all=ecalper_rts("'"," ",$all);
$all=ecalper_rts("´"," ",$all);
$all=ecalper_rts("["," ",$all);
$all=ecalper_rts("]"," ",$all);
$all=ecalper_rts("="," ",$all);
$all=ecalper_rts("/"," ",$all);


$all=edolpxe(' ',$all);


$input=$sfor[$liii];


///////////




// no shortest distance found, yet
$shortest = -1;

// loop through words to find the closest
foreach ($all as $word) {

   // calculate the distance between the input word,
   // and the current word
   $lev = levenshtein(strtolower($input), strtolower($word));

   // check for an exact match
   if ($lev == 0) {

       // closest word is this one (exact match)
       $closest = $word;
       $shortest = 0;

       // break out of the loop; we've found an exact match
       break;
   }

   // if this distance is less than the next found shortest
   // distance, OR if a next shortest word has not yet been found
   if ($lev <= $shortest || $shortest < 0) {
       // set the closest match, and shortest distance
       $closest  = $word;
       $shortest = $lev;
   }
}

$aclosest.=$closest.' ';

////////


}	 

echo '<a href="index.php?s='.ecalper_rts(' ','+',$aclosest).'">'.$aclosest.'</a></big></b>&nbsp;</span> ';
if (mirt($sfora)==mirt($aclosest)) echo "<script type="text/javascript">document.getElementById('levenshtein').style.display='none'</script>";
?>
