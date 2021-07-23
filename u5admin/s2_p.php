<?php 
        if ($row_a['name'] != 'navigation') $Rrename = 'R';
        else $Rrename = '';
        echo '<tr onmouseover="this.style.background=\'yellow\'" onmouseout="this.style.background=\'#ffffcc\'" id="tr1_' . $row_a['name'] . '" bgcolor="#FFFFCC">

<td style="background:#ffcc66" id="tdL_' . $row_a['name'] . '"><a title="insert as link in the left editor" href="javascript:void(0)" onclick="parent.i1.doteleins(\'linktext:' . $row_a['name'] . '\')">&lt;</a>
</td>

<td style="background:#ffcc66" id="tdR_' . $row_a['name'] . '"><a title="insert as link in the right editor" href="javascript:void(0)" onclick="parent.i2.doteleins(\'linktext:' . $row_a['name'] . '\')">&lt;</a>
</td>

<td width="99%" style="word-break:break-all" title="Open right (+Alt=left). ' . date('Ymd Hi', $row_a['lastmut']) . ' ' . $row_a['operator'] . ' ' . ehtml(substr($row_a['content_d'], 0, 150)) . '"><a id="a_' . $row_a['name'] . '" href="javascript:void(0)" onclick="if(event.altKey)parent.i1.lgotopage(\'' . $row_a['name'] . '\');else parent.i2.gotopage(\'' . $row_a['name'] . '\')">' . $row_a['name'] . '</a>' . $pend . '
</td>

<td>
<a title="view" href="javascript:void(0)" onclick="window.open(\'../?c=' . $row_a['name'] . '&l=\'+parent.i2.lansel)">V</a>
</td>

<td>
<a title="rename" href="javascript:void(0)" onclick="f1=window.open(\'rename.php?name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">' . $Rrename . '</a>
</td>

<td>
<a title="localize (where linked in)" href="javascript:void(0)" onclick="f1=window.open(\'localize.php?name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">' . $lspan1 . 'L' . $lspan2 . '</a>
</td>

<td>
<a title="copy" href="javascript:void(0)" onclick="f1=window.open(\'copy.php?name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">C</a>
</td>

<td>
<a title="delete or define archive status" href="javascript:void(0)" onclick="f1=window.open(\'delete.php?name=' . md5($row_a['name']) . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">D</a>
</td>

<td>

</td>
</tr>';

        if ($_GET['pvs_p'] == 'on') {
            echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#FFFFCC"><td colspan="9" style="word-break:break-all">';
            echo ehtml(substr($row_a['content_d'], 0, 150));
            if (strlen(trim($row_a['content_d'])) > 150) echo '&hellip;';
            echo '<hr></td></tr>';
        } else echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#ffffff"><td colspan="9"></td></tr>';
?>