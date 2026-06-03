/**
 * login.js — Validazione lato client del form di login.
 * Vanilla JavaScript, 'use strict'.
 * Non impedisce la submit per username/password errate (il server decide),
 * ma blocca invii con campi vuoti.
 */
'use strict';

(function () {
    const form    = document.getElementById('form-login');
    const inputU  = document.getElementById('username');
    const inputP  = document.getElementById('password');
    const errU    = document.getElementById('err-username');
    const errP    = document.getElementById('err-password');

    if (!form) return; // Sicurezza: uscita se il DOM non è quello atteso

    /**
     * Mostra o nasconde un messaggio di errore inline.
     * Aggiorna anche aria-invalid sull'input associato.
     */
    function mostraErrore(input, elemErrore, messaggio) {
        if (messaggio) {
            elemErrore.textContent = messaggio;
            elemErrore.hidden = false;
            input.setAttribute('aria-invalid', 'true');
            input.setAttribute('aria-describedby', elemErrore.id);
        } else {
            elemErrore.hidden = true;
            elemErrore.textContent = '';
            input.removeAttribute('aria-invalid');
        }
    }

    // Validazione username: non vuoto, inizia con lettera
    function validaUsername() {
        const val = inputU.value.trim();
        if (!val) {
            mostraErrore(inputU, errU, 'Inserisci il tuo username.');
            return false;
        }
        if (!/^[a-zA-Z]/.test(val)) {
            mostraErrore(inputU, errU, 'Lo username deve iniziare con una lettera.');
            return false;
        }
        mostraErrore(inputU, errU, '');
        return true;
    }

    // Validazione password: non vuota
    function validaPassword() {
        if (!inputP.value) {
            mostraErrore(inputP, errP, 'Inserisci la password.');
            return false;
        }
        mostraErrore(inputP, errP, '');
        return true;
    }

    // Feedback in tempo reale (senza bloccare la digitazione)
    inputU.addEventListener('blur', validaUsername);
    inputP.addEventListener('blur', validaPassword);

    // Pulizia errore alla modifica
    inputU.addEventListener('input', () => mostraErrore(inputU, errU, ''));
    inputP.addEventListener('input', () => mostraErrore(inputP, errP, ''));

    // Intercetta la submit per validazione finale
    form.addEventListener('submit', function (evento) {
        const okU = validaUsername();
        const okP = validaPassword();

        if (!okU || !okP) {
            evento.preventDefault();
            // Metti il focus sul primo campo errato
            if (!okU) {
                inputU.focus();
            } else {
                inputP.focus();
            }
            console.warn('[Login] Submit bloccata per validazione fallita');
        } else {
            console.log('[Login] Form validato, invio in corso');
        }
    });

    console.log('[Login] Script validazione caricato');
})();
