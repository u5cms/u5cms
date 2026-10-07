<?php
require_once('connect.inc.php');
if ($_COOKIE['shrchv']=='1') $delstatus=2;
else $delstatus=0;
$squot="\'";
?>
<script>
var filterRowGroups=[];

function orderFilterRows() {
var doc=parent.document;
var form=doc.form1;
if (!form || !form.pvs_f || !form.pvs_f[1]) return;

if (filterRowGroups.length==0) {
var rows=doc.querySelectorAll('tr[id^="tr1_"]');
for (var r=0;r<rows.length;r++) {
var container=rows[r].parentNode;
var group=null;
for (var g=0;g<filterRowGroups.length;g++) {
if (filterRowGroups[g].container===container) group=filterRowGroups[g];
}
if (!group) {
group={container:container,rows:[]};
filterRowGroups.push(group);
}
group.rows.push(rows[r]);
}
}

for (var g=0;g<filterRowGroups.length;g++) {
var group=filterRowGroups[g];
var regular=[];
var orphans=[];
for (var r=0;r<group.rows.length;r++) {
var row=group.rows[r];
var marker=doc.getElementById('o_'+row.id.substring(4));
if (form.pvs_f[1].checked && marker && marker.innerHTML.indexOf('_orphan_')!==-1) orphans.push(row);
else regular.push(row);
}
var ordered=regular.concat(orphans);
var current=group.container.querySelectorAll('tr[id^="tr1_"]');
var changed=false;
for (var r=0;r<ordered.length;r++) {
if (current[r]!==ordered[r]) changed=true;
}
if (!changed) continue;

// Move existing row pairs so previews, highlights and handlers stay intact.
var fragment=doc.createDocumentFragment();
for (var r=0;r<ordered.length;r++) {
var row=ordered[r];
var preview=doc.getElementById('tr2_'+row.id.substring(4));
fragment.appendChild(row);
if (preview && preview.parentNode===group.container) fragment.appendChild(preview);
}
group.container.appendChild(fragment);
}
}

function green(s1,s2,s3,s4,s5,s6,s7,s8,s9,s10,s11,tid) {
if (ctL.indexOf(s1)>-1 || ctL.indexOf(s2)>-1 || ctL.indexOf(s3)>-1 || ctL.indexOf(s4)>-1 || ctL.indexOf(s5)>-1 || ctL.indexOf(s6)>-1 || ctL.indexOf(s7)>-1 || ctL.indexOf(s8)>-1 || ctL.indexOf(s9)>-1 || ctL.indexOf(s10)>-1) {
parent.document.getElementById('tdL_'+tid).style.background='lightgreen';
parent.document.getElementById('tdL_'+tid).name='on';
}
else {
parent.document.getElementById('tdL_'+tid).style.background='#ffcc66';
parent.document.getElementById('tdL_'+tid).name='off';
}

if (ctR.indexOf(s1)>-1 || ctR.indexOf(s2)>-1 || ctR.indexOf(s3)>-1 || ctR.indexOf(s4)>-1 || ctR.indexOf(s5)>-1 || ctR.indexOf(s6)>-1 || ctR.indexOf(s7)>-1 || ctR.indexOf(s8)>-1 || ctR.indexOf(s9)>-1 || ctR.indexOf(s10)>-1) {
parent.document.getElementById('tdR_'+tid).style.background='lightgreen';
parent.document.getElementById('tdR_'+tid).name='on';
}
else {
parent.document.getElementById('tdR_'+tid).style.background='#ffcc66';
parent.document.getElementById('tdR_'+tid).name='off';
}

if (parent.document.form1.pvs_f[1].checked==true) {
if(parent.document.getElementById('tdL_'+tid).name=='on' || parent.document.getElementById('tdR_'+tid).name=='on' || parent.document.getElementById('o_'+id).innerHTML.indexOf('_orphan_')>0) parent.document.getElementById('tr1_'+tid).style.display='block';
else parent.document.getElementById('tr1_'+tid).style.display='none';

if(parent.document.getElementById('tdL_'+tid).name=='on' || parent.document.getElementById('tdR_'+tid).name=='on' || parent.document.getElementById('o_'+id).innerHTML.indexOf('_orphan_')>0)parent.document.getElementById('tr2_'+tid).style.display='block';
else parent.document.getElementById('tr2_'+tid).style.display='none';


if(parent.document.getElementById('tr1_'+parent.parent.i1.form1.page.value))parent.document.getElementById('tr1_'+parent.parent.i1.form1.page.value).style.display='block';
if(parent.document.getElementById('tr2_'+parent.parent.i1.form1.page.value))parent.document.getElementById('tr2_'+parent.parent.i1.form1.page.value).style.display='block';

if(parent.document.getElementById('tr1_'+parent.parent.i2.form1.page.value))parent.document.getElementById('tr1_'+parent.parent.i2.form1.page.value).style.display='block';
if(parent.document.getElementById('tr2_'+parent.parent.i2.form1.page.value))parent.document.getElementById('tr2_'+parent.parent.i2.form1.page.value).style.display='block';

}
}

function filter() {
if (parent.parent.i1 && parent.parent.i2 && parent.parent.i1.document.form1 && parent.parent.i2.document.form1) {

ctL='';
ctR='';
i1=parent.parent.i1;
i2=parent.parent.i2;

if (i1.document.form1.view[0].checked==true) if (i1.document.form1.content_1) ctL=i1.document.form1.content_1.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i1.document.form1.view[1].checked==true) if (i1.document.form1.content_2) ctL=i1.document.form1.content_2.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i1.document.form1.view[2].checked==true) if (i1.document.form1.content_3) ctL=i1.document.form1.content_3.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i1.document.form1.view[3].checked==true) if (i1.document.form1.content_4) ctL=i1.document.form1.content_4.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i1.document.form1.view[4].checked==true) if (i1.document.form1.content_5) ctL=i1.document.form1.content_5.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");

if (i2.document.form1.view[0].checked==true) if (i2.document.form1.content_1) ctR=i2.document.form1.content_1.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i2.document.form1.view[1].checked==true) if (i2.document.form1.content_2) ctR=i2.document.form1.content_2.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i2.document.form1.view[2].checked==true) if (i2.document.form1.content_3) ctR=i2.document.form1.content_3.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i2.document.form1.view[3].checked==true) if (i2.document.form1.content_4) ctR=i2.document.form1.content_4.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");
if (i2.document.form1.view[4].checked==true) if (i2.document.form1.content_5) ctR=i2.document.form1.content_5.value.replace(/\]\]\]\]/g, " ").replace(/\]\]\]/g, " ").replace(/\]\]/g, " ").replace(/\[\[\[\[/g, " ").replace(/\[\[\[/g, " ").replace(/\[\[/g, " ");

tr=parent.document.getElementsByTagName('tr');

for(i=0;i<tr.length;i++) {
id=tr[i].id
if(id.indexOf('tr1_')===0) {
id=id.substring(4);

green('['+id+']' , ':'+id+']' , '['+id+'?' , ':'+id+'?' , '[lo:]'+id+'[:lo]' , '[go:]'+id+'[:go]' , '/'+id+'/'+id+'_' , 'c='+id+'"' , 'c='+id+'\'' , 'c='+id+'\\' , 'c='+id+'&',id);
}
}
orderFilterRows();
} // if parents there
setTimeout("filter()",333);
} // function filter
filter();
</script>
<?php include('orphan.inc.php') ?>