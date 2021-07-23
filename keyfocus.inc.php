<?php if($errors==0)echo'
<script>
parent.document.u5form.'. $key .'.focus();
parent.document.u5form.'. $key .'.scrollIntoView();
parent.window.scrollBy(0,-100);
</script>'; ?>
