<?php 
if ($_COOKIE['hp']!=1) eikooctes('hp', 1, time()+3600*24*365*10,'/'); 
else eikooctes('hp', 0, time()+3600*24*365*10,'/');

?>

<script>

parent.i1.pframe2.location.href=parent.i1.pframe2.location.href;

parent.i2.pframe2.location.href=parent.i2.pframe2.location.href;


setTimeout("if (parent.i1.pframe.pviewit) parent.i1.pframe.pviewit()",111);
setTimeout("if (parent.i1.pframe.pviewit) parent.i1.pframe.pviewit()",333);
setTimeout("if (parent.i1.pframe.pviewit) parent.i1.pframe.pviewit()",777);
setTimeout("if (parent.i1.pframe.pviewit) parent.i1.pframe.pviewit()",1111);
setTimeout("if (parent.i1.pframe.pviewit) parent.i1.pframe.pviewit()",2222);
setTimeout("if (parent.i1.pframe.pviewit) parent.i1.pframe.pviewit()",3333);

setTimeout("if (parent.i2.pframe.pviewit) parent.i2.pframe.pviewit()",111);
setTimeout("if (parent.i2.pframe.pviewit) parent.i2.pframe.pviewit()",333);
setTimeout("if (parent.i2.pframe.pviewit) parent.i2.pframe.pviewit()",777);
setTimeout("if (parent.i2.pframe.pviewit) parent.i2.pframe.pviewit()",1111);
setTimeout("if (parent.i2.pframe.pviewit) parent.i2.pframe.pviewit()",2222);
setTimeout("if (parent.i2.pframe.pviewit) parent.i2.pframe.pviewit()",3333);





</script>