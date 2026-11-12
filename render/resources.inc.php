<?php
/* PIDVESA reference expansion. Syntax and lookup order are compatibility contracts. */
function u5RenderResources($stringa) {
    global $usesessioninsteadofbasicauth;
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

    $stringa = str_replace('[', '!_-q-_![', $stringa);
    $stringa = str_replace(']', ']!_-q-_!', $stringa);
    $stringa = str_replace(']!_-q-_!]!_-q-_!', ']]', $stringa);
    $stringa = explode('!_-q-_!', $stringa);

    for ($i = 0;$i < count($stringa);$i++) {
        if (strpos($stringa[$i], '[') === 0 && $stringa[$i] != '[h:]' && $stringa[$i] != '[:h]') {

            $tokens = explode(':',$stringa[$i]);
            $name=$tokens[count($tokens)-1];
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
                echo 'SQL_a-Query failed!...!<p>';
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
                        $ext=$ext[count($ext)-1];

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

                        if (substr(def($file_1,$file_2,$file_3,$file_4,$file_5),strlen(($row_a['name'] ?? ''))-1,1)!='!') {
                            $stringa[$i].= '<'.$audiovideo.' preload="'.$audiovideopreload.'" style="'.u5RenderMediaStyle($plwi,$plhe,$audiovideo).'" '.$poster.' controls '.$html5autoplay.'>';

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

                        if (substr(def($file_1,$file_2,$file_3,$file_4,$file_5),strlen(($row_a['name'] ?? ''))-1,1)!='!') {
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
                            <iframe title="YouTube video player" width="'.$smallimgC_w.'" height="'.(ceil($smallimgC_w*0.8)).'"'.u5RenderFrameAttributes($smallimgC_w,(ceil($smallimgC_w*0.8))).' src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[::::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$smallimgR_w.'px" class="imgBoxRight dlRightSmall"><dt>
                            <iframe title="YouTube video player" width="'.$smallimgR_w.'" height="'.(ceil($smallimgR_w*0.8)).'"'.u5RenderFrameAttributes($smallimgR_w,(ceil($smallimgR_w*0.8))).' src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[:::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$smallimgL_w.'px" class="imgBoxLeft dlLeftSmall"><dt>
                            <iframe title="YouTube video player" width="'.$smallimgL_w.'" height="'.(ceil($smallimgL_w*0.8)).'"'.u5RenderFrameAttributes($smallimgL_w,(ceil($smallimgL_w*0.8))).' src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[::', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$youtube_w.'px" class="imgBoxCenter dlYoutubeLarge"><dt>
                            <iframe title="YouTube video player" width="'.$youtube_w.'" height="'.$youtube_h.'"'.u5RenderFrameAttributes($youtube_w,$youtube_h).' src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } elseif (str_replace('[:', '', $stringa[$i]) != $stringa[$i]) {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$youtube_w.'px" class="imgBoxRight dlYoutubeLarge"><dt>
                            <iframe title="YouTube video player" width="'.$youtube_w.'" height="'.$youtube_h.'"'.u5RenderFrameAttributes($youtube_w,$youtube_h).' src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
                            </dt>
                            <dd>' . render(def($row_a['content_1'], $row_a['content_2'], $row_a['content_3'], $row_a['content_4'], $row_a['content_5'])) .'</dd>
                            </dl>
';
                    ////////////////////
                    } else {
                        $stringa[$i] = '<dl '.$title.' style="width:'.$youtube_w.'px" class="imgBoxLeft dlYoutubeLarge"><dt>
                            <iframe title="YouTube video player" width="'.$youtube_w.'" height="'.$youtube_h.'"'.u5RenderFrameAttributes($youtube_w,$youtube_h).' src="'.$videoportalegyoutubeembedurl.''.def($row_a['desc_1'],$row_a['desc_2'],$row_a['desc_3'],$row_a['desc_4'],$row_a['desc_5']).'" frameborder="0" allowfullscreen></iframe>
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

                        for ($ia=1;$ia<count($album);$ia++) {
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
                        for ($ia=1;$ia<count($album);$ia++) {
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

                        $stringa[$i].= '</dt><dd>Total '.(count($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5)
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
                        for ($ia=1;$ia<count($album);$ia++) {
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
                        $stringa[$i].= '</dt><dd>Total '.(count($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:7px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

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
                        for ($ia=1;$ia<count($album);$ia++) {
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
                        for ($ia=1;$ia<count($album);$ia++) {
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
                        $stringa[$i].= '</dt><dd>Total '.(count($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:5px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

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
                        for ($ia=1;$ia<count($album);$ia++) {
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

                        $stringa[$i].= '</dt><dd>Total '.(count($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:5px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

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
                        for ($ia=1;$ia<count($album);$ia++) {
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
                        for ($ia=1;$ia<count($album);$ia++) {
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

                        $stringa[$i].= '</dt><dd>Total '.(count($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:7px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';

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
                        for ($ia=1;$ia<count($album);$ia++) {
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
                        $stringa[$i].= '</dt><dd>Total '.(count($album)-1).' '.def($picsfound_1,$picsfound_2,$picsfound_3,$picsfound_4,$picsfound_5).'<div style="padding-top:5px">'. render(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) .'</div></dd></dl>';
                    }
                }

                ///////////////////////////////////////
                /// Render an external link element ///
                ///////////////////////////////////////
                if ($row_a['typ'] == 'e') {

                    // URLs may already contain HTML entities because u5CMS stores
                    // text in its historical WINDOWS-1252/Latin-1 environment.
                    // Decode one stored HTML-entity layer before escaping the URL
                    // again for the HTML attribute. Numeric entities for characters
                    // outside WINDOWS-1252 remain untouched here and are decoded by
                    // the browser when the href is parsed. Existing %XX and %uXXXX
                    // sequences are deliberately left unchanged.
                    $externalurl = htmlX_entity_decode(def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']));

                    // If a non-WINDOWS-1252 character was stored as a numeric
                    // HTML character reference and that reference itself was
                    // HTML-escaped, collapse only the nested ampersand layer.
                    // Ordinary query separators, named entities, %XX and %uXXXX
                    // are deliberately not normalized here.
                    $externalurl = preg_replace(
                        '/&(?:amp;)+#(x[0-9a-f]+|[0-9]+);/i',
                        '&#$1;',
                        $externalurl
                    );

                    $title = '';
                    if ($externalurl != '') {
                        $title = 'title="' . ehtml($externalurl) . '"';
                    }

                    if ($human=='' || $human=='linktext') $human=ehtml(def($row_a['title_1'], $row_a['title_2'], $row_a['title_3'], $row_a['title_4'], $row_a['title_5']));
                    if ($human=='' || $human=='linktext') $human='LINK';

                    $extern='';
                    $isblank='';

                    if(str_replace('@','',def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']))==def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']) && str_replace('javascript:','',def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5']))==def($row_a['desc_1'], $row_a['desc_2'], $row_a['desc_3'], $row_a['desc_4'], $row_a['desc_5'])) {
                        $extern= '<span style="font-size:80%;text-decoration:none"><img src="images/extern.svg" style="height:0.9em;vertical-align:baseline;margin-left:3px" /></span>';
                        $isblank='target="_blank"';
                    }

					$stringa[$i] = '<a '.$isblank.' ' . $title . ' href="' . ehtml($externalurl) . '">' . str_replace('<nobr>;',';<nobr>&#8288;',substr($human,0,-1).'<nobr>'.$human[strlen($human)-1]) . $extern . '</nobr></a>';
                }
            }
        }
    }

    return implode('', $stringa);
}
