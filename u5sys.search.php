<?php 
require_once('connect.inc.php');
$siteandintranet='';
if (isset($_GET['c']) && (substr($_GET['c'],0,1)=='!' || $_GET['c']=='_searchsi')) $siteandintranet='si';
?>
<form id="frm_Search" name="fsearch" method="get" action="javascript:location.href='index.php?c=_search<?php echo $siteandintranet ?>&amp;l=<?php echo $_GET['l']?>&amp;q='+escape(document.fsearch.q.value.replace(/ /g,',').replace(/\+/g,','))" onsubmit="return isterm()">
  <input type="hidden" name="l" value="<?php echo $_GET['l']?>" />
  <input type="hidden" name="c" value="_search" />

  <input <?php include('search.onchange.inc.php') ?> id="search_Input" type="text" name="q" value="" aria-label="<?php echo def($recherche_1,$recherche_2,$recherche_3,$recherche_4,$recherche_5) ?>" />
  <input onclick="document.getElementById('search_Input').value=document.getElementById('search_Input').value.replace(/Site \+ Intranet/g,'')" id="search_Submit" aria-label="<?php echo def($recherche_1,$recherche_2,$recherche_3,$recherche_4,$recherche_5) ?>" type="submit" class="btnSubmit" value="" />
</form>
<script type="text/javascript">
function isterm() {
if (document.fsearch.q.value.replace(/ /g,'')=='') {
valert=jQuery('<textarea />').html("<?php echo def($term_1,$term_2,$term_3,$term_4,$term_5)?>").text();
alert(valert);
document.fsearch.q.focus();
return false;
}
}
document.fsearch.q.value=unescape('<?php echo (str_replace('  ',' ',str_replace(',',' ',trim(addslashes($_GET['q'])))))?>').replace(/&quot;/g,'"');
</script>
