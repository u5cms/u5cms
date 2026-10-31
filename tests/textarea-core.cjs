const assert=require('node:assert/strict');
const core=require('../u5admin/textarea-actions.js');
let checks=0;
const equal=(a,b,label)=>{assert.deepEqual(a,b,label);checks++;};
const reserved=['[b]','[i]','[u]','[/]','[h:]','[:h]'];
const cases=[
 ['[page]', ['page']], ['[$$$:page]', ['page']], ['[human text:page]', ['page']],
 ['[:human text:page]', ['page']], ['[Time: 12:30:page]', ['page']], ['[humentext:page]', ['page']],
 ['[b]Text[/] [!intranet]', ['!intranet']], ['[a!b!js?l=fr&x=2]', ['a!b!js']],
 ['[h:]<script>const values=[page]</script>[:h] [real]', ['real']],
 ['[h:][page]', []], ['[[page]] [[[page]]] [[[[page]]]] [real]', ['real']],
 ['{{{page}}} [real]', ['real']], ['<a href="[page]">[real]</a>', ['real']],
 ['<!-- [page] --> [real]', ['real']], ['<script>const x=[page]</script> [real]', ['real']],
 ['[outer [inner] text] [real]', ['real']], ['[unclosed [page]', []],
 ['[/] [h:] [page]', []], ['[_language_] [123] [other_name]', ['123','other_name']],
 ['[label:../page] [label:bad-name] [label:valid-name!js]', ['valid-name!js']],
 ['[label:<script>] [javascript:alert(1)] [label:page]', ['page']],
 ['[text 🌷 & < >:page] [other]', ['page','other']],
 ['[bad\npage] [page]', ['page']], ['[page?x=1&y=two#three]', ['page']]
];
for(const [text,names] of cases)equal(core.references(text,reserved).map(x=>x.name),names,text);
for(const text of ['[page]','[$$$:page]','[human text:page]','[:human text:page]','[12:30:page?l=de&x=1]']){
 const ref=core.references(text,reserved)[0];
 for(let i=ref.start;i<ref.end;i++)equal(core.at(text,i,i,reserved).raw,text,'every source cursor offset '+text+' '+i);
 equal(core.at(text,text.length,text.length,reserved),null,'closing boundary is outside token');
 equal(core.replaceTarget(ref,'replacement'),text.replace('page','replacement'),'replace only the target');
 equal(core.replaceTarget(ref,'page'),text,'identity target replacement');
 if(!ref.inclusion)equal(core.replaceLabel(ref,'new: text'),(ref.button?'[:':'[')+'new: text:page'+ref.query+']','label and target remain separate');
}
const plan=core.format('abc xyz def',4,7,'[b]','[/]',reserved);
equal('abc xyz def'.slice(0,plan.start)+plan.text+'abc xyz def'.slice(plan.end),'abc [b]xyz[/] def','format selected text');
equal(plan.select,[3,6],'retain selected text within markup');
equal(core.format('[b]xyz[/]',3,6,'[b]','[/]',reserved),{start:0,end:9,text:'xyz',select:[0,3]},'toggle an adjacent format');
equal(core.format('',0,0,'[b]','[/]',reserved).select,[3,3],'insert empty format with cursor inside');
for(const [text,start,end] of [['[h:]x[:h]',5,6],['[label:page]',2,4],['[[heading]]',2,5]]){
 assert.throws(()=>core.format(text,start,end,'[b]','[/]',reserved));checks++;
}
for(const bad of ['','../x','x/y','x.y','x:y','x\n','bad-name','x'.repeat(256),null]){
 equal(core.validName(bad),false,'unsafe target '+bad);assert.throws(()=>core.replaceTarget(core.references('[page]',reserved)[0],bad));checks++;
}
assert.throws(()=>core.replaceLabel(core.references('[$$$:page]',reserved)[0],'label'));checks++;
for(const label of ['[bad]','bad\nlabel','$$$']){assert.throws(()=>core.replaceLabel(core.references('[page]',reserved)[0],label));checks++;}
for(let i=0;i<200;i++){
 const name='object_'+i, prefix='é 🌷 '.repeat(i%8), query='?a='+i+'&l=fr';
 const text=prefix+'[:label: '+i+':'+name+query+'] suffix';
 const refs=core.references(text,reserved);equal(refs.length,1,'one exact reference');
 const ref=refs[0];equal(text.slice(ref.start,ref.end),ref.raw,'UTF-16 source range');
 equal(text.slice(0,ref.start)+core.replaceTarget(ref,'new_'+i)+text.slice(ref.end),text.replace(name,'new_'+i),'lossless replacement with surrounding Unicode');
}
console.log('Textarea parser/transform checks: '+checks+' passed.');
