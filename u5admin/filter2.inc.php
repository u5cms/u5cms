<?php
require_once('connect.inc.php');
if ($_COOKIE['shrchv']=='1') $delstatus=2;
else $delstatus=0;
$squot="\'";
?>
<script>
var filterRowGroups=[];

function compactFilterRows(group, filtered) {
if (!group.spacers) {
group.spacers=[];
var children=group.container.children;
for (var r=0;r<children.length;r++) {
var row=children[r];
if (row.tagName!='TR' || row.id || row.textContent.trim()!='') continue;
var empty=true;
for (var c=0;c<row.children.length;c++) {
var cell=row.children[c];
if (cell.tagName!='TD' || cell.id || cell.children.length) empty=false;
}
if (empty) group.spacers.push({row:row,display:row.style.display});
}
}
for (var s=0;s<group.spacers.length;s++) {
var spacer=group.spacers[s], display=filtered ? 'none' : spacer.display;
if (spacer.row.style.display!==display) spacer.row.style.display=display;
}
}

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
compactFilterRows(group,form.pvs_f[1].checked);
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

var ctL='', ctR='', pageL='', pageR='';
var filterTimer=null, filterInputs=null, orphanDirty=true, markerObserver=null;
function highlight(cell, linked) {
if (!cell) return;
var state=linked ? 'on' : 'off';
if (cell.name===state) return;
cell.name=state;
cell.style.background=linked ? 'lightgreen' : '#ffcc66';
}
function green(patterns,tid) {
var left=false, right=false;
for (var p=0;p<patterns.length;p++) {
if (ctL.indexOf(patterns[p])!==-1) left=true;
if (ctR.indexOf(patterns[p])!==-1) right=true;
}
var doc=parent.document, form=doc.form1;
highlight(doc.getElementById('tdL_'+tid),left);
highlight(doc.getElementById('tdR_'+tid),right);
var marker=doc.getElementById('o_'+tid);
var visible=!form.pvs_f[1].checked || left || right ||
(marker && marker.innerHTML.indexOf('_orphan_')!==-1) || tid===pageL || tid===pageR;
var row=doc.getElementById('tr1_'+tid), display=visible ? '' : 'none';
if (row.style.display!==display) row.style.display=display;
var preview=doc.getElementById('tr2_'+tid);
var showPreview=form.pvs_p && form.pvs_p[1] && form.pvs_p[1].checked;
display=visible && showPreview ? '' : 'none';
if (preview && preview.style.display!==display) preview.style.display=display;
}
function selectedContent(form) {
for (var n=0;n<5;n++) {
if (form.view && form.view[n] && form.view[n].checked) {
var field=form.elements.namedItem('content_'+(n+1));
return field ? field.value : '';
}
}
return '';
}
function filter(force) {
if (filterTimer!==null) clearTimeout(filterTimer);
filterTimer=null;
try {
var left=parent.parent.i1.document.form1, right=parent.parent.i2.document.form1;
var form=parent.document.form1;
if (!left || !right || !form || !form.pvs_f || !form.pvs_f[1]) return;
var inputs=[selectedContent(left),selectedContent(right),left.page.value,right.page.value,
form.pvs_f[1].checked,!!(form.pvs_p && form.pvs_p[1] && form.pvs_p[1].checked)];
var changed=!!force || orphanDirty || !filterInputs;
for (var n=0;!changed && n<inputs.length;n++) if (inputs[n]!==filterInputs[n]) changed=true;
var controller=parent.u5FScroll;
if (!changed && !(controller && controller.hold())) return;
var scrollState=controller ? controller.before() : null;
if (changed) {
ctL=inputs[0].replace(/\]{2,4}|\[{2,4}/g,' ');
ctR=inputs[1].replace(/\]{2,4}|\[{2,4}/g,' ');
pageL=inputs[2]; pageR=inputs[3];
var rows=parent.document.querySelectorAll('tr[id^="tr1_"]');
if (!markerObserver && window.MutationObserver) {
markerObserver=new MutationObserver(function(){orphanDirty=true;});
for (var n=0;n<rows.length;n++) {
var marker=parent.document.getElementById('o_'+rows[n].id.substring(4));
if (marker) markerObserver.observe(marker,{childList:true,subtree:true,characterData:true});
}
}
for (var n=0;n<rows.length;n++) {
var id=rows[n].id.substring(4);
green(['['+id+']',':'+id+']','['+id+'?',':'+id+'?','[lo:]'+id+'[:lo]',
'[go:]'+id+'[:go]','/'+id+'/'+id+'_','c='+id+'"',"c="+id+"'",'c='+id+'\\','c='+id+'&'],id);
}
orderFilterRows();
filterInputs=inputs; orphanDirty=false;
if (markerObserver) markerObserver.takeRecords();
}
if (controller) controller.after(scrollState);
} catch(error) {
// An editor may be navigating; retain the last valid list until it is ready.
} finally {
filterTimer=setTimeout(filter,333);
}
}
filter();
</script>
<?php include('orphan.inc.php') ?>
