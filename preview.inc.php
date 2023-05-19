<?php 
if ($_COOKIE['i1_l']!='P') $lansel=$_COOKIE['i1_l'];
else $lansel=$_COOKIE['i2_l'];
?>
<form style="display:none" name="formp" action="index.php?c=<?php echo $_GET['c']?>&l=<?php echo $lansel?>&p=<?php echo $_GET['p']?>" method="post">
<textarea name="r"></textarea>
</form>
<script type="text/javascript">
u5prvldd=1;

function pviewit() {
if (parent.window.name=='i2') {
   
   if (parent.parent.i1.document.form1.view[0].checked==true) {
   document.formp.r.value=parent.parent.i1.document.form1.content_1.value;
   }

   if (parent.parent.i1.document.form1.view[1].checked==true) {
   document.formp.r.value=parent.parent.i1.document.form1.content_2.value;
   }

   if (parent.parent.i1.document.form1.view[2].checked==true) {
   document.formp.r.value=parent.parent.i1.document.form1.content_3.value;
   }

    if (parent.parent.i1.document.form1.view[3].checked==true) {
        document.formp.r.value=parent.parent.i1.document.form1.content_4.value;
    }

    if (parent.parent.i1.document.form1.view[4].checked==true) {
        document.formp.r.value=parent.parent.i1.document.form1.content_5.value;
    }
}

if (parent.window.name=='i1') {
   
   if (parent.parent.i2.document.form1.view[0].checked==true) {
   document.formp.r.value=parent.parent.i2.document.form1.content_1.value;
   }

   if (parent.parent.i2.document.form1.view[1].checked==true) {
   document.formp.r.value=parent.parent.i2.document.form1.content_2.value;
   }

   if (parent.parent.i2.document.form1.view[2].checked==true) {
   document.formp.r.value=parent.parent.i2.document.form1.content_3.value;
   }

    if (parent.parent.i2.document.form1.view[3].checked==true) {
        document.formp.r.value=parent.parent.i2.document.form1.content_4.value;
    }

    if (parent.parent.i2.document.form1.view[4].checked==true) {
        document.formp.r.value=parent.parent.i2.document.form1.content_5.value;
    }

}


document.formp.submit();
}
<?php 
if ($_GET['i']==1) echo 'setTimeout("pviewit()",777);';
?>
if (parent.pframe2 && parent.pframe2.document.getElementById('body') && parent.pframe2.document.getElementById('body').innerHTML!=document.getElementById('body').innerHTML) parent.pframe2.document.getElementById('body').innerHTML=document.getElementById('body').innerHTML;
</script>