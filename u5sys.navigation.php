<?php
$naviecho = '<ul>';
require_once('connect.inc.php');
require_once('render.inc.php');
$sql_b    = "SELECT * FROM inserts WHERE source1 NOT like '[' AND source1 NOT like '[h:]' ORDER BY length(source1) DESC";
$result_b = mysql_query($sql_b);
if ($result_b == false) {
    $naviecho .= 'SQL_b-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_b . '</font><p>';
}
$num_b = mysql_num_rows($result_b);
if ($_GET['c'] == '') {
    $sql_a    = "SELECT * FROM resources WHERE deleted!=1 AND ishomepage=1";
    $result_a = mysql_query($sql_a);
    if ($result_a == false) {
        $naviecho .= 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
    }
    $row_a     = mysql_fetch_array($result_a);
    $_GET['c'] = $row_a['name'];
}
$sql_a    = "SELECT * FROM resources WHERE deleted!=1 AND name='navigation'";
$result_a = mysql_query($sql_a);
if ($result_a == false) {
    $naviecho .= 'SQL_a-Query failed!<p>' . mysql_error() . '<p><font color=red>' . $sql_a . '</font><p>';
}
$row_a  = mysql_fetch_array($result_a);
////////////////////////////////////////////////////////////////////////////////////////////
$string = def($row_a['content_d'], $row_a['content_e'], $row_a['content_f']);
$string = explode('<br />', nl2br($string));
$z1     = 0;
$oldz1  = 0;
$z2     = 0;
$oldz2  = 0;
$z3     = 0;
$oldz3  = 0;
$z4     = 0;
for ($i = 0; $i < count($string); $i++) {
    $string[$i] = trim($string[$i]);
    if (trim($string[$i]) != '') {
        if (str_replace('##[', '', $string[$i]) == $string[$i] && str_replace('###[', '', $string[$i]) == $string[$i] && str_replace('####[', '', $string[$i]) == $string[$i]) { // if level 1
            $z1++;
            $z2        = 0;
            $oldz2     = 0;
            $tokens    = explode(':', $string[$i]);
            $name      = $tokens[count($tokens) - 1];
            $name      = str_replace(']', '', $name);
            $name      = str_replace('[', '', $name);
            $name      = str_replace(':', '', $name);
            $tokens    = explode(':', $string[$i], -1);
            $human     = implode(':', $tokens);
            $human     = str_replace(']', '', $human);
            $human     = str_replace('####[', '', $human);
            $human     = str_replace('###[', '', $human);
            $human     = str_replace('##[', '', $human);
            $human     = str_replace('#[', '', $human);
            $pack[$z1] = ',' . trim($name) . ',';
        } else if (str_replace('##[', '', $string[$i]) != $string[$i] && str_replace('###[', '', $string[$i]) == $string[$i] && str_replace('####[', '', $string[$i]) == $string[$i]) { // if level 2
            $z2++;
            $z3     = 0;
            $oldz3  = 0;
            $tokens = explode(':', $string[$i]);
            $name   = $tokens[count($tokens) - 1];
            $name   = str_replace(']', '', $name);
            $name   = str_replace('[', '', $name);
            $name   = str_replace(':', '', $name);
            $tokens = explode(':', $string[$i], -1);
            $human  = implode(':', $tokens);
            $human  = str_replace(']', '', $human);
            $human  = str_replace('####[', '', $human);
            $human  = str_replace('###[', '', $human);
            $human  = str_replace('##[', '', $human);
            $human  = str_replace('#[', '', $human);
            $pack[$z1] .= ',' . trim($name) . ',';
            $pack2[$z1 . $z2] = ',' . trim($name) . ',';
        } else if (str_replace('###[', '', $string[$i]) != $string[$i] && str_replace('####[', '', $string[$i]) == $string[$i]) { // if level 3
            $z3++;
            $z4     = 0;
            $tokens = explode(':', $string[$i]);
            $name   = $tokens[count($tokens) - 1];
            $name   = str_replace(']', '', $name);
            $name   = str_replace('[', '', $name);
            $name   = str_replace(':', '', $name);
            $tokens = explode(':', $string[$i], -1);
            $human  = implode(':', $tokens);
            $human  = str_replace(']', '', $human);
            $human  = str_replace('####[', '', $human);
            $human  = str_replace('###[', '', $human);
            $human  = str_replace('##[', '', $human);
            $human  = str_replace('#[', '', $human);
            $pack[$z1] .= ',' . trim($name) . ',';
            $pack2[$z1 . $z2] .= ',' . trim($name) . ',';
            $pack3[$z1 . $z2 . $z3] .= ',' . trim($name) . ',';
        } else if (str_replace('####[', '', $string[$i]) != $string[$i]) { // if level 4
            $z4++;
            $tokens = explode(':', $string[$i]);
            $name   = $tokens[count($tokens) - 1];
            $name   = str_replace(']', '', $name);
            $name   = str_replace('[', '', $name);
            $name   = str_replace(':', '', $name);
            $tokens = explode(':', $string[$i], -1);
            $human  = implode(':', $tokens);
            $human  = str_replace(']', '', $human);
            $human  = str_replace('####[', '', $human);
            $human  = str_replace('###[', '', $human);
            $human  = str_replace('##[', '', $human);
            $human  = str_replace('#[', '', $human);
            $pack[$z1] .= ',' . trim($name) . ',';
            $pack2[$z1 . $z2] .= ',' . trim($name) . ',';
            $pack3[$z1 . $z2 . $z3] .= ',' . trim($name) . ',';
        }
    }
}
////////////////////////////////////////////////////////////////////////////////////////////
$string       = def($row_a['content_d'], $row_a['content_e'], $row_a['content_f']);
$string       = explode('<br />', nl2br($string));
$z1           = 0;
$oldz1        = 0;
$z2           = 0;
$oldz2        = 0;
$z3           = 0;
$oldz3        = 0;
$wassub       = 0;
$sub          = 0;
$wassubsub    = 0;
$subsub       = 0;
$wassubsubsub = 0;
$subsubsub    = 0;

for ($i = 0; $i < count($string); $i++) {
    $string[$i] = trim($string[$i]);
    if (trim($string[$i]) != '') {
        if (str_replace('##[', '', $string[$i]) == $string[$i] && str_replace('###[', '', $string[$i]) == $string[$i] && str_replace('####[', '', $string[$i]) == $string[$i]) { // if level 1
            $z1++;
            $z2        = 0;
            $oldz2     = 0;
            $z3        = 0;
            $oldz3     = 0;
            $z4        = 0;
            $sub       = 0;
            $subsub    = 0;
            $subsubsub = 0;
            if ($wassub == 1) {
                $naviecho .= '</ul>';
                $wassub = 0;
            }
            if ($wassubsub == 1) {
                $naviecho .= '</ul>';
                $wassubsub = 0;
            }
            if ($wassubsubsub == 1) {
                $naviecho .= '</ul>';
                $wassubsubsub = 0;
            }
            
            $tokens     = explode(':', $string[$i]);
            $name       = $tokens[count($tokens) - 1];
            $name       = str_replace(']', '', $name);
            $name       = str_replace('[', '', $name);
            $name       = str_replace(':', '', $name);
            $tokens     = explode(':', $string[$i], -1);
            $human      = implode(':', $tokens);
            $human      = str_replace(']', '', $human);
            $human      = str_replace('####[', '', $human);
            $human      = str_replace('###[', '', $human);
            $human      = str_replace('##[', '', $human);
            $human      = str_replace('#[', '', $human);
            $here       = '';
            $activeitem = 'class="inactive"';
            if (str_replace(',' . $_GET['c'] . ',', '', $pack[$z1]) != $pack[$z1]) {
                $here       = 'class="activeItem"';
                $activeitem = 'class="active"';
            }
            $div = '';
            if ($z1 > 1)
                $div = '</li>';
            if ($sub == 1) {
                $subul = '</ul>';
                $sub   = 0;
            }
            $divstyle = '';
            if (str_replace(',' . $_GET['c'] . ',', '', $pack[$z1]) == $pack[$z1])
                $divstyle = 'style="display:none"';
            if (trim($human) != '')
                $naviecho .= $subul . $div . '<li ' . $activeitem . '><a ' . $here . '  href="index.php?c=' . $name . '&amp;l=' . $_GET['l'] . '">' . render($human) . '</a>';
        }
        
        
        else if (str_replace('##[', '', $string[$i]) != $string[$i] && str_replace('###[', '', $string[$i]) == $string[$i] && str_replace('####[', '', $string[$i]) == $string[$i]) { // if level 2
            $z2++;
            $z3        = 0;
            $z4        = 0;
            $subsub    = 0;
            $subsubsub = 0;
            if ($wassubsub == 1) {
                $naviecho .= '</ul>';
                $wassubsub = 0;
            }
            if ($wassubsubsub == 1) {
                $naviecho .= '</ul>';
                $wassubsubsub = 0;
            }
            
            
            $wassub = 1;
            $tokens = explode(':', $string[$i]);
            $name   = $tokens[count($tokens) - 1];
            $name   = str_replace(']', '', $name);
            $name   = str_replace('[', '', $name);
            $name   = str_replace(':', '', $name);
            $tokens = explode(':', $string[$i], -1);
            $human  = implode(':', $tokens);
            $human  = str_replace(']', '', $human);
            $human  = str_replace('####[', '', $human);
            $human  = str_replace('###[', '', $human);
            $human  = str_replace('##[', '', $human);
            $human  = str_replace('#[', '', $human);
            $here   = '';
            if ($_GET['c'] == $name) {
                $here = 'class="activeItem"';
            }
            
            if (str_replace(',' . $_GET['c'] . ',', '', $pack2[$z1 . $z2]) != $pack2[$z1 . $z2]) {
                $here       = 'class="activeItem"';
                $activeitem = 'class="active"';
            }
            
            
            if ($oldz1 != $z1) {
                $naviecho .= '<ul ' . $divstyle . '>';
                $sub = 1;
            }
            if (trim($human) != '')
                $naviecho .= '<li class="subItem"><a ' . $here . ' href="index.php?c=' . $name . '&amp;l=' . $_GET['l'] . '">' . render($human) . '</a></li>';
            $oldz1 = $z1;
        }
        
        
        else if (str_replace('###[', '', $string[$i]) != $string[$i] && str_replace('####[', '', $string[$i]) == $string[$i]) { // if level 3
            $z3++;
            $wassubsub = 1;
            $z4        = 0;
            $subsubsub = 0;
            if ($wassubsubsub == 1) {
                $naviecho .= '</ul>';
                $wassubsubsub = 0;
            }
            
            
            $tokens = explode(':', $string[$i]);
            $name   = $tokens[count($tokens) - 1];
            $name   = str_replace(']', '', $name);
            $name   = str_replace('[', '', $name);
            $name   = str_replace(':', '', $name);
            $tokens = explode(':', $string[$i], -1);
            $human  = implode(':', $tokens);
            $human  = str_replace(']', '', $human);
            $human  = str_replace('####[', '', $human);
            $human  = str_replace('###[', '', $human);
            $human  = str_replace('##[', '', $human);
            $human  = str_replace('#[', '', $human);
            $here   = '';
            if ($_GET['c'] == $name) {
                $here = 'class="activeItem"';
            }
            $divstyle2 = '';
            if (str_replace(',' . $_GET['c'] . ',', '', $pack2[$z1 . $z2]) == $pack2[$z1 . $z2])
                $divstyle2 = 'style="display:none"';
            
            if ($oldz2 != $z2) {
                $naviecho .= '<ul ' . $divstyle2 . '>';
                $subsub = 1;
            }
            
            if (trim($human) != '')
                $naviecho .= '<li class="subSubItem" ' . $divstyle2 . '><a ' . $here . ' href="index.php?c=' . $name . '&amp;l=' . $_GET['l'] . '">' . render($human) . '</a></li>';
            $oldz2 = $z2;
        }
        
        
        else if (str_replace('####[', '', $string[$i]) != $string[$i]) { // if level 4
            $z4++;
            $wassubsubsub = 1;
            $tokens       = explode(':', $string[$i]);
            $name         = $tokens[count($tokens) - 1];
            $name         = str_replace(']', '', $name);
            $name         = str_replace('[', '', $name);
            $name         = str_replace(':', '', $name);
            $tokens       = explode(':', $string[$i], -1);
            $human        = implode(':', $tokens);
            $human        = str_replace(']', '', $human);
            $human        = str_replace('####[', '', $human);
            $human        = str_replace('###[', '', $human);
            $human        = str_replace('##[', '', $human);
            $human        = str_replace('#[', '', $human);
            $here         = '';
            if ($_GET['c'] == $name) {
                $here = 'class="activeItem"';
            }
            $divstyle3 = '';
            if (str_replace(',' . $_GET['c'] . ',', '', $pack3[$z1 . $z2 . $z3]) == $pack3[$z1 . $z2 . $z3])
                $divstyle3 = 'style="display:none"';
            
            if ($oldz3 != $z3) {
                $naviecho .= '<ul ' . $divstyle3 . '>';
                $subsubsub = 1;
            }
            
            if (trim($human) != '')
                $naviecho .= '<li class="subsubSubItem" ' . $divstyle3 . '><a ' . $here . ' href="index.php?c=' . $name . '&amp;l=' . $_GET['l'] . '">' . render($human) . '</a></li>';
            $oldz3 = $z3;
        }
        
    }
}

if ($z1 > 1)
    $naviecho .= '</li>';
$naviecho .= '</ul>';
$naviecho = str_replace('<ul ></ul>', '', $naviecho);
echo $naviecho;
?>