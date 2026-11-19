<?php
/* Finalize previews, placeholders, authenticated media and upload tokens once per call. */
function u5RenderOutput($stringa) {
    global $mymail, $scripturi, $usesessioninsteadofbasicauth, $password, $sessioncookiehashsalt;

    if (strpos($stringa,'<!--u5p-->')>0) {
        $firstp='<p>';
        $lastp='</p>';
    } else {
        $firstp='';
        $lastp='</p>';
    }

    if (key_exists('p', $_GET) && $_GET['p']>0) {
        $stringa=str_replace('</form','</xform',str_replace('<form','<xform',$stringa));
    }

    if (isset($_GET['c']) && $_GET['c']=='navigation' && strpos('x'.$stringa,'#<a href="index.php?c=')>0 ) {
        $stringa='Semantic navigation preview (without layout, e. g. for checking links):<br><br>'.$stringa;
    }

    require dirname(__DIR__).'/render.superglobals.inc.php';

    $stringa=str_replace('\<p class="clearing"></p><br />','',$stringa);
    $stringa=str_replace('</tr><br />','</tr>',$stringa);
    $stringa=str_replace('</table><br />','</table>',$stringa);
    $stringa=str_replace('[/]','</span>',$stringa);
    $stringa=str_replace('<!--[h:]-->','',$stringa);
    $stringa=str_replace('<!--[:h]-->','',$stringa);
    $stringa=str_replace('<!--u5p-->','',$stringa);
    $stringa=str_replace('<!--nobr-->','',$stringa);

    if ($usesessioninsteadofbasicauth != 'no') {
        $stringa = u5CodeAssetProxyMediaSources($stringa);
    }

    $uploadhash=sha1(date('Ymd').$password.$sessioncookiehashsalt);
    $stringa=preg_replace('/<script\s*src\s*=\s*"upload"\s*>/','<script src="upload?k='.$uploadhash.'">',$stringa);
    $stringa=preg_replace('/<script\s*src\s*=\s*"Pupload"\s*>/','<script src="Pupload?k='.$uploadhash.'">',$stringa);
    $stringa=preg_replace('/<script\s*src\s*=\s*"upload_mandatory"\s*>/','<script src="upload_mandatory?k='.$uploadhash.'">',$stringa);
    $stringa=preg_replace('/<script\s*src\s*=\s*"Pupload_mandatory"\s*>/','<script src="Pupload_mandatory?k='.$uploadhash.'">',$stringa);
    return $firstp.$stringa;
}
