FilterBool: 
<?php 
if ($_COOKIE['fdbool']=='and') echo 'and<font color=green>&#10003;</font>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdbool&v=or">or</a>';
                          else echo '<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdbool&v=and">and</a>/or<font color=green>&#10003;</font>';
?>

&nbsp;

<span title="
no=ORDER BY Notes 
au=ORDER BY Authuser
ff=ORDER BY First Field
se=ORDER BY Sent
">Order:
<?php 
     if ($_COOKIE['fdorder']=='no') echo ' no<font color=green>&#10003;</font>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=au">au</a>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=ff">ff</a>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=se">se</a>';

else if ($_COOKIE['fdorder']=='au') echo ' <a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=no">no</a>/au<font color=green>&#10003;</font>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=ff">ff</a>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=se">se</a>';

else if ($_COOKIE['fdorder']=='ff') echo ' <a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=no">no</a>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=au">au</a>/ff<font color=green>&#10003;</font>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=se">se</a>';

else                                echo ' <a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=no">no</a>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=au">au</a>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdorder&v=ff">ff</a>/se<font color=green>&#10003;</font>';
?>
</span>

&nbsp;

Truncate:
<?php
if ($_COOKIE['fdtrunc']=='off') echo 'off<font color=green>&#10003;</font>/<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdtrunc&v=on">on</a>';
                           else echo '<a target="fdc" style="font-size:80%" href="formdataset.php?f=fdtrunc&v=off">off</a>/on<font color=green>&#10003;</font>';
?>



<iframe name="fdc" frameborder="0" width="0" height="0"></iframe>




