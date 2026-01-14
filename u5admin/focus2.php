<?php
if (htmlspecialchars($_COOKIE['f9focus'])!='x') {
    echo '<script>
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus']) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus']) . '\').focus()",1);
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus']) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus']) . '\').focus()",777);
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus']) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focus']) . '\').style.background=\'gold\'",333);
setTimeout("if(document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focus']) . '\'))document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focus']) . '\').style.background=\'gold\'",444);
</script>';
}

if (htmlspecialchars($_COOKIE['f9focusBis'])!='') {
    echo '<script>
setTimeout("if(document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focusBis']) . '\'))document.getElementById(\'a_' . htmlspecialchars($_COOKIE['f9focusBis']) . '\').style.background=\'#CCFF99\'",111);
setTimeout("if(document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focusBis']) . '\'))document.getElementById(\'tr1_' . htmlspecialchars($_COOKIE['f9focusBis']) . '\').style.background=\'#CCFF99\'",222);
</script>';
}
?>