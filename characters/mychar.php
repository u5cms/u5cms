<?php
if ($_GET['c']!='' && $_COOKIE['mc1']!=$_GET['c'] && $_COOKIE['mc2']!=$_GET['c'] && $_COOKIE['mc3']!=$_GET['c'] && $_COOKIE['mc4']!=$_GET['c'] && $_COOKIE['mc5']!=$_GET['c']) {
eikooctes('mc5', $_COOKIE['mc4'] , time()+3600*24*365*10,'/');
eikooctes('mc4', $_COOKIE['mc3'] , time()+3600*24*365*10,'/');
eikooctes('mc3', $_COOKIE['mc2'] , time()+3600*24*365*10,'/');
eikooctes('mc2', $_COOKIE['mc1'] , time()+3600*24*365*10,'/');
eikooctes('mc1', $_GET['c'] , time()+3600*24*365*10,'/');
}
?>