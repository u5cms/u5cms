<?php
/* Existing responses must finish loading before their initial values can be sent. */
function u5FormDataPrefillGuardScript() {
    return <<<'HTML'
<script>
(function () {
    var ready = false;
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
    window.u5FormDataPrefillReady = function () { ready = true; };
}());
</script>
HTML;
}
