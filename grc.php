<script>
var u5turnstileToken='';

function u5turnstileUpdate() {
var widget=document.getElementById('u5turnstile');
var form=widget && widget.closest('form');
if (!form) return;
var buttons=form.querySelectorAll('input[type="submit"],input[type="image"],button[type="submit"],button:not([type])');
for (var i=0;i<buttons.length;i++) {
buttons[i].style.visibility=u5turnstileToken ? 'visible' : 'hidden';
if (u5turnstileToken) {
buttons[i].style.background='lightgreen';
buttons[i].style.color='black';
}
}
if (!u5turnstileToken && document.getElementById('Layer1')) document.getElementById('Layer1').style.top='650px';
}

function u5turnstileSuccess(token) {
u5turnstileToken=token;
u5turnstileUpdate();
}

function u5turnstileClear() {
u5turnstileToken='';
u5turnstileUpdate();
}

function u5turnstileReset() {
u5turnstileClear();
if (window.turnstile) window.turnstile.reset();
}

function u5turnstileSetup() {
var widget=document.getElementById('u5turnstile');
var form=widget && widget.closest('form');
if (!form) return;
form.addEventListener('submit',function(event) {
if (!u5turnstileToken) event.preventDefault();
});
u5turnstileUpdate();
setInterval(u5turnstileUpdate,1111);
}

if (document.readyState==='loading') document.addEventListener('DOMContentLoaded',u5turnstileSetup);
else u5turnstileSetup();
</script>
<div id="u5turnstile" class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($u5turnstilesitekey ?? '', ENT_QUOTES, 'WINDOWS-1252') ?>" data-callback="u5turnstileSuccess" data-expired-callback="u5turnstileClear" data-error-callback="u5turnstileClear" data-timeout-callback="u5turnstileClear" data-refresh-expired="auto"></div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<br>