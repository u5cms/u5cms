<?php
        echo '<tr onmouseover="this.style.background=\'yellow\'" onmouseout="this.style.background=\'#ffffcc\'" id="tr1_' . $row_a['name'] . '" bgcolor="#FFFFCC">

<td style="background:#ffcc66" id="tdL_' . $row_a['name'] . '"><a title="insert in left editor" href="javascript:void(0)" onclick="parent.i1.doteleins(\'' . $row_a['name'] . '\')">&lt;</a>
</td>

<td style="background:#ffcc66" id="tdR_' . $row_a['name'] . '"><a title="insert in right editor" href="javascript:void(0)" onclick="parent.i2.doteleins(\'' . $row_a['name'] . '\')">&lt;</a>
</td>

<td width="99%" style="word-break:break-all" title="' . date('Ymd Hi', $row_a['lastmut']) . ' ' . $row_a['operator'] . ' ' . ehtml(substr($row_a['desc_1'], 0, 150)) . '"><a href="javascript:void(0)" style="color:black;cursor:text" id="a_' . $row_a['name'] . '">' . $row_a['name'] . '</a>' . $pend . '
</td>



<td>
<a title="rename" href="javascript:void(0)" onclick="f1=window.open(\'rename.php?name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">R</a>
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
<a title="metadata (alt-text, captions a.s.o.)" href="javascript:void(0)" onclick="f1=window.open(\'metav.php?typ=' . $row_a['typ'] . '&name=' . $row_a['name'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">M</a>
</td>

<td>
<a title="upload / replace file" href="javascript:void(0)" onclick="f1=window.open(\'upload.php?name=' . $row_a['name'] . '&typ=' . $row_a['typ'] . '\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');">' . $lspan3 . 'U' . $lspan4 . '</a>
</td></tr>';

        if ($_GET['pvs_p'] == 'on') {
            echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#FFFFCC"><td colspan="9" style="word-break:break-all"><iframe width="100%" height="33" frameborder="0" src="filespreview.php?name=' . $row_a['name'] . '"></iframe>';
include('vgetfile.inc.php');

if(file_exists('../r/v' . $row_a['name'] . '/' . $file_1)) echo '<img style="cursor:pointer" title="poster (click = upload/change/delete)" onclick="f1=window.open(\'upload.php?name=v' . $row_a['name'] . '&typ=i\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');" align="right" src="../thumb.php?w=50&t=' . @filemtime('../r/v' . $row_a['name'] . '/' . $file_1) . '&s=' . $row_a['lastmut'] . '&f=r/v' . $row_a['name'] . '/' . $file_1 . '" />';

else echo '<a style="float:right" title="poster (click = upload/change/delete)" onclick="f1=window.open(\'upload.php?name=v' . $row_a['name'] . '&typ=i\',\'_blank\',\'toolbar=0,location=0,status=1,menubar=0,scrollbars=1,resizable=1,width=800,height=999\');" align="right" href="javascript:void(0)">POSTER</a>';

            echo ehtml(substr(trim($row_a['content_1']), 0, 80));
            if (strlen(trim($row_a['content_1'])) > 80) echo '&hellip;';
            echo '<hr></td></tr>';
        } else echo '<tr id="tr2_' . $row_a['name'] . '" bgcolor="#ffffff"><td colspan="9"></td></tr>';
?>