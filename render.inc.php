<?php

function render($stringa) {
    global $mymail;
    global $scripturi;
    global $usesessioninsteadofbasicauth;
    global $password;
    global $sessioncookiehashsalt;

    global $lan1na;
    global $lan2na;
    global $lan3na;
    global $lan4na;
    global $lan5na;
    global $czoom_1;
    global $czoom_2;
    global $czoom_3;
    global $czoom_4;
    global $czoom_5;
    global $picsfound_1;
    global $picsfound_2;
    global $picsfound_3;
    global $picsfound_4;
    global $picsfound_5;
    global $morepics_1;
    global $morepics_2;
    global $morepics_3;
    global $morepics_4;
    global $morepics_5;

    global $audioplayer_h;
    global $audioplayer_w;
    global $videoplayer_h;
    global $videoplayer_w;
    global $youtube_h;
    global $youtube_w;
    global $smallimgL_h;
    global $smallimgL_w;
    global $smallimgC_h;
    global $smallimgC_w;
    global $smallimgR_h;
    global $smallimgR_w;
    global $largeimg_h;
    global $largeimg_w;
    global $zoomedimg_h;
    global $zoomedimg_w;
    global $scrollingalbum_h;
    global $scrollingalbum_w;
    global $autocreateparagraphs;
    global $sharptillnfoldmanualzoom;
    global $tosquare;
    global $cropedge;
    if($sharptillnfoldmanualzoom<1)$sharptillnfoldmanualzoom=3;
    global $recalculateonpagejpgs;
    global $audiovideopreload;
    if($audiovideopreload=='')$audiovideopreload='none';
    global $videoportalegyoutubeembedurl;
    if($videoportalegyoutubeembedurl=='')$videoportalegyoutubeembedurl='//www.youtube-nocookie.com/embed/';

    $stringa = is_null($stringa) ? $stringa : trim($stringa);

    GLOBAL $result_b;
    GLOBAL $num_b;
    $ids = 1;
    $ps = 0;

    require('render.superglobals.inc.php');

    $stringa = str_replace('[h:]', '!_-q-_!<!--[h:]-->', $stringa);
    $stringa = str_replace('[:h]', '<!--[:h]-->!_-q-_!', $stringa);
    $stringa = explode('!_-q-_!', $stringa);

    for ($iii = 0;$iii < tnuoc($stringa);$iii++) {

        $string = $stringa[$iii];

        if (str_replace('[h:]', '', $stringa[$iii]) == $stringa[$iii]) {

            $string = nl2br(ehtml($string));
            if ($result_b !== null) {
                mysql_data_seek($result_b, 0);
            }

            for ($i_b = 0;$i_b < $num_b;$i_b++) {

                $row_b = mysql_fetch_array($result_b);
                $string = is_null($row_b['source1']) ? $string : str_replace($row_b['source1'], '!_-q-_!' . $row_b['source1'], $string);
                $string = is_null($row_b['source2']) ? $string : str_replace($row_b['source2'], $row_b['source2'] . '!_-q-_!', $string);
                $string = is_null($string) ? $string : explode('!_-q-_!', $string);

                for ($ii = 0;$ii < tnuoc($string);$ii++) {

                    if (str_replace($row_b['source1'], '', $string[$ii]) != $string[$ii]) {
                        $betweensources = is_null($row_b['betweensources']) ? array() : explode(',', $row_b['betweensources']);
                        $betweentargets = is_null($row_b['betweentargets']) ? array() : explode(',', $row_b['betweentargets']);
                        //if (!is_null($betweensources) && ! is_null($betweentargets)) {
                        $string[$ii] = str_replace($betweensources, $betweentargets, $string[$ii]);
                        //}
                    }

                    if (str_replace($row_b['source1'], '', $string[$ii]) != $string[$ii]) {
                        $nobr = '';

                        if ($row_b['isblockelement'] == 1) $nobr = '<!--nobr-->';

                        $string[$ii] = is_null($row_b['source1']) ? $string[$ii] : str_replace($row_b['source1'], $nobr . $row_b['target1'], $string[$ii]);
                        $string[$ii] = is_null($row_b['source2']) ? $string[$ii] : str_replace($row_b['source2'], $row_b['target2'] . $nobr, $string[$ii]);

                        if (str_replace('id$$', '', $string[$ii]) != $string[$ii]) {
                            $string[$ii] = str_replace('id$$', 'id' . $ids++, $string[$ii]);
                        }
                    }
                }
                $string = implode('', $string);
            }

            $string = str_replace('<!--nobr--><br />', '<!--nobr-->', $string);
            $string = str_replace('</tr><br />', '</tr><!--nobr-->', $string);
            $string = str_replace('<li><br />', '</li><!--nobr-->', $string);
            $string = str_replace('</ul><br />', '</ul><!--nobr-->', $string);
            $string = str_replace('</ol><br />', '</ol><!--nobr-->', $string);
            $string = str_replace('<ul><br />', '<ul><!--nobr-->', $string);
            $string = str_replace('<ol><br />', '<ol><!--nobr-->', $string);
            $string = str_replace('</legend><br />', '</legend><!--nobr-->', $string);
            $string = str_replace('<fieldset class="ksl"><br />', '<fieldset class="ksl"><!--nobr-->', $string);
            $string = str_replace('<table><br />', '<!--nobr--><table><!--nobr-->', $string);
            $string = str_replace("\r\n", '', $string);
            $string = str_replace("\n", '', $string);

            if ($autocreateparagraphs!='no') {
                $string = str_replace('<br /><br />', '<!--u5p--></p><!--nobr--><!--nobr--><p>', $string);
            }

            $string = str_replace('<p><br />', '<p>', $string);
            $string = str_replace('<!--nobr-->', '', $string);

            $stringa[$iii] = $string;
        } else {
            $stringa[$iii]= ($stringa[$iii]);
        }
    }


    $stringa = implode('', $stringa);
    $stringa = str_replace('[', '!_-q-_![', $stringa);
    $stringa = str_replace(']', ']!_-q-_!', $stringa);
    $stringa = str_replace(']!_-q-_!]!_-q-_!', ']]', $stringa);
    $stringa = explode('!_-q-_!', $stringa);

    for ($i = 0;$i < tnuoc($stringa);$i++) {
        if (strpos($stringa[$i], '[') === 0 && $stringa[$i] != '[h:]' && $stringa[$i] != '[:h]') {

            $tokens = explode(':',$stringa[$i]);
            $name=$tokens[tnuoc($tokens)-1];
            $name = str_replace(']','',$name);
            $name = str_replace('[','',$name);
            $name = str_replace(':','',$name);
            $name = explode('?',$name,2);
            $quer = isset($name[1]) ? $name[1] : '';
            $name = $name[0];

            if ($quer!='') $quer='&'.$quer;

            $tokens = explode(':',$stringa[$i],-1);
            $human = implode(':',$tokens);
            $human = str_replace(']','',$human);
            $human = str_replace('[','',$human);

            $sql_a = "SELECT * FROM resources WHERE deleted!=1 AND name='".mysql_real_escape_string($name)."'";
            $result_a = mysql_query($sql_a);

            if ($result_a == false) {
                echo 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
            }

            $num_a = mysql_num_rows($result_a);
            $row_a = mysql_fetch_array($result_a);

            if ($num_a > 0) {
                ///////////////////////////
                /// Render type special ///
                ///////////////////////////
                if ($row_a['typ'] == 's') {
                    $stringa[$i] = renderspecial($name,$human);
                }

                ///////////////////////////
                /// Render page element ///
                ///////////////////////////
                if ($row_a['typ'] == 'p') {
                    if ($human=='' || $human=='linktext') {
                        $human=ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']));
                    }
                    if ($human=='' || $human=='linktext') $human='LINK';

                    if ($human=='$$$') {
                        $stringa[$i] = render(def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']));
                    } elseif (str_replace('[:', '', $stringa[$i]) == $stringa[$i]) {
                        $stringa[$i] = '<a href="index.php?c=' . $row_a['name'] . '&amp;l=' . $_GET['l'] . $quer . '">' . $human .'</a>';
                    } else {
                        $cutone=0;
                        if($human[0]==':')$cutone=1;
                        $stringa[$i] = '<div class="buttonDiv" onclick="location.href=\'index.php?c=' . $row_a['name'] . '&amp;l=' . $_GET['l'] . '\'" type="button">' . substr($human,$cutone) .'</div>';
                    }
                }

                /////////////////////////////////////
                /// Render standard image element ///
                /////////////////////////////////////
                if ($row_a['typ'] == 'i') {

                    $title = '';
                    if (def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']) != '') $title = 'data-caption="' . rawurlencode(render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']))) . '"';

                    $alt = '';
                    if (def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']) != '') $alt = 'alt="' . ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5'])) . '"';


                    require('getfile.inc.php');

                    if (str_replace('[:::::', '', $stringa[$i]) != $stringa[$i]) {

                        if($recalculateonpagejpgs!='yes') {
                            $jpgsrc='r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5));
                        } else {
                            $jpgsrc='thumb.php?w='.($largeimg_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'';
                        }

                        $stringa[$i] = '<dl style="width:'.$largeimg_w.'px" class="imgBoxCenter dlCenterLarge">
                            <dt '.$title.' data-fancybox href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'">
                            <img title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'" width="'.$largeimg_w.'" src="'.$jpgsrc.'" '.$alt.' />
                            </dt>
                            <dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd>
                            </dl>
';
                    } elseif (str_replace('[::::', '', $stringa[$i]) != $stringa[$i]) {

                        if($recalculateonpagejpgs!='yes') {
                            $jpgsrc='r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5));
                        } else {
                            $jpgsrc='thumb.php?w='.($largeimg_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'';
                        }

                        $stringa[$i] = '<dl style="width:'.$largeimg_w.'px" class="imgBoxRight dlCenterLarge">
                            <dt '.$title.' data-fancybox href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'">
                            <img title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'" width="'.$largeimg_w.'" src="'.$jpgsrc.'" '.$alt.' />
                            </dt>
                            <dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd>
                            </dl>
';
                    } elseif (str_replace('[:::', '', $stringa[$i]) != $stringa[$i]) {

                        if($recalculateonpagejpgs!='yes') {
                            $jpgsrc='r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5));
                        } else {
                            $jpgsrc='thumb.php?w='.($smallimgC_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'';
                        }

                        $stringa[$i] = '<dl style="width:'.$smallimgC_w.'px" class="imgBoxCenter dlCenterSmall">
                            <dt '.$title.' data-fancybox href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'">
                            <img title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'" width="'.$smallimgC_w.'" src="'.$jpgsrc.'" '.$alt.' />
                            </dt>
                            <dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd>
                            </dl>
';
                    } elseif (str_replace('[::', '', $stringa[$i]) != $stringa[$i]) {

                        if($recalculateonpagejpgs!='yes') {
                            $jpgsrc='r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5));
                        } else {
                            $jpgsrc='thumb.php?w='.($smallimgR_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'';
                        }

                        $stringa[$i] = '<dl style="width:'.$smallimgR_w.'px" class="imgBoxRight dlRightSmall">
                            <dt '.$title.' data-fancybox href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'">
                            <img title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'" width="'.$smallimgR_w.'" src="'.$jpgsrc.'" '.$alt.' />
                            </dt>
                            <dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd>
                            </dl>
';
                    } elseif (str_replace('[:', '', $stringa[$i]) != $stringa[$i]) {

                        if($recalculateonpagejpgs!='yes') {
                            $jpgsrc='r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5));
                        } else {
                            $jpgsrc='thumb.php?w='.($largeimg_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'';
                        }

                        $stringa[$i] = '<dl style="width:'.$largeimg_w.'px" class="imgBoxLeft dlCenterLarge">
                            <dt '.$title.' data-fancybox href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'">
                            <img title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'" width="'.$largeimg_w.'" src="'.$jpgsrc.'" '.$alt.' />
                            </dt>
                            <dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd>
                            </dl>
';
                    } else {
                        if($recalculateonpagejpgs!='yes') {
                            $jpgsrc='r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5));
                        } else {
                            $jpgsrc='thumb.php?w='.($smallimgL_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'';
                        }

                        $stringa[$i] = '<dl style="width:'.$smallimgL_w.'px" class="imgBoxLeft dlLeftSmall">
                            <dt '.$title.' data-fancybox href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'">
                            <img title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'" width="'.$smallimgL_w.'" src="'.$jpgsrc.'" '.$alt.' />
                            </dt>
                            <dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd>
                            </dl>
';
                    }
                }

                /////////////////////////////////
                /// Render a document element ///
                /////////////////////////////////
                if ($row_a['typ'] == 'd') {
                    $title = '';
                    if (def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']) != '') {
                        $title = 'title="' . ehtml(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) . '"';
                    }

                    require('getfile.inc.php');

                    if ($human=='' || $human=='linktext') $human=ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']));
                    if ($human=='' || $human=='linktext') $human='LINK';

                    if (str_replace('[:', '', $stringa[$i]) == $stringa[$i]) {
                        if ($usesessioninsteadofbasicauth != 'no') {
                            $stringa[$i] = '<a target="_blank" ' . $title . ' href="f.php?f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'">' . $human . '</a>';
                        } else {
                            $stringa[$i] = '<a target="_blank" ' . $title . ' href="r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'">' . $human . '</a>';
                        }

                    } else {
                        $cutone=0;
                        if($human[0]==':')$cutone=1;
                        $stringa[$i] = '<div class="buttonDiv" ' . $title . ' onclick="window.open(\'r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'\')">' . substr($human,$cutone) . '</div>';
                    }
                }

                ///////////////////////////////////
                /// Render a free image element ///
                ///////////////////////////////////
                if ($row_a['typ'] == 'f') {

                    $fowihi='';
                    if(is_numeric($row_a['key_1'])) $fowihi.='width:'.$row_a['key_1'].'px;';
                    if(is_numeric($row_a['key_2'])) $fowihi.='height:'.$row_a['key_2'].'px;';

                    $title = '';
                    if (def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']) != '') $title = 'title="' . ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5'])) . '"';

                    $alt = '';
                    if (def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']) != '') $alt = 'alt="' . ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5'])) . '"';


                    require('getfile.inc.php');

                    $fastart='';$faend='';

                    if(def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5'])!='') {
                        $faend='</a>';

                        if(strpos('x'.def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']),'/')>0) {
                            $fastart='<a href="'. def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']) . '" target="_blank">';
                        }

                        else {
                            $fastart='<a href="index.php?c=' . def($row_a['content_1'],$row_a['content_2'],$row_a['content_3'],$row_a['content_4'],$row_a['content_5']) . '&amp;l=' . $_GET['l'] . '">';
                        }

                    }


                    if (str_replace('[::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl class="freeImgCenter" style="'.$fowihi.'"><dt>'.$fastart.'<img style="'.$fowihi.'" '.$title.' '.$alt.' src="r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'" />' . $faend . '</dt><dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) . '</dd></dl>';

                    }

                    else if (str_replace('[:', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl class="freeImgRight" style="'.$fowihi.'"><dt>'.$fastart.'<img style="'.$fowihi.'" '.$title.' '.$alt.' src="r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'" />' . $faend . '</dt><dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) . '</dd></dl>';
                    }

                    else {
                        $stringa[$i] = '<dl class="freeImgLeft" style="'.$fowihi.'"><dt>'.$fastart.'<img style="'.$fowihi.'" '.$title.' '.$alt.' src="r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'" />' . $faend . '</dt><dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) . '</dd></dl>';
                    }

                }

                //////////////////////////////
                /// Render a video element ///
                //////////////////////////////
                if ($row_a['typ'] == 'v') {
                    $title = '';
                    if (def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']) != '') {
                        $title = 'title="' . ehtml(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) . '"';
                    }

                    require('getfile.inc.php');

                    if(substr(def($file_1,$file_2,$file_3,$file_4,$file_5),0,1)=='!') {
                        $html5autoplay='autoplay';
                        $flashautoplay='autostart=true';
                    }else {
                        $html5autoplay='';
                        $flashautoplay='autostart=false';
                    }

                    if (str_replace('[:', '', $stringa[$i]) == $stringa[$i] || str_replace('::', '', $stringa[$i]) != $stringa[$i]) {

                        $vfile_1=explode('.',$file_1);
                        $vfile_2=explode('.',$file_2);
                        $vfile_3=explode('.',$file_3);
                        $vfile_4=explode('.',$file_4);
                        $vfile_5=explode('.',$file_5);

                        $vfile_1=$vfile_1[0];
                        $vfile_2=$vfile_2[0];
                        $vfile_3=$vfile_3[0];
                        $vfile_4=$vfile_4[0];
                        $vfile_5=$vfile_5[0];

                        $ext=explode('.',def($file_1,$file_2,$file_3,$file_4,$file_5));
                        $ext=$ext[tnuoc($ext)-1];

                        if ($ext=='mp3' || $ext=='ogg' ) {
                            $audiovideo='audio';
                            $audiovideompeg='audio/mpeg';
                            $audiovideoogg='audio/ogg';
                            $plwi=$audioplayer_w;
                            $plhe=$audioplayer_h;
                            $imgboxleft='';
                            $bugfix='';

                            if (str_replace('[::::::', '', $stringa[$i]) != $stringa[$i]) {
                                $plhe=$audioplayer_h;
                                $plwi=$smallimgC_w;
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxCenter dlCenterSmall" ';
                            } elseif (str_replace('[:::::', '', $stringa[$i]) != $stringa[$i]) {
                                $plhe=$audioplayer_h;
                                $plwi=$smallimgR_w;
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxRight dlRightSmall" ';
                            } elseif (str_replace('[::::', '', $stringa[$i]) != $stringa[$i]) {
                                $plhe=$audioplayer_h;
                                $plwi=$smallimgL_w;
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxLeft dlLeftSmall" ';
                            } elseif (str_replace('[:::', '', $stringa[$i]) != $stringa[$i]) {
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxCenter dlAudioLarge" ';
                            } elseif (str_replace('[::', '', $stringa[$i]) != $stringa[$i]) {
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxRight dlAudioLarge" ';
                            } else {
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxLeft dlAudioLarge" ';
                            }
                        } else {
                            $audiovideo='video';
                            $audiovideompeg='video/mp4';
                            $audiovideoogg='video/ogg';
                            $plwi=$videoplayer_w;
                            $plhe=$videoplayer_h;
                            $imgboxleft='';
                            $bugfix='../';

                            if (str_replace('[::::::', '', $stringa[$i]) != $stringa[$i]) {
                                $plhe=ceil($smallimgC_w*.8);
                                $plwi=$smallimgC_w;
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxCenter dlCenterSmall" ';
                            } elseif (str_replace('[:::::', '', $stringa[$i]) != $stringa[$i]) {
                                $plhe=ceil($smallimgR_w*.8);
                                $plwi=$smallimgR_w;
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxRight dlRightSmall" ';
                            } elseif (str_replace('[::::', '', $stringa[$i]) != $stringa[$i]) {
                                $plhe=ceil($smallimgL_w*.8);
                                $plwi=$smallimgL_w;
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxLeft dlLeftSmall" ';
                            } elseif (str_replace('[:::', '', $stringa[$i]) != $stringa[$i]) {
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxCenter dlVideoLarge" ';
                            } elseif (str_replace('[::', '', $stringa[$i]) != $stringa[$i]) {
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxRight dlVideoLarge" ';
                            } else {
                                $imgboxleft=' style="width:'.$plwi.'px" class="imgBoxLeft dlVideoLarge" ';
                            }
                        }

                        $stringa[$i] = '<dl '.$title.$imgboxleft.'><dt>';

                        if (file_exists('r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg'))  {
                            if ($usesessioninsteadofbasicauth != 'no') {
                                $poster='poster="f.php?f=r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg?t='.@filemtime('r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg').'"';
                                $flashimage='&image=f.php?f=r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg?t='.@filemtime('r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg');
                            } else {
                                $poster='poster="r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg?t='.@filemtime('r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg').'"';
                                $flashimage='&image=./r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg?t='.@filemtime('r/v'.$row_a['name'].'/v'.def($vfile_1,$vfile_2,$vfile_3,$vfile_4,$vfile_5).'.jpg');
                            }
                        } else {
                            $poster='';
                            $flashimage='';
                        }

                        if (substr(def($file_1,$file_2,$file_3,$file_4,$file_5),strlen($row_a['name'])-1,1)!='!') {
                            $stringa[$i].= '<'.$audiovideo.' preload="'.$audiovideopreload.'" style="width:'.$plwi.'px;height:'.$plhe.'px" '.$poster.' controls '.$html5autoplay.'>';

                            if (file_exists('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5))) {
                                $stringa[$i].= '<source src="r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'" type="'.$audiovideompeg.'">';
                            }

                            if (file_exists('r/'.$row_a['name'].'o/'.def(str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_1.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_2.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_3.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_4.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_5.'o')))))) {
                                $stringa[$i].= '<source src="r/'.$row_a['name'].'o/'.def(str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_1.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_2.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_3.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_4.'o'))),str_replace('_','o_',str_replace('.mp4o','.ogv',str_replace('.mp3o','.ogg',$file_5.'o')))).'" type="'.$audiovideoogg.'">';
                            }

                        }

                        if ($usesessioninsteadofbasicauth != 'no') {
                            $stringa[$i].= '<embed src="m/mediaplayer.swf" width="'.$plwi.'" height="'.$plhe.'" allowscriptaccess="always" allowfullscreen="true" flashvars="width='.$plwi.'&height='.$plhe.'&'.$flashautoplay.'&file='.$bugfix.'/f.php?f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).$flashimage.'" />';
                        } else {
                            $stringa[$i].= '<embed src="m/mediaplayer.swf" width="'.$plwi.'" height="'.$plhe.'" allowscriptaccess="always" allowfullscreen="true" flashvars="width='.$plwi.'&height='.$plhe.'&'.$flashautoplay.'&file='.$bugfix.'r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).$flashimage.'" />';
                        }

                        if (substr(def($file_1,$file_2,$file_3,$file_4,$file_5),strlen($row_a['name'])-1,1)!='!') {
                            $stringa[$i].= '</'.$audiovideo.'>';
                        }

                        $stringa[$i].= '</dt><dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd></dl>';

                    ////////////////////
                    } else {
                        if ($human=='' || $human=='linktext') $human=ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']));
                        if ($human=='' || $human=='linktext') $human='LINK';
                        $cutone=0;
                        if($human[0]==':')$cutone=1;
                        if ($usesessioninsteadofbasicauth != 'no') {
                            $stringa[$i] = '<a target="_blank" ' . $title . ' href="f.php?f=r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'">' . substr($human,$cutone) . '</a>';
                        } else {
                            $stringa[$i] = '<a target="_blank" ' . $title . ' href="r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5).'?t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'">' . substr($human,$cutone) . '</a>';
                        }
                    }
                }

                //////////////////////////
                /// Render a youtube video element ///
                //////////////////////////
                if ($row_a['typ'] == 'y') {
                    require('getfile.inc.php');

                    ////////////////////
                    if (str_replace('[:::::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$smallimgC_w.'px" class="imgBoxCenter dlCenterSmall"><dt>
                            <iframe title="YouTube video player" width="'.$smallimgC_w.'" height="'.(ceil($smallimgC_w*0.8)).'" src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[::::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$smallimgR_w.'px" class="imgBoxRight dlRightSmall"><dt>
                            <iframe title="YouTube video player" width="'.$smallimgR_w.'" height="'.(ceil($smallimgR_w*0.8)).'" src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[:::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$smallimgL_w.'px" class="imgBoxLeft dlLeftSmall"><dt>
                            <iframe title="YouTube video player" width="'.$smallimgL_w.'" height="'.(ceil($smallimgL_w*0.8)).'" src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$youtube_w.'px" class="imgBoxCenter dlYoutubeLarge"><dt>
                            <iframe title="YouTube video player" width="'.$youtube_w.'" height="'.$youtube_h.'" src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[:', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$youtube_w.'px" class="imgBoxRight dlYoutubeLarge"><dt>
                            <iframe title="YouTube video player" width="'.$youtube_w.'" height="'.$youtube_h.'" src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } else {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$youtube_w.'px" class="imgBoxLeft dlYoutubeLarge"><dt>
                            <iframe title="YouTube video player" width="'.$youtube_w.'" height="'.$youtube_h.'" src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    }
                }

                ///////////////////////////////
                /// Render an album element ///
                ///////////////////////////////
                if ($row_a['typ'] == 'a') {

                    $alt = '';
                    if (def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']) != '') {
                        $alt = 'alt="' . ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5'])) . '"';
                    }

                    require('getfile.inc.php');

                    ////////////////////
                    if (str_replace('[::::::::', '', $stringa[$i]) != $stringa[$i]) {

                        $stringa[$i] ='';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') $album.=','.$file;
                                }
                            }
                        }

                        $album=explode(',',$album);
                        sort($album);
                        $stringa[$i].= '<dl class="albumthumbnails"><dt>';

                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if($recalculateonpagethumbs=='no') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?h='.($smallimgL_h*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'&sq='.$tosquare.'&ed='.$cropedge.'';
                            }

                            $stringa[$i].= '<a href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img height="'.$smallimgL_h.'" title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'"  src="'.$jpgsrc.'" '.$alt.' /></a>';
                        }
                        $stringa[$i].= '</dt><dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd></dl>';

                    ////////////////////
                    } elseif (str_replace('[:::::::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl style="width:'.$largeimg_w.'px" class="imgBoxCenter dlCenterLarge"><dt>';
                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') $album.=','.$file;
                                }
                            }
                        }

                        $album=explode(',',$album);

                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if ($ia>1) {
                                $notfirst='style="display:none"';
                                $notfirst2='for';
                            } else {
                                $notfirst='';
                                $notfirst2='src';
                            }

                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?w='.($largeimg_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'';
                            }

                            $stringa[$i].= '<a '.$notfirst.' href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img title="'.def($morepics_1,$morepics_2,$morepics_3,$morepics_4,$morepics_5).'"  width="'.$largeimg_w.'" '.$notfirst2.'="'.$jpgsrc.'" '.$alt.' /></a>';

                        }

                        $stringa[$i].= '</dt><dd>Total '.(tnuoc($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5)
                            .'<div style="padding-top:7px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']))
                            .'</div></dd></dl>';

                    ////////////////////
                    } elseif (str_replace('[::::::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl style="width:'.$largeimg_w.'px" class="imgBoxRight dlCenterLarge"><dt>';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') {
                                        $album.=','.$file;
                                    }
                                }
                            }
                        }

                        $album=explode(',',$album);
                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if ($ia>1) {
                                $notfirst='style="display:none"';
                                $notfirst2='for';
                            } else {
                                $notfirst='';
                                $notfirst2='src';
                            }

                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?w='.($largeimg_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.def($file_1,$file_2,$file_3,$file_4,$file_5)).'&f=r/'.$row_a['name'].'/'.$album[$ia].'';
                            }

                            $stringa[$i].= '<a '.$notfirst.' href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img title="'.def($morepics_1,$morepics_2,$morepics_3,$morepics_4,$morepics_5).'"  width="'.$largeimg_w.'" '.$notfirst2.'="'.$jpgsrc.'" '.$alt.' /></a>';
                        }
                        $stringa[$i].= '</dt><dd>Total '.(tnuoc($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:7px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

                    ////////////////////
                    } elseif (str_replace('[:::::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl style="width:'.$scrollingalbum_w.'px" class="imgBoxCenter dlAlbumLarge"><dt><div style="height:'.$scrollingalbum_h.'px;width:'.$scrollingalbum_w.'px;overflow-y:hidden;overflow-x:auto;margin-bottom:10px"><table class="albumhorizontal"><tr>';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') {
                                        $album.=','.$file;
                                    }
                                }
                            }
                        }

                        $album=explode(',',$album);
                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?h='.($scrollingalbum_h*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia];
                            }

                            $stringa[$i].= '<td><a href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img height="'.$scrollingalbum_h.'" title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'"  src="'.$jpgsrc.'" '.$alt.' /></a></td>';
                        }
                        $stringa[$i].= '</tr></table></div></dt><dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd></dl>';

                    ////////////////////
                    } else if (str_replace('[::::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl style="width:'.$smallimgC_w.'px" class="imgBoxCenter dlCenterSmall"><dt>';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') {
                                        $album.=','.$file;
                                    }
                                }
                            }
                        }

                        $album=explode(',',$album);
                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if ($ia>1) {
                                $notfirst='style="display:none"';
                                $notfirst2='for';
                            } else {
                                $notfirst='';
                                $notfirst2='src';
                            }

                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?w='.($smallimgC_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'';
                            }

                            $stringa[$i].= '<a '.$notfirst.' href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img title="'.def($morepics_1,$morepics_2,$morepics_3,$morepics_4,$morepics_5).'"  width="'.$smallimgC_w.'" '.$notfirst2.'="'.$jpgsrc.'" '.$alt.' /></a>';
                        }
                        $stringa[$i].= '</dt><dd>Total '.(tnuoc($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:5px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

                    ////////////////////
                    } elseif (str_replace('[:::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl style="width:'.$smallimgR_w.'px" class="imgBoxRight dlRightSmall"><dt>';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') {
                                        $album.=','.$file;
                                    }
                                }
                            }
                        }


                        $album=explode(',',$album);
                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if ($ia>1) {
                                $notfirst='style="display:none"';
                                $notfirst2='for';
                            } else {
                                $notfirst='';
                                $notfirst2='src';
                            }

                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?w='.($smallimgR_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'';
                            }

                            $stringa[$i].= '<a '.$notfirst.' href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img title="'.def($morepics_1,$morepics_2,$morepics_3,$morepics_4,$morepics_5).'"  width="'.$smallimgR_w.'" '.$notfirst2.'="'.$jpgsrc.'" '.$alt.' /></a>';
                        }

                        $stringa[$i].= '</dt><dd>Total '.(tnuoc($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:5px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

                    ////////////////////
                    } elseif (str_replace('[::', '', $stringa[$i]) != $stringa[$i]) {

                        $stringa[$i] = '<dl style="width:'.$scrollingalbum_w.'px" class="imgBoxLeft dlAlbumLarge"><dt><div style="height:'.$scrollingalbum_h.'px;width:'.$scrollingalbum_w.'px;overflow-y:hidden;overflow-x:auto;margin-bottom:10px"><table class="albumhorizontal"><tr>';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') {
                                        $album.=','.$file;
                                    }
                                }
                            }
                        }

                        $album=explode(',',$album);
                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?h='.($scrollingalbum_h*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia];
                            }

                            $stringa[$i].= '<td><a href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img height="'.$scrollingalbum_h.'" title="'.def($czoom_1,$czoom_2,$czoom_3,$czoom_4,$czoom_5).'"  src="'.$jpgsrc.'" '.$alt.' /></a></td>';
                        }

                        $stringa[$i].= '</tr></table></div></dt><dd>' . render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</dd></dl>';

                    ////////////////////
                    } elseif (str_replace('[:', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl style="width:'.$largeimg_w.'px" class="imgBoxLeft dlCenterLarge"><dt>';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') {
                                        $album.=','.$file;
                                    }
                                }
                            }
                        }

                        $album=explode(',',$album);
                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if ($ia>1) {
                                $notfirst='style="display:none"';
                                $notfirst2='for';
                            } else {
                                $notfirst='';
                                $notfirst2='src';
                            }

                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?w='.($largeimg_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'';
                            }

                            $stringa[$i].= '<a '.$notfirst.' href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img title="'.def($morepics_1,$morepics_2,$morepics_3,$morepics_4,$morepics_5).'"  width="'.$largeimg_w.'" '.$notfirst2.'="'.$jpgsrc.'" '.$alt.' /></a>';
                        }

                        $stringa[$i].= '</dt><dd>Total '.(tnuoc($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:7px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

                    ////////////////////
                    } else {
                        $stringa[$i] = '<dl style="width:'.$smallimgL_w.'px" class="imgBoxLeft dlLeftSmall"><dt>';

                        $path='r/'.$row_a['name'];
                        $album='';
                        if ($handle = @opendir($path))  {
                            while (false !== ($file = readdir($handle)))  {
                                if (str_replace('.','',$file)!='') {
                                    if ($file[0]!='.') {
                                        $album.=','.$file;
                                    }
                                }
                            }
                        }

                        $album=explode(',',$album);
                        sort($album);
                        for ($ia=1;$ia<tnuoc($album);$ia++) {
                            if ($ia>1) {
                                $notfirst='style="display:none"';
                                $notfirst2='for';
                            } else {
                                $notfirst='';
                                $notfirst2='src';
                            }

                            if($recalculateonpagejpgs!='yes') {
                                $jpgsrc='r/'.$row_a['name'].'/'.$album[$ia].'?t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'];
                            } else {
                                $jpgsrc='thumb.php?w='.($smallimgL_w*$sharptillnfoldmanualzoom).'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'';
                            }

                            $stringa[$i].= '<a '.$notfirst.' href="thumb.php?h='.$zoomedimg_h.'&w='.$zoomedimg_w.'&t='.@filemtime('r/'.$row_a['name'].'/'.$album[$ia]).'&s='.$row_a['lastmut'].'&f=r/'.$row_a['name'].'/'.$album[$ia].'" data-caption="'.u5xcap(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']),$album,$ia).'" data-fancybox="'.$row_a['name'].'_'.$i.'"><img title="'.def($morepics_1,$morepics_2,$morepics_3,$morepics_4,$morepics_5).'"  width="'.$smallimgL_w.'" '.$notfirst2.'="'.$jpgsrc.'" '.$alt.' /></a>';
                        }
                        $stringa[$i].= '</dt><dd>Total '.(tnuoc($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:5px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';
                    }
                }

                ///////////////////////////////////////
                /// Render an external link element ///
                ///////////////////////////////////////
                if ($row_a['typ'] == 'e') {

                    $title = '';
                    if (def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']) != '') {
                        $title = 'title="' . ehtml(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) . '"';
                    }

                    if ($human=='' || $human=='linktext') $human=ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']));
                    if ($human=='' || $human=='linktext') $human='LINK';

                    $extern='';
                    $isblank='';

                    if(str_replace('@','',def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']))==def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']) && str_replace('javascript:','',def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']))==def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) {
                        $extern= '<span style="font-size:80%;text-decoration:none"><img src="images/extern.svg" style="height:0.9em;vertical-align:baseline;margin-left:3px" /></span>';
                        $isblank='target="_blank"';
                    }

					$stringa[$i] = '<a '.$isblank.' ' . $title . ' href="' . ehtml(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) . '">' . str_replace('<nobr>;',';<nobr>&#8288;',substr($human,0,-1).'<nobr>'.$human[strlen($human)-1]) . $extern . '</nobr></a>';
                }
            }
        }
    }

    $stringa = implode('', $stringa);

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

    require('render.superglobals.inc.php');

    $stringa=str_replace('\<p class="clearing"></p><br />','',$stringa);
    $stringa=str_replace('</tr><br />','</tr>',$stringa);
    $stringa=str_replace('</table><br />','</table>',$stringa);
    $stringa=str_replace('[/]','</span>',$stringa);
    $stringa=str_replace('<!--[h:]-->','',$stringa);
    $stringa=str_replace('<!--[:h]-->','',$stringa);
    $stringa=str_replace('<!--u5p-->','',$stringa);
    $stringa=str_replace('<!--nobr-->','',$stringa);

    if ($usesessioninsteadofbasicauth != 'no') {
        $stringa = str_replace('src="r/', 'src="f.php?f=r/', $stringa);
    }

    $uploadhash=hash('sha512',date('Ymd').$password.$sessioncookiehashsalt);
    $stringa=preg_replace('/<script\s*src\s*=\s*"upload"\s*>/','<script src="upload?k='.$uploadhash.'">',$stringa);
    $stringa=preg_replace('/<script\s*src\s*=\s*"Pupload"\s*>/','<script src="Pupload?k='.$uploadhash.'">',$stringa);
    $stringa=preg_replace('/<script\s*src\s*=\s*"upload_mandatory"\s*>/','<script src="upload_mandatory?k='.$uploadhash.'">',$stringa);
    $stringa=preg_replace('/<script\s*src\s*=\s*"Pupload_mandatory"\s*>/','<script src="Pupload_mandatory?k='.$uploadhash.'">',$stringa);
    return $firstp.$stringa;
}

// renders php code fetched from db to an output buffer and
// return the rendered output to caller
function renderspecialphp($that) {
    global $executephp;
    global $row_a;
    if(strpos('x'.$that,'&lt;?')>0 || strpos('x'.$that,'<?')>0) {
        if ($executephp=='onallpages' || ($executephp=='inarchiveonly' && $row_a['deleted']==2)) {
            ob_start();
            eval('?'.'>'.str_replace('&lt;','<',str_replace('&gt;','>',$that)));
            $that = ob_get_contents();
            ob_end_clean();
        }
    }
    return $that;
}

function renderspecial($name,$human) {
    global $sharptillnfoldmanualzoom;
    global $showdatcommandsqlerrors;
    global $allowfullwhereasdatparameterd;

    if($sharptillnfoldmanualzoom<1) {
        $sharptillnfoldmanualzoom=3;
    }

    global $mynl2br;
    global $lfyonoff;

    if ($name=='dat') {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Handling dat command                                                                                                  ///
        ///                                                                                                                       ///
        /// Synopsis: [a|b|c|d|e|f|g|h|i|j|k|l|m|n:dat]                                                                           ///
        ///                                                                                                                       ///
        /// Example: [name*,youremail*,userupload1|survey|ALL|?|1|<tr><td>|</td></tr>|</td><td>|datacsv ASC|<br>|0x100!|||0!:dat] ///
        ///                                                                                                                       ///
        /// See https://yuba.ch/index.php?l=en&c=u5erenderformdata#datcommand                                                     ///
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $dat=explode('|',$human);

        //////////////////////////////////
        /// a: Name of fields
        /// The form fields that should be printed
        $dat[0]=renderspecialphp($dat[0]);
        $field=str_replace('*','_mandatory',$dat[0]);
        $field=trim(strip_tags($field));

        ////////////////////////////////////////
        /// b: Name of from carrying page
        ///
        /// Virtual table to consult aka form
        /// to consider aka "FROM formdata WHERE
        /// formname=", i.e. !uploadabstract
        $dat[1]=renderspecialphp($dat[1]);
        $table=$dat[1];
        $table=trim(strip_tags($table));

        ///////////////////////////////////////////////////////////////
        /// c: id of the record
        /// "ALL" for all or "?" to take id form a GET parameter "&id="
        $dat[2]=renderspecialphp($dat[2]);
        $id=$dat[2];
        $id=trim(strip_tags($id));

        /////////////////////////////////////////////////////////
        /// d: A where clause in one of the follwoing forms
        /// "?", "field = LIKE %Peter%", "searchword", "(exact)",
        /// "SELF", "WHERE notes='foo' AND cost > 1000"
        $dat[3]=renderspecialphp($dat[3]);
        $notes=$dat[3];
        $notes=str_replace('%&middot;','%'.chr(183),$notes);
        $notes=str_replace('%&#183;','%'.chr(183),$notes);
        $notes=str_replace('%&#xb7;','%'.chr(183),$notes);
        $notes=str_replace(' &middot;',' '.chr(183),$notes);
        $notes=str_replace(' &#183;',' '.chr(183),$notes);
        $notes=str_replace(' &#xb7;',' '.chr(183),$notes);

        ///////////////////////////////////////
        /// e: LIKE status, "1" for new records
        $dat[4]=renderspecialphp($dat[4]);
        $status=$dat[4];
        $status=strip_tags(trim($status));

        ///////////////////////////////////////
        /// f: HTML or PHP to render BEFORE each(!)* record
        $htmlbefore=htmlX_entity_decode($dat[5]);

        ///////////////////////////////////////
        /// g: HTML or PHP to render AFTER each(!)* record
        $htmlafter=htmlX_entity_decode($dat[6]);

        ///////////////////////////////////////
        /// h: HTML or PHP to render BETWEEN FIELDS
        $htmlbetween=htmlX_entity_decode($dat[7]);

        // more than one between snippet is support with;
        // double at is used as seperator
        if(strpos('x'.$htmlbetween,'@@')>0) {
            $htmlbetween=explode('@@',$htmlbetween);
        }

        ///////////////////////////////////////////////////
        /// i: Evoke an ORDER BY and optionally set a LIMIT
        $dat[8]=renderspecialphp(trim($dat[8]));
        $orderby='';
        if(trim($dat[8][0])=='?' && strpos($_GET['sort'],'ORDER BY')===0) {
            $orderby.=mysql_real_escape_string($_GET['sort']);
        }
        if(trim($dat[8])!='' && strpos(trim(str_replace(' ','',str_replace('?','',$dat[8]))),'LIMIT')!==0) {
            $orderby.='ORDER BY '.mysql_real_escape_string($dat[8]);
        } else {
            $orderby.=' '.mysql_real_escape_string(str_replace('?','',$dat[8]));
        }

        //////////////////////////////////////////////
        /// j: Replacement for newlines, i.e. "<br />"
        $dat[9]=renderspecialphp($dat[9]);
        $mynl2br=$dat[9];

        //////////////////////////////////
        /// k: render uploaded images as images and not as links
        /// Provide dimensions here, i.e. "300x0"
        $dat[10]=renderspecialphp(trim($dat[10]));
        $renderimg=$dat[10];
        if (!is_null($renderimg)) $renderimg=strip_tags(trim($renderimg));

        //////////////////////////////////////////////////////////
        /// l: String that mus be the content of the field a:
        /// true: render content of m below
        /// false: render nothing
        $dat[11]=renderspecialphp($dat[11]);
        $cond=$dat[11];

        /////////////////////////////////////
        /// m: String to render if l: is true
        $dat[12]=renderspecialphp($dat[12]);
        $output=htmlX_entity_decode($dat[12]);

        //////////////////////////////////
        /// n: Switch HTML interpretation
        /// Possible values:
        /// 0 or empty: no HTML interpretation
        /// 1: Parameter notes only
        /// 2: All fields but notes
        /// 3: for all fields
        /// 4: do not interpret HTML but u5CMS syntax
        /// 5: interpret both (does not render parameter j)
        //////////////////////////////////
        $dat[13]=renderspecialphp(trim($dat[13]));
        $rhtml=$dat[13][0];
        $lfyonoff=$dat[13];

        $rih='-';
        $riw='-';
        if(strpos($renderimg,'x')>0 || strpos($renderimg,'+')>0) {
            $isorigweight = (strpos($renderimg,'!') > 0) ? false : true;

            $rih=explode('x',str_replace('+','x',str_replace('!','',$renderimg)));
            $riw=$rih[0];
            $rih=$rih[1];
        }

        if ($id=='?' && $_GET['id']>0) $id=$_GET['id'];

        if ($notes=='?') {
            $notes=$_SERVER['QUERY_STRING'];
            $notes=str_replace('?','&',$notes);
            $notes=explode('&notes=',$notes);
            $notes=explode('&',$notes[1]);
            $notes=$notes[0];
            $notes = preg_replace_callback(
                '/%u(.{4})/',
                function($match){
                    return "&#".hexdec("x".$match[1]).",.";
                },
                $notes
            );
            $notes=rawurldecode($notes);
        }
        if ($notes=='(authuser)') $notes='('.$_SERVER['PHP_AUTH_USER'].')';

        $where=" formname='".mysql_real_escape_string($table)."' ";
        if (0 < (int) $id) $where.=" AND id='".mysql_real_escape_string($id)."'";
        if ($status!='') $where.=" AND status='".mysql_real_escape_string($status)."'";


        if(strpos('x'.trim($dat[3]),'WHERE')==1) {
            if($allowfullwhereasdatparameterd=='yes') {
                $where.=" AND (".substr(trim($dat[3]),5).")";
            } else {
                die('FATAL ERROR: You have to set $allowfullwhereasdatparameterd=\'yes\'; in config.php if you want to use full WHERE clauses in parameter d of the dat-command!');
            }
        } else {
            if(strpos($notes,' LIKE ')<1) {
                if ($notes!='' && $notes!='SELF') $where.=" AND notes LIKE '%".mysql_real_escape_string($notes)."%'";
                if ($notes=='SELF') $where.=" AND authuser ='".mysql_real_escape_string(u5flatidnlower($_SERVER['PHP_AUTH_USER']))."'";
            } else {
                $noteslike=explode(' LIKE ',$notes);
                if ($notes!='' && $notes!='SELF')  {

                    $noteslike[1]=mysql_real_escape_string($noteslike[1]);
                    $noteslike[0]=mysql_real_escape_string($noteslike[0]);

                    if(strpos($noteslike[1],'||')>0||strpos($noteslike[1],'&&')>0) {
                        $noteslike[1]=str_replace("||","' OR ".$noteslike[0]." LIKE '",$noteslike[1]);
                        $noteslike[1]=str_replace("&&","' AND ".$noteslike[0]." LIKE '",$noteslike[1]);
                    }

                    $where.=" AND (".$noteslike[0]." LIKE '".$noteslike[1]."')";
                }
            }
        }

        // Fetch all data records from formdata that match the
        // the above built criteria
        $sql_a="SELECT * FROM formdata WHERE $where $orderby";
        $result_a=mysql_query($sql_a);

        if ($result_a==false && $showdatcommandsqlerrors!='no') {
            $return.=mysql_error();
        }

        //echo '<pre>';
        $num_a = mysql_num_rows($result_a);
        for ($i_a=0; $i_a<$num_a; $i_a++) {
            if($id=='?') $i_a=$num_a-1;
            $row_a = mysql_fetch_array($result_a);

            $notespart=explode('|||',$row_a['notes']);
            $row_a['notes']=$notespart[0];

            $headcsv=explode(';',$row_a['headcsv']);
            array_walk($headcsv,'subone');

            $datacsv=explode(';',$row_a['datacsv']);
            array_walk($datacsv,'subone');

            $return.= $htmlbefore;

            $fields=explode(',',$field);
            //var_dump($row_a['id']);
            //var_dump($headcsv);
            //var_dump($fields);
            //var_dump($datacsv);
            for($f=0;$f<tnuoc($fields);$f++) {
                $fields[$f]=trim($fields[$f]);
                $position=array_search($fields[$f],$headcsv);
                //var_dump($position);

                // SISSEL
                if ($position!==0) $wrongfield=1;
                else $wrongfield=0;
                if ($position>0) $wrongfield=0;

                ////////////////////////////////////////////
                $isupload=0;
                if ($renderimg!='!' && (strpos('x'.$fields[$f],'userupload')==1 && (strpos($datacsv[$position],'/fileversions/')>0 || strpos($datacsv[$position],'/r/P/')>0))) {

                    $ispu = (strpos($datacsv[$position],'/r/P/')>0) ? 1 : 0;
                    $isupload=1;
                    global $mymail;
                    global $host;
                    global $username;
                    global $password;
                    global $db;

                    $file=basename($datacsv[$position]);
                    $ext = pathinfo($datacsv[$position], PATHINFO_EXTENSION);
                    $filehash=hash('sha512',$mymail.$host.$username.$password.$db.$_SERVER['REMOTE_ADDR'].$file.date('Ymd'));

                    if ($riw!='-' && (strtolower($ext)=='jpg' || strtolower($ext)=='jpeg' || strtolower($ext)=='gif' || strtolower($ext)=='png' || strtolower($ext)=='svg')) {

                        $keepratiobymaxhw = (strpos($renderimg,'x')>0) ? 'max-' : '';

                        if($riw>0 && $rih<1) $ridim='style="'.$keepratiobymaxhw.'width:'.$riw.'px"';
                        if($riw<1 && $rih>0) $ridim='style="'.$keepratiobymaxhw.'height:'.$rih.'px"';
                        if($riw>0 && $rih>0) $ridim='style="'.$keepratiobymaxhw.'width:'.$riw.'px;'.$keepratiobymaxhw.'height:'.$rih.'px"';

                        if($riw>0 && $rih<1) $gridim='w='.($riw*$sharptillnfoldmanualzoom).'';
                        if($riw<1 && $rih>0) $gridim='h='.($rih*$sharptillnfoldmanualzoom).'';
                        if($riw>0 && $rih>0) $gridim='w='.($riw*$sharptillnfoldmanualzoom).'&h='.($rih*$sharptillnfoldmanualzoom).'';

                        if ($isorigweight && $ispu==1) {
                            $datacsv[$position]='<img class="formdataimg" src="r/P/'.$file.'?t='.filemtime('r/P/'.$file).'" '.$ridim.' />';
                        }

                        if (!$isorigweight && $ispu==1) {
                            $datacsv[$position]='<img class="formdataimg" src="thumb.php?'.$gridim.'&f=r/P/'.$file.'&t='.filemtime('r/P/'.$file).'" '.$ridim.' />';
                        }

                        if ($isorigweight && $ispu!=1) {
                            $datacsv[$position]='<img class="formdataimg" src="df.php?f='.$filehash.'" '.$ridim.' />';
                        }

                        if (!$isorigweight && $ispu!=1) {
                            $datacsv[$position]='<img class="formdataimg" src="df.php?'.$gridim.'&f='.$filehash.'" '.$ridim.' />';
                        }
                    } else {
                        if ($ispu==1) {
                            $datacsv[$position]='<a target="_blank" href="r/P/'.$file.'?t='.filemtime('r/P/'.$file).'">'.$ext.'</a>';
                        } else {
                            $datacsv[$position]='<a target="_blank" href="df.php?f='.$filehash.'">'.$ext.'</a>';
                        }
                    }
                }
                ////////////////////////////////////////////
                // Fix deprecation error if $cond is null
                $cond = $cond ?? '';

                if ($isupload==1) {
                    if ($fields[$f][0]!='?' && $wrongfield==0) $return.= mynl2br(str_replace(',.',';',$datacsv[$position]));
                    else $return.= mynl2br(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))]));
                } else {
                    if( (trim($cond)!='' || trim($output)!='') && trim($datacsv[$position]==trim($cond))) $datacsv[$position]=$output;
                    else if( (trim($cond)!='' || trim($output)!='') && trim($datacsv[$position]!=trim($cond))) $datacsv[$position]='';

                    if( (trim($cond)!='' || trim($output)!='') && trim($row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))]==trim($cond)))  $row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))]=$output;
                    else if ( (trim($cond)!='' || trim($output)!='') && trim($row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))]!=trim($cond))) $row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))]='';

                    if( (trim($cond)=='' && trim($output)=='') ) {
                        if($rhtml<1) {
                            if ($fields[$f][0]!='?' && $wrongfield==0) $return.= islnfy(mynl2br(str_replace('&amp;#','&#',htmlXentities(str_replace(',.',';',$datacsv[$position])))));
                            else $return.= islnfy(mynl2br(str_replace('&amp;#','&#',htmlXentities(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))])))));
                        }

                        else if($rhtml==1) {
                            if ($fields[$f][0]!='?' && $wrongfield==0) $return.= islnfy(mynl2br(str_replace('&amp;#','&#',htmlXentities(str_replace(',.',';',$datacsv[$position])))));
                            else $return.= islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))])))));
                        }

                        else if($rhtml==2) {
                            if ($fields[$f][0]!='?' && $wrongfield==0) $return.= islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace('&lt;','<',str_replace('&gt;','>',str_replace(',.',';',$datacsv[$position])))))));
                            else $return.= islnfy(mynl2br(str_replace('&amp;#','&#',htmlXentities(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))])))));
                        }

                        else if($rhtml==3) {
                            if ($fields[$f][0]!='?' && $wrongfield==0) $return.= islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace('&lt;','<',str_replace('&gt;','>',str_replace(',.',';',$datacsv[$position])))))));
                            else $return.= islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))])))));
                        }

                        else if($rhtml==4) {
                            if ($fields[$f][0]!='?' && $wrongfield==0) $return.= render(islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace(',.',';',$datacsv[$position]))))));
                            else $return.= islnfy(mynl2br(str_replace('&amp;#','&#',htmlXentities(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))])))));
                        }

                        else if($rhtml==5) {
                            if ($fields[$f][0]!='?' && $wrongfield==0) $return.= islnfy(str_replace('&amp;#','&#',(str_replace('&lt;','<',str_replace('&gt;','>',str_replace('&quot;','"',render(str_replace(',.',';',$datacsv[$position]))))))));
                            else $return.= islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))])))));
                        }
                    } else {
                        if ($fields[$f][0]!='?' && $wrongfield==0) $return.= islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace('&lt;','<',str_replace('&gt;','>',str_replace(',.',';',$datacsv[$position])))))));
                        else $return.= islnfy(mynl2br(str_replace('&amp;#','&#',(str_replace(',.',';',$row_a[str_replace('sent','humantime',substr(strtolower($fields[$f]),1))])))));
                    }
                }

                if(is_array($htmlbetween)) {
                    if($f<tnuoc($fields)-1 && tnuoc($fields)>1) {
                        $return.=$htmlbetween[$f];
                    }
                } else {
                    if($f<tnuoc($fields)-1 && tnuoc($fields)>1) {
                        $return.=$htmlbetween;
                    }
                }

            }

            ///
            $return.=$htmlafter;
            $return=str_replace('$id',$row_a['id'],$return);
            $return=str_replace('$st',$row_a['status'],$return);
            $return=str_replace('$se',$row_a['time'],$return);

            if ($row_a['lastmut']<1) {
                $lastmut='&mdash;';
            } else {
                $lastmut=date('Y-m-d H:i:s',$row_a['lastmut']);
            }

            $return=str_replace('$la',$lastmut,$return);
            $rowaip=$row_a['ip'];
            $rowaip=explode(' ',$rowaip);
            $rowaip=$rowaip[0];
            $return=str_replace('$ip',$rowaip,$return);
            $return=str_replace('$hu',$row_a['humantime'],$return);
            $return=str_replace('$au',$row_a['authuser'],$return);
            $return=str_replace('$ah',md5(strtolower($row_a['authuser'])),$return);
        }
        $inclbr=array('<br /><tr>','<tr><br />','<br /></tr>','</tr><br />','<br /><td>','</td><br />');
        $exclbr=array('<tr>'      ,'<tr>'      ,'</tr>'      ,'</tr>'      ,'<td>'      ,'</td>');
        return str_replace($inclbr,$exclbr,$return);
    }
}

function subone(&$item1) {
    $item1=substr($item1,1);
}

function mynl2br($str) {
    global $mynl2br;
    if(trim($mynl2br)=='') $mynl2br=' | ';
    return str_replace('<br />',htmlX_entity_decode($mynl2br),nl2br($str));
}

function islnfy($s) {
    global $lfyonoff;
    if($lfyonoff[1]=='!')return linkify($s);
    else return $s;
}

function linkify($input) {
    $re = <<<'REGEX'
!
    (
      <\w++
      (?:
        \s++
      | [^"'<>]++
      | "[^"]*+"
      | '[^']*+'
      )*+
      >
    )
    |
    (\b https?://[^\s"'<>]++ )
    |
    (\b www\d*+\.\w++[^\s"'<>]++ )
!xi
REGEX;

    return preg_replace_callback($re, function($m){
        if($m[1]) return $m[1];
        $url = $m[2] ? $m[2] : "http://$m[3]";
        $text = $m[2].$m[3];
        $url=trim($url," \t\n\r\0\x0B()[]{}.?;:,");
        $srchtfrnd=array('.</a>','?</a>',':</a>',',</a>',')</a>',']</a>','}</a>','&amp;gt;</a>',"&amp;gt;'>");
        $rplctwnd= array('</a>.','</a>?','</a>:','</a>,','</a>)','</a>]','</a>}','</a>&gt;',          "'>");
        global $lfyonoff;

        if(shorten($text)!=$text){
            if(in_array($text[strlen($text)-3],array(")","]","}")))$end=$text[strlen($text)-3].$text[strlen($text)-2].$text[strlen($text)-1];
            else if(in_array($text[strlen($text)-2],array(")","]","}")))$end=$text[strlen($text)-2].$text[strlen($text)-1];
            else if(in_array($text[strlen($text)-1],array(")","]","}")))$end=$text[strlen($text)-1];
        }
        else $end='';

        if($lfyonoff[0]==4){$ishL='[h:]';$ishR='[:h]';};
        return $ishL.str_replace($srchtfrnd,$rplctwnd,str_replace($srchtfrnd,$rplctwnd,str_replace($srchtfrnd,$rplctwnd,"<a target='_blank' href='$url'>".shorten($text)."</a>$end"))).$ishR;
    },
        $input);
}

function shorten($text) {
    if (strlen($text) > 40) {
        return substr($text,0,35).'&hellip;';
    } else {
        return $text;
    }
}

function u5xcap($text,$album,$ia) {
/*
         $title = '';
   if (def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']) != '') $title = 'data-caption="' . rawurlencode(render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5']))) . '"';
str_replace('title="','title="('.$ia.'/'.(tnuoc($album)-1).') ',$title).'
}
 */
    $img=$album[$ia];
    $img=explode('_',$img);
    $img=explode('.',$img[1]);
    $img=$img[0].'>>>';

    $intro='';
    if(strpos($text,'[ca:]')>0) {
        $intro=explode('[ca:]',$text);
        $intro=$intro[0];
    }

    $outro='';
    if(strpos($text,'[:ca]')>0) {
        $outro=explode('[:ca]',$text);
        $outro=$outro[1];
    }

    $middle='';
    if(strpos($text,$img)>0) {
        $middle=explode($img,$text);
        $middle=$middle[1];
        if(strpos($middle,'[ca]')>0){
            $middle=explode('[ca]',$middle);
            $middle=$middle[0];
        }
        $middle=explode('[/]',$middle);
        array_pop($middle);
        $middle=implode('[/]',$middle);
    }
    $counter='';
    $string=$intro.$middle.$outro;
    if($string[0]=='#'){
        $string=substr($string, 1);
        $counter=$ia.'/'.(tnuoc($album)-1).' ';
    }
    return rawurlencode(render($counter.$string));
}