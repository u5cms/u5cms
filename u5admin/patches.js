(function() {
  const PADDING_RIGHT = 9;

  function isFirefox() {
    return navigator.userAgent.toLowerCase().includes('firefox');
  }

  function doesScrollbarOverlay(doc) {
    try {
      const test = doc.createElement('div');
      test.style.cssText = `
        width: 100px;
        height: 100px;
        overflow: scroll;
        position: absolute;
        top: -9999px;
      `;
      doc.body.appendChild(test);
      const inner = doc.createElement('div');
      inner.style.height = '200px';
      test.appendChild(inner);
      const overlays = test.clientWidth === test.offsetWidth;
      doc.body.removeChild(test);
      return overlays;
    } catch (e) {
      return false;
    }
  }

  function fixSpecialElements(doc) {
    function tryInit() {
      const container = doc.getElementById('pidvesadivinc');
      if (!container) {
        setTimeout(tryInit, 100);
        return;
      }

      function fixLinks() {
        const links = container.querySelectorAll('a');
        links.forEach(a => {
          const txt = a.textContent.trim();
          if ((txt === 'a' || txt === 'x') && !a.textContent.endsWith('\u00A0\u00A0\u00A0')) {
            a.textContent = txt + '\u00A0\u00A0\u00A0';
          }
        });
      }

      fixLinks();

      const observer = new MutationObserver(() => fixLinks());
      observer.observe(container, { childList: true, subtree: true });
    }

    tryInit();
  }

  function injectFixCSS(doc) {
    try {
      const style = doc.createElement('style');
      style.textContent = `
        html {
          scrollbar-gutter: stable;
        }
        body {
          padding-right: ${PADDING_RIGHT}px;
          box-sizing: border-box;
        }
        * {
          box-sizing: border-box;
        }
      `;
      doc.head.appendChild(style);
      fixSpecialElements(doc);
    } catch (e) {}
  }

  function applyToAllFrames(win) {
    try {
      const doc = win.document;
      injectFixCSS(doc);

      const iframes = doc.querySelectorAll('iframe');
      iframes.forEach(f => {
        function tryInject() {
          try {
            if (
              f.contentWindow &&
              f.contentDocument &&
              f.contentDocument.readyState === 'complete'
            ) {
              applyToAllFrames(f.contentWindow);
            }
          } catch (e) {}
        }

        tryInject();
        f.addEventListener('load', tryInject);
      });
    } catch (e) {}
  }

  function runFix() {
    if (isFirefox() && doesScrollbarOverlay(document)) {
      applyToAllFrames(window);
      setTimeout(() => applyToAllFrames(window), 1000);
      setTimeout(() => applyToAllFrames(window), 3000);
    }
  }

  if (document.readyState === 'complete') {
    runFix();
  } else {
    window.addEventListener('load', runFix);
  }
})();
