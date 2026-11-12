<?php
/* Existing dat a-n semantics and helper APIs, moved without behavior changes. */

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
        $return='';
        $dat=array_pad(explode('|',$human),14,'');

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
        $orderSpec=trim($dat[8]);
        if (substr($orderSpec,0,1)==='?') {
            u5ValidateTextRequest($_GET,array('sort'));
            if (strpos(($_GET['sort'] ?? ''),'ORDER BY')===0) $orderby=mysql_real_escape_string($_GET['sort']);
            $limit=trim(substr($orderSpec,1));
            if ($limit!=='') $orderby.=' '.mysql_real_escape_string($limit);
        } elseif ($orderSpec!=='') {
            $orderby=(strpos($orderSpec,'LIMIT')===0 ? '' : 'ORDER BY ').mysql_real_escape_string($orderSpec);
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

        if ($id=='?') {
            u5ValidateTextRequest($_GET,array('id'));
            if (($_GET['id'] ?? 0)>0) $id=$_GET['id'];
        }

        if ($notes=='?') {
            u5ValidateTextRequest($_GET,array('notes'));
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
        $num_a = $result_a===false ? 0 : mysql_num_rows($result_a);
        for ($i_a=0; $i_a<$num_a; $i_a++) {
            if($id=='?') $i_a=$num_a-1;
            $row_a = mysql_fetch_array($result_a);

            $notespart=explode('|||',($row_a['notes'] ?? ''));
            $row_a['notes']=$notespart[0];

            $headcsv=explode(';',($row_a['headcsv'] ?? ''));
            array_walk($headcsv,'subone');

            $datacsv=explode(';',($row_a['datacsv'] ?? ''));
            array_walk($datacsv,'subone');

            $return.= $htmlbefore;

            $fields=explode(',',$field);
            //var_dump($row_a['id']);
            //var_dump($headcsv);
            //var_dump($fields);
            //var_dump($datacsv);
            for($f=0;$f<count($fields);$f++) {
                $fields[$f]=trim($fields[$f]);
                $position=array_search($fields[$f],$headcsv);
                $systemField=str_replace('sent','humantime',substr(strtolower($fields[$f]),1));
                if (!isset($row_a[$systemField])) $row_a[$systemField]='';
                if (!isset($datacsv[$position])) $datacsv[$position]='';
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
                    u5ValidateTextRequest($_COOKIE,array('u','p'));
                    if($sticksessiontoip=='yes')$serverremoteaddr=$_SERVER['REMOTE_ADDR'].$_COOKIE['u'].$_COOKIE['p'];
                    else $serverremoteaddr=$_COOKIE['u'].$_COOKIE['p'];
                    $filehash=sha1($mymail.$host.$username.$password.$db.$serverremoteaddr.$file.date('Ymd')).'_URL_NON_TRANSFERABLE_AND_TIME_LIMITED';

                    if ($riw!='-' && (strtolower($ext)=='jpg' || strtolower($ext)=='jpeg' || strtolower($ext)=='jpe' || strtolower($ext)=='jfif' || strtolower($ext)=='pjpeg' || strtolower($ext)=='pjp' || strtolower($ext)=='gif' || strtolower($ext)=='png' || strtolower($ext)=='apng' || strtolower($ext)=='svg' || strtolower($ext)=='webp' || strtolower($ext)=='avif' || strtolower($ext)=='bmp' || strtolower($ext)=='ico' || strtolower($ext)=='cur')) {

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
                    if($f<count($fields)-1 && count($fields)>1) {
                        $return.=$htmlbetween[$f];
                    }
                } else {
                    if($f<count($fields)-1 && count($fields)>1) {
                        $return.=$htmlbetween;
                    }
                }

            }

            ///
            $return.=$htmlafter;
            $return=str_replace('$id',($row_a['id'] ?? ''),$return);
            $return=str_replace('$st',($row_a['status'] ?? ''),$return);
            $return=str_replace('$se',($row_a['time'] ?? ''),$return);

            if ($row_a['lastmut']<1) {
                $lastmut='&mdash;';
            } else {
                $lastmut=date('Y-m-d H:i:s',$row_a['lastmut']);
            }

            $return=str_replace('$la',$lastmut,$return);
            $rowaip=$row_a['ip'] ?? '';
            $rowaip=explode(' ',$rowaip);
            $rowaip=$rowaip[0];
            $return=str_replace('$ip',$rowaip,$return);
            $return=str_replace('$hu',($row_a['humantime'] ?? ''),$return);
            $return=str_replace('$au',($row_a['authuser'] ?? ''),$return);
            $return=str_replace('$ah',md5(strtolower(($row_a['authuser'] ?? ''))),$return);
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
str_replace('title="','title="('.$ia.'/'.(count($album)-1).') ',$title).'
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
        $counter=$ia.'/'.(count($album)-1).' ';
    }
    return rawurlencode(render($counter.$string));
}
