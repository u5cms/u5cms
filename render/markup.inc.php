<?php
/* Ordered insert expansion; preserve the historical escaping and [h:] boundaries. */
function u5RenderMarkup($stringa) {
    global $result_b, $num_b, $autocreateparagraphs;
    $ids = 1;
    $stringa = str_replace('[h:]', '!_-q-_!<!--[h:]-->', $stringa);
    $stringa = str_replace('[:h]', '<!--[:h]-->!_-q-_!', $stringa);
    $stringa = explode('!_-q-_!', $stringa);

    for ($iii = 0;$iii < count($stringa);$iii++) {

        $string = $stringa[$iii];

        if (str_replace('[h:]', '', $stringa[$iii]) == $stringa[$iii]) {

            $string = nl2br(ehtml($string));
            if ($result_b !== null) {
                mysql_data_seek($result_b, 0);
            }

            for ($i_b = 0;$i_b < $num_b;$i_b++) {

                $row_b = mysql_fetch_array($result_b);
                $string = is_null($row_b['source1']) ? $string : str_replace(($row_b['source1'] ?? ''), '!_-q-_!' . $row_b['source1'], $string);
                $string = is_null($row_b['source2']) ? $string : str_replace(($row_b['source2'] ?? ''), $row_b['source2'] . '!_-q-_!', $string);
                $string = is_null($string) ? $string : explode('!_-q-_!', $string);

                for ($ii = 0;$ii < count($string);$ii++) {

                    if (str_replace(($row_b['source1'] ?? ''), '', $string[$ii]) != $string[$ii]) {
                        $betweensources = is_null($row_b['betweensources']) ? array() : explode(',', ($row_b['betweensources'] ?? ''));
                        $betweentargets = is_null($row_b['betweentargets']) ? array() : explode(',', ($row_b['betweentargets'] ?? ''));
                        //if (!is_null($betweensources) && ! is_null($betweentargets)) {
                        $string[$ii] = str_replace($betweensources, $betweentargets, $string[$ii]);
                        //}
                    }

                    if (str_replace(($row_b['source1'] ?? ''), '', $string[$ii]) != $string[$ii]) {
                        $nobr = '';

                        if ($row_b['isblockelement'] == 1) $nobr = '<!--nobr-->';

                        $string[$ii] = is_null($row_b['source1']) ? $string[$ii] : str_replace(($row_b['source1'] ?? ''), $nobr . $row_b['target1'], $string[$ii]);
                        $string[$ii] = is_null($row_b['source2']) ? $string[$ii] : str_replace(($row_b['source2'] ?? ''), $row_b['target2'] . $nobr, $string[$ii]);

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
    return $stringa;
}
