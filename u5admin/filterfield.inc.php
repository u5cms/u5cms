<script>
function dblftr() {
if(document.getElementById('idgdf').value=='**') {
if (document.cookie.indexOf('dblftr=')>-1){
document.getElementById('idgdf').value=('; '+document.cookie).split('; dblftr=')[1].split(';')[0];
}
}
else {
document.cookie='dblftr='+escape(document.getElementById('idgdf').value)+'; expires=Thu, 31 Dec 2037 12:00:00 GMT';
document.getElementById('idgdf').value='';
}
}
</script>
<input
id="idgdf" 
type="text" 
name="f" 
style="font-size:90%;width:35px;float:right" 
onmouseover="document.getElementById('ifrautomatch').src='fautomatch.ifr.php';if(this.value.length>6)this.style.width=this.value.length*6+'px';else this.style.width='35px';if(this.value.replace(/ /g,'')!=this.value)this.value=this.value.replace(/ /g,'');if(this.value.replace(/\[/g,'')!=this.value)this.value=this.value.replace(/\[/g,'');if(this.value.replace(/\]/g,'')!=this.value)this.value=this.value.replace(/\]/g,'');if(this.value.replace(/:/g,'')!=this.value)this.value=this.value.replace(/:/g,'');if(this.value.replace(/\$/g,'')!=this.value)this.value=this.value.replace(/\$/g,'')"
onmouseout="document.getElementById('ifrautomatch').src='fautomatch.ifr.php';if(this.value.length>6)this.style.width=this.value.length*6+'px';else this.style.width='35px';if(this.value.replace(/ /g,'')!=this.value)this.value=this.value.replace(/ /g,'');if(this.value.replace(/\[/g,'')!=this.value)this.value=this.value.replace(/\[/g,'');if(this.value.replace(/\]/g,'')!=this.value)this.value=this.value.replace(/\]/g,'');if(this.value.replace(/:/g,'')!=this.value)this.value=this.value.replace(/:/g,'');if(this.value.replace(/\$/g,'')!=this.value)this.value=this.value.replace(/\$/g,'')"
onfocus="document.getElementById('ifrautomatch').src='fautomatch.ifr.php';if(this.value.length>6)this.style.width=this.value.length*6+'px';else this.style.width='35px';if(this.value.replace(/ /g,'')!=this.value)this.value=this.value.replace(/ /g,'');if(this.value.replace(/\[/g,'')!=this.value)this.value=this.value.replace(/\[/g,'');if(this.value.replace(/\]/g,'')!=this.value)this.value=this.value.replace(/\]/g,'');if(this.value.replace(/:/g,'')!=this.value)this.value=this.value.replace(/:/g,'');if(this.value.replace(/\$/g,'')!=this.value)this.value=this.value.replace(/\$/g,'')"
onblur="document.getElementById('ifrautomatch').src='fautomatch.ifr.php';if(this.value.length>6)this.style.width=this.value.length*6+'px';else this.style.width='35px';if(this.value.replace(/ /g,'')!=this.value)this.value=this.value.replace(/ /g,'');if(this.value.replace(/\[/g,'')!=this.value)this.value=this.value.replace(/\[/g,'');if(this.value.replace(/\]/g,'')!=this.value)this.value=this.value.replace(/\]/g,'');if(this.value.replace(/:/g,'')!=this.value)this.value=this.value.replace(/:/g,'');if(this.value.replace(/\$/g,'')!=this.value)this.value=this.value.replace(/\$/g,'')"
onkeyup="document.getElementById('ifrautomatch').src='fautomatch.ifr.php';if(this.value.length>6)this.style.width=this.value.length*6+'px';else this.style.width='35px';if(this.value.replace(/ /g,'')!=this.value)this.value=this.value.replace(/ /g,'');if(this.value.replace(/\[/g,'')!=this.value)this.value=this.value.replace(/\[/g,'');if(this.value.replace(/\]/g,'')!=this.value)this.value=this.value.replace(/\]/g,'');if(this.value.replace(/:/g,'')!=this.value)this.value=this.value.replace(/:/g,'');if(this.value.replace(/\$/g,'')!=this.value)this.value=this.value.replace(/\$/g,'')"
ondblclick="dblftr();location.href=location.href.split('?')[0]+'?f='+this.value" 
title="Filter; *=Wildcard; Enter/Tab=Evoke; Dblclick=Reset" 
onchange=";if(this.value.replace(/ /g,'')!=this.value)this.value=this.value.replace(/ /g,'');if(this.value.replace(/\[/g,'')!=this.value)this.value=this.value.replace(/\[/g,'');if(this.value.replace(/\]/g,'')!=this.value)this.value=this.value.replace(/\]/g,'');if(this.value.replace(/:/g,'')!=this.value)this.value=this.value.replace(/:/g,'');if(this.value.replace(/\$/g,'')!=this.value)this.value=this.value.replace(/\$/g,'');setfilter(this.value)" 
value="<?php echo htmlXspecialchars($_GET['f'])?>">
<span id="fautomatch" style="float:right"></span><iframe id="ifrautomatch" style="display:none" src="fautomatch.ifr.php"></iframe>
<script>
if(location.href.indexOf('a.php')>0) document.getElementById('idgdf').style.display='none'
function setfilter(that) {
keepthat=that;	
setTimeout("location.href=location.href.split('?')[0]+'?f='+keepthat",333);	
}
if(document.getElementById('idgdf').value.length>6)document.getElementById('idgdf').style.width=document.getElementById('idgdf').value.length*6+'px';else document.getElementById('idgdf').style.width='35px'
</script>