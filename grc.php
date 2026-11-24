<div class="u5-turnstile" data-sitekey="<?php echo htmlspecialchars($u5turnstilesitekey ?? '', ENT_QUOTES, 'WINDOWS-1252') ?>"></div>
<script>
(function (widget) {
    var form = widget.closest('form');
    if (!form) return;
    var widgets = window.u5turnstileWidgets;
    if (!widgets) {
        widgets = window.u5turnstileWidgets = new Map();
        window.u5turnstileReset = function (key) {
            // A cached single-form response may omit the widget number.
            if (!key && widgets.size === 1) key = widgets.keys().next().value;
            var reset = widgets.get(key);
            if (reset) reset();
        };
    }
    var key = (window.u5turnstileLastId || 0) + 1;
    window.u5turnstileLastId = key;
    var widgetId = null;
    var verified = false;
    widget.id = key === 1 ? 'u5turnstile' : 'u5turnstile-' + key;
    var field = document.createElement('input');
    field.type = 'hidden';
    field.name = 'u5-turnstile-widget';
    field.value = key;
    widget.parentNode.insertBefore(field, widget);

    function updateButtons() {
        var buttons = form.querySelectorAll('input[type="submit"],input[type="image"],button[type="submit"],button:not([type])');
        for (var i = 0; i < buttons.length; ++i) {
            buttons[i].style.visibility = verified ? 'visible' : 'hidden';
            if (verified) {
                buttons[i].style.background = 'lightgreen';
                buttons[i].style.color = 'black';
            }
        }
    }
    function clear() { verified = false; updateButtons(); }
    form.addEventListener('submit', function (event) { if (!verified) event.preventDefault(); });
    widgets.set(key, function () {
        clear();
        if (widgetId !== null) window.turnstile.reset(widgetId);
    });
    function setup() {
        clear();
        var layer = form.querySelector('[id="Layer1"]');
        if (!layer && widgets.size === 1) layer = document.getElementById('Layer1');
        if (layer) layer.style.top = '650px';
        if (!window.turnstile) return;
        window.turnstile.ready(function () {
            widgetId = window.turnstile.render(widget, {
                sitekey: widget.getAttribute('data-sitekey'),
                callback: function (token) { verified = !!token; updateButtons(); },
                'expired-callback': clear,
                'error-callback': clear,
                'timeout-callback': clear,
                'refresh-expired': 'auto'
            });
        });
    }
    // The rest of the form (including its submit buttons) can follow this include.
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', updateButtons);
    updateButtons();
    var timer = window.setInterval(function () {
        if (widget.isConnected) { updateButtons(); return; }
        window.clearInterval(timer);
        widgets.delete(key);
    }, 1111);
    if (window.turnstile) { setup(); return; }
    var url = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    var script = document.querySelector('script[src="' + url + '"],script[src^="' + url + '?"]');
    var loadScript = !script;
    if (loadScript) {
        script = document.createElement('script');
        script.src = url + '?render=explicit';
        script.async = true;
    }
    script.addEventListener('load', setup);
    if (loadScript) document.head.appendChild(script);
})(document.currentScript.previousElementSibling);
</script>
<br>
