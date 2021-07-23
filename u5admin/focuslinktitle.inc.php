<script>
flink='&nbsp;<span style="font-size:40%"><a style="text-decoration:none" title="focus in repository" href="javascript:void(0)" onclick="if(!opener){alert(\'Context window missing!\')} else {opener.parent.i3.location.href=\'focus.php?c=<?php echo $_GET['name']?>\'}">F</a>&nbsp;</span>';
if(location.href.indexOf('upload.php')>0||location.href.indexOf('uploada.php')>0)mlink='&nbsp;<span style="font-size:40%"><a style="text-decoration:none" title="metadata" href="javascript:void(0)" onclick="location.href=location.href.replace(/uploada.php/,\'upload.php\').replace(/upload.php/,\'meta<?php echo $_GET['typ']?>.php\')">M</a>&nbsp;</span>';
else mlink='';
h1=document.getElementsByTagName('h1');
h1=h1[0];
h1.innerHTML=h1.innerHTML.split('</i>')[0]+'</i>'+mlink+''+flink+h1.innerHTML.split('</i>')[1];
</script>
<?php include('selfclose.inc.php')?>