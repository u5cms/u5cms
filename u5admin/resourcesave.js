(function() {
'use strict';
var busy=false, queued=false, transport=null, serial=0, snapshot=null;
var session=Date.now().toString(36)+'_'+Math.random().toString(36).slice(2);
function data() {
var form=document.form1, names=['name','logins'];
for(var n=1;n<=5;n++) ['content','title','desc','key'].forEach(function(field){names.push(field+'_'+n);});
var result=names.map(function(name){var field=form.elements.namedItem(name);return field ? field.value : '';});
// Album captions are edited in the outer frame and copied only on submission.
if(window.name==='ametai') result.push(Array.prototype.map.call(parent.document.querySelectorAll('input,textarea,select'),function(field){return [field.id,field.value];}));
return JSON.stringify(result);
}
function dirty() {
if(document.getElementById('changes')) document.getElementById('changes').value=1;
if(typeof window.cchanges==='number') window.cchanges=Math.max(cchanges,1);
var button=document.getElementById('savebutton');
if(button) button.style.background='pink';
if(document.getElementById('bodyid')) document.getElementById('bodyid').style.background='#592100';
document.querySelectorAll('.asterisk').forEach(function(star){star.style.display='inline';star.classList.remove('blink_me');});
}
function syncMonitor() {
if(typeof window.getcheckstring==='function') { getcheckstring();window.checkstringold=window.checkstring; }
}
function init() {
var form=document.form1;
if(!form || transport) return;
var token=document.createElement('input');token.type='hidden';token.name='u5resourceid';form.appendChild(token);
transport=document.querySelector('iframe[name="saver"]') || document.createElement('iframe');
// Keep an existing browsing context's name; changing the DOM attribute alone
// does not reliably change the target used by form submission.
if(!transport.name) transport.name='u5resource_transport';
transport.style.display='none';
transport.addEventListener('load',function() {
if(!busy) return;
try { if(transport.contentWindow.location.href==='about:blank') return; } catch(error) {}
var acknowledged=false;try { acknowledged=!!transport.contentWindow.u5ResourceAcknowledged; } catch(error) {}
if(!acknowledged) {
dirty();
var status=document.getElementById('u5resource_status');
if(!status) { status=document.createElement('div');status.id='u5resource_status';status.style.cssText='position:fixed;top:0;left:0;z-index:10000;color:white;background:darkred;padding:4px';document.body.appendChild(status); }
var message='Save failed. Your editor contents have been kept.';
try { var body=transport.contentDocument.body.cloneNode(true);body.querySelectorAll('script,style').forEach(function(node){node.remove();});message+=' '+body.textContent.trim().slice(0,500); } catch(error) {}
status.textContent=message;
}
busy=false;
if(queued) { queued=false;submit(); }
});
if(!transport.parentNode) document.body.appendChild(transport);
form.target=transport.name;
syncMonitor();
}
function begin(event) {
if(document.readyState==='loading') return false;
init();
if(busy) { queued=true;return false; }
var form=document.form1;
if(!form || !transport) return false;
var status=document.getElementById('u5resource_status');if(status)status.remove();
serial++;
form.u5resourceid.value=session+'_'+serial;
snapshot={id:form.u5resourceid.value,name:form.elements.namedItem('name').value,data:data()};
busy=true;
// Retain unload protection throughout the request.
if(typeof window.cchanges==='number') window.cchanges=Math.max(cchanges,1);
if(document.getElementById('changes')) document.getElementById('changes').value=1;
var button=document.getElementById('savebutton');if(button) button.style.background='lightyellow';
document.querySelectorAll('.asterisk').forEach(function(star){star.classList.add('blink_me');});
syncMonitor();
return true;
}
function submit() {
var form=document.form1;
if(!form || document.readyState==='loading') return;
if(typeof form.requestSubmit==='function') form.requestSubmit();
else if((!form.checkValidity || form.checkValidity()) && begin()) HTMLFormElement.prototype.submit.call(form);
}
function complete(id,name,stamp,success) {
if(!snapshot || snapshot.id!==id || snapshot.name!==name || document.form1.elements.namedItem('name').value!==name) return false;
var clean=success && snapshot.data===data();
if(success) document.form1.coco.value=stamp;
if(clean) {
if(document.getElementById('changes')) document.getElementById('changes').value=0;
if(typeof window.cchangesreset==='function') cchangesreset();
var button=document.getElementById('savebutton');if(button) button.style.background='lightgreen';
if(document.getElementById('bodyid')) document.getElementById('bodyid').style.background='#335C33';
syncMonitor();
} else dirty();
return clean;
}
window.u5ResourceSave={begin:begin,submit:submit,complete:complete};
if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init);
else init();
})();
