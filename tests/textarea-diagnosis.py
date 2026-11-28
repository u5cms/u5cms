#!/usr/bin/env python3
"""Instrument an isolated exact-source textarea test without relaxing assertions."""
import os,pathlib,subprocess,tarfile,tempfile
root=pathlib.Path(__file__).resolve().parent.parent
sha=os.environ['U5_DIAG_SOURCE']; variant=os.environ['U5_DIAG_VARIANT']
with tempfile.TemporaryDirectory(prefix='u5-undo-diagnostic-') as temporary:
    work=pathlib.Path(temporary); archive=work/'source.tar'; site=work/'source';site.mkdir()
    subprocess.run(['git','archive',sha,'--output='+str(archive)],cwd=root,check=True)
    with tarfile.open(archive) as contents:contents.extractall(site,filter='data')
    print('EXACT_DIAGNOSTIC_SOURCE',sha,'FIXTURE_VARIANT',variant,flush=True)
    target=site/'u5admin/textarea-actions.js'; text=target.read_text()
    anchor='var histories = new WeakMap(), active = null, menu = null, serial = 0, pending = null;'
    assert text.count(anchor)==1
    text=text.replace(anchor,anchor+'''\n            win.u5TextareaAudit=[];
            function trace(event,ta,extra){var h=state(ta);win.u5TextareaAudit.push({event:event,id:ta.id,at:Date.now(),value:ta.value,current:h.current.value,past:h.past.map(function(s){return s.value;}),future:h.future.map(function(s){return s.value;}),kind:h.kind,extra:extra});if(win.u5TextareaAudit.length>120)win.u5TextareaAudit.shift();}
''')
    for anchor,addition in [
      ('function record(ta, before, kind) {',"trace('record',ta,{before:before.value,kind:kind});"),
      ('function undo(ta, redo) {',"trace('undo',ta,{redo:redo});"),
      ('function receive(id, value) {',"var diagnosticArea=doc.getElementById(id);if(diagnosticArea&&areas.indexOf(diagnosticArea)>=0)trace('receive',diagnosticArea,{incoming:value});")]:
        assert text.count(anchor)==1;text=text.replace(anchor,anchor+'\n                '+addition)
    target.write_text(text)
    target=site/'tests/textarea-browser.cjs'; text=target.read_text()
    before="await left.evaluate(()=>{ta1.focus();ta1.dispatchEvent(new CompositionEvent('compositionstart'"
    start=text.index(before);end=text.index("   await ta.fill(initial);",start)
    sequence=text[start:end]
    assert sequence.count("ordinary typing undo")==1
    sequence=sequence.replace("await ta.press('End');await ta.press('x');", "await ta.press('End');await ta.press('x');if(delay)await page.waitForTimeout(delay);")
    sequence=sequence.replace("focused:await ta.evaluate(el=>el===el.ownerDocument.activeElement),errors", "focused:await ta.evaluate(el=>el===el.ownerDocument.activeElement),traceLeft:await left.evaluate(()=>window.u5TextareaAudit),traceRight:await right.evaluate(()=>window.u5TextareaAudit),errors")
    repeated="for(const delay of [0,10,100,950]){for(let repetition=0;repetition<2;repetition++){console.log('UNDO_PROFILE',JSON.stringify({mode,delay,repetition}));await ta.fill(initial);"+sequence+"console.log('UNDO_PROFILE_PASS',JSON.stringify({mode,delay,repetition}));}}\n"
    text=text[:start]+repeated+text[end:];target.write_text(text)
    if variant=='nonrecursive':
        target=site/'tests/textarea-web-fixture.php';raw=target.read_bytes()
        needle=b"        $columns[]='content_'.$l;$values[]="
        assert raw.count(needle)==1
        start=raw.index(needle);end=raw.index(b'\n',start)
        raw=raw[:end]+b"\n        if($name==='textareatarget')$values[count($values)-1]=\"'Non-recursive target fixture'\";"+raw[end:]
        target.write_bytes(raw)
    target=site/'tests/codeassets-web-regression.py';text=target.read_text()
    anchor='            server.terminate();server.wait(timeout=10)';assert text.count(anchor)==1
    text=text.replace(anchor,anchor+"\n            log.flush();diagnostic_log=(work/'server.log').read_text();print('RENDER_MEMORY_FATAL_COUNT',diagnostic_log.count('Allowed memory size'));print('RENDER_MEMORY_FATAL_LINES',repr([line for line in diagnostic_log.splitlines() if 'Allowed memory size' in line]))")
    target.write_text(text)
    env=os.environ.copy();env.update(U5CMS_TEXTAREA_AUDIT='1',U5CMS_TEXTAREA_AUDIT_ONLY='1',U5CMS_BACKUP_STRESS='1')
    subprocess.run(['python3','tests/codeassets-web-regression.py'],cwd=site,env=env,check=True)
