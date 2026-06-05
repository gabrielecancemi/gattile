/**
 * login.js — Validazione form login.
 * Solo validazione lato client; il server decide l'esito finale.
 */
'use strict';

(function () {
    var form   = document.getElementById('form-login');
    var inputU = document.getElementById('username');
    var inputP = document.getElementById('password');
    var errU   = document.getElementById('err-username');
    var errP   = document.getElementById('err-password');

    if (!form) return;

    function mostraErrore(input, el, msg) {
        if (!el) return;
        if (msg) {
            el.textContent = '⚠ ' + msg;
            el.hidden = false;
            if (input) input.setAttribute('aria-invalid', 'true');
        } else {
            el.hidden = true;
            el.textContent = '';
            if (input) input.removeAttribute('aria-invalid');
        }
    }

    function validaUsername() {
        var v = inputU ? inputU.value.trim() : '';
        if (!v) { mostraErrore(inputU, errU, 'Inserisci il tuo username.'); return false; }
        if (!/^[a-zA-Z]/.test(v)) { mostraErrore(inputU, errU, 'Lo username deve iniziare con una lettera.'); return false; }
        mostraErrore(inputU, errU, ''); return true;
    }

    function validaPassword() {
        if (!inputP || !inputP.value) { mostraErrore(inputP, errP, 'Inserisci la password.'); return false; }
        mostraErrore(inputP, errP, ''); return true;
    }

    if (inputU) {
        inputU.addEventListener('blur',  validaUsername);
        inputU.addEventListener('input', function () { mostraErrore(inputU, errU, ''); });
    }
    if (inputP) {
        inputP.addEventListener('blur',  validaPassword);
        inputP.addEventListener('input', function () { mostraErrore(inputP, errP, ''); });
    }

    form.addEventListener('submit', function (e) {
        var okU = validaUsername();
        var okP = validaPassword();
        if (!okU || !okP) {
            e.preventDefault();
            if (!okU && inputU) inputU.focus();
            else if (!okP && inputP) inputP.focus();
        }
    });

    console.log('[Login] Script caricato');
})();
