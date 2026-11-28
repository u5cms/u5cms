<?php
/* Existing responses must finish loading before their initial values can be sent. */
function u5FormDataPrefillGuardScript($mode = 'edit') {
    $script = <<<'HTML'
<script>
(function () {
    if (typeof window.u5FormDataPrefillReady === 'function') return;
    var ready = false;
    var mode = __U5_PREFILL_MODE__;
    function pending(form) {
        return !ready && form && form.getAttribute('name') === 'u5form';
    }
    function waitForData() {
        alert('The existing form data is still loading. Please wait, or reload the page if loading has failed.');
    }
    document.addEventListener('submit', function (event) {
        if (pending(event.target)) {
            event.preventDefault();
            event.stopImmediatePropagation();
            waitForData();
        }
    }, true);
    var submit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        if (pending(this)) { waitForData(); return; }
        return submit.apply(this, arguments);
    };
    window.u5FormDataPrefillReady = function (completedMode) {
        if (completedMode === mode) ready = true;
    };
}());
</script>
HTML;
    return str_replace('__U5_PREFILL_MODE__', json_encode($mode), $script);
}

function u5FormDataGuardMonoMarkup($markup) {
    if (preg_match('~<iframe\b[^>]*\bsrc\s*=\s*["\x27]?(?:\./)?monofill(?:shared)?\.php\?~i', $markup)) {
        return u5FormDataPrefillGuardScript('mono').$markup;
    }
    return $markup;
}
