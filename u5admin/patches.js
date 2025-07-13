(function() {
  function isFirefox() {
    return navigator.userAgent.toLowerCase().includes('firefox');
  }

  function injectFixCSS(doc) {
    try {
      const style = doc.createElement('style');
      style.textContent = `
        html {
          scrollbar-gutter: stable;
        }
        body {
          padding-right: 9px;
          box-sizing: border-box;
        }
        * {
          box-sizing: border-box;
        }
      `;
      doc.head.appendChild(style);

      const el = doc.getElementById('pidvesadivinc');
      if (el) {
        el.style.setProperty('width', 'calc(100% - 9px)', 'important');
      }
    } catch (e) {}
  }

  function applyToAllFrames(win) {
    try {
      const doc = win.document;
      injectFixCSS(doc);
      const iframes = doc.querySelectorAll('iframe');
      iframes.forEach(f => {
        try {
          if (f.contentWindow) applyToAllFrames(f.contentWindow);
        } catch (e) {}
      });
    } catch (e) {}
  }

  function runFix() {
    if (isFirefox()) {
      applyToAllFrames(window);
      setTimeout(() => applyToAllFrames(window), 1000);
      setTimeout(() => applyToAllFrames(window), 3000);
    }
  }

  if (document.readyState === "complete") {
    runFix();
  } else {
    window.addEventListener("load", runFix);
  }
})();
