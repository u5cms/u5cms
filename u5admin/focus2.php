<?php
if (htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES)!='x') {
    echo '<script>
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\').focus()",1);
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\').focus()",777);
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\').style.background=\'gold\'",333);
setTimeout("if(document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\'))document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focus'], ENT_QUOTES) . '\').style.background=\'gold\'",444);
</script>';
}

if (htmlspecialchars($_COOKIE['f9focusBis'], ENT_QUOTES)!='') {
    echo '<script>
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focusBis'], ENT_QUOTES) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focusBis'], ENT_QUOTES) . '\').style.background=\'#CCFF99\'",111);
setTimeout("if(document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focusBis'], ENT_QUOTES) . '\'))document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focusBis'], ENT_QUOTES) . '\').style.background=\'#CCFF99\'",222);
</script>';
}
?>