(function() {
  const frames = new WeakSet();
  const documents = new WeakSet();
  const containers = new WeakSet();
  const STYLE_ID = 'u5FirefoxScrollbarFix';

  function doesScrollbarOverlay(doc) {
    const test = doc.createElement('div');
    test.style.cssText = 'width:100px;height:100px;overflow:scroll;position:absolute;top:-9999px';
    doc.body.appendChild(test);
    const overlays = test.clientWidth === test.offsetWidth;
    test.remove();
    return overlays;
  }

  function fixSpecialElements(doc) {
    const container = doc.getElementById('pidvesadivinc');
    if (!container || containers.has(container)) return;
    containers.add(container);
    function fixLinks() {
      container.querySelectorAll('a').forEach(a => {
        const text = a.textContent.trim();
        if ((text === 'a' || text === 'x') && !a.textContent.endsWith('\u00A0\u00A0\u00A0')) {
          a.textContent = text + '\u00A0\u00A0\u00A0';
        }
      });
    }
    fixLinks();
    new MutationObserver(fixLinks).observe(container, { childList: true, subtree: true, characterData: true });
  }

  function watchFrame(frame) {
    if (!frames.has(frame)) {
      frames.add(frame);
      frame.addEventListener('load', () => applyToDocument(frame.contentDocument));
    }
    try { applyToDocument(frame.contentDocument); } catch (error) {}
  }

  function applyToDocument(doc) {
    try {
      if (!doc || !doc.head || !doc.body || documents.has(doc)) return;
      if (doc.readyState === 'loading') return;
      documents.add(doc);
      if (!doc.getElementById(STYLE_ID)) {
        const style = doc.createElement('style');
        style.id = STYLE_ID;
        style.textContent = 'html{scrollbar-gutter:stable}body{padding-right:9px;box-sizing:border-box}*{box-sizing:border-box}';
        doc.head.appendChild(style);
      }
      fixSpecialElements(doc);
      doc.querySelectorAll('iframe').forEach(watchFrame);
      new MutationObserver(records => {
        records.forEach(record => record.addedNodes.forEach(node => {
          if (node.nodeType !== 1) return;
          if (node.tagName === 'IFRAME') watchFrame(node);
          node.querySelectorAll('iframe').forEach(watchFrame);
        }));
      }).observe(doc.body, { childList: true, subtree: true });
    } catch (error) {}
  }

  function runFix() {
    if (navigator.userAgent.toLowerCase().includes('firefox') && doesScrollbarOverlay(document)) {
      applyToDocument(document);
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', runFix);
  else runFix();
})();
