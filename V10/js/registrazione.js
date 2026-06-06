/**
 * registrazione.js — Validazione form registrazione.
 * Indicatore forza password (meter), progress completamento, validazione blur+submit.
 */
'use strict';

(function () {
    let form = document.getElementById('form-registrazione');
    if (!form) return;

    let campi = {
        nome:     { input: document.getElementById('reg-nome'),     errore: document.getElementById('err-nome')     },
        cognome:  { input: document.getElementById('reg-cognome'),  errore: document.getElementById('err-cognome')  },
        indirizzo:{ input: document.getElementById('reg-indirizzo'),errore: document.getElementById('err-indirizzo')},
        username: { input: document.getElementById('reg-username'), errore: document.getElementById('err-reg-username')},
        password: { input: document.getElementById('reg-password'), errore: document.getElementById('err-reg-password')},
        conferma: { input: document.getElementById('reg-conferma'), errore: document.getElementById('err-reg-conferma')},
    };

    let forzaMeter = document.getElementById('forza-password');
    let forzaTesto = document.getElementById('forza-password-testo');
    let progresso  = document.getElementById('progresso-form');
    let progrTesto = document.getElementById('progresso-testo');
    let btnSubmit  = document.getElementById('btn-registra');

    let REGEX_PWD = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,16}$/;
    let REGEX_USR = /^[a-zA-Z][a-zA-Z0-9_]{2,49}$/;
    let ETICHETTE  = ['Molto debole', 'Debole', 'Sufficiente', 'Buona', 'Ottima'];

    function mostraErrore(info, msg) {
        if (!info || !info.errore) return;
        if (msg) {
            info.errore.textContent = 'Errore: ' + msg;
            info.errore.hidden = false;
            if (info.input) info.input.setAttribute('aria-invalid', 'true');
        } else {
            info.errore.hidden = true;
            info.errore.textContent = '';
            if (info.input) info.input.removeAttribute('aria-invalid');
        }
    }

    function calcolaForza(pwd) {
        let p = 0;
        if (pwd.length >= 8)          p++;
        if (/[A-Z]/.test(pwd))        p++;
        if (/[a-z]/.test(pwd))        p++;
        if (/\d/.test(pwd))           p++;
        if (/[^a-zA-Z\d]/.test(pwd)) p++;
        return Math.min(p, 4);
    }

    function aggiornaForza(pwd) {
        if (!forzaMeter) return;
        let f = calcolaForza(pwd);
        forzaMeter.value = f;
        if (forzaTesto) forzaTesto.textContent = pwd ? ETICHETTE[f] : '';
    }

    function aggiornaProgresso() {
        let ok = 0;
        if (campi.nome.input && campi.nome.input.value.trim().length >= 2) ok++;
        if (campi.cognome.input && campi.cognome.input.value.trim().length >= 2) ok++;
        if (campi.indirizzo.input && campi.indirizzo.input.value.trim().length >= 5) ok++;
        if (campi.username.input && REGEX_USR.test(campi.username.input.value.trim())) ok++;
        if (campi.password.input && REGEX_PWD.test(campi.password.input.value)) ok++;
        if (campi.conferma.input && campi.password.input &&
            campi.conferma.input.value === campi.password.input.value &&
            campi.conferma.input.value.length > 0) ok++;

        if (progresso) progresso.value = ok;
        if (progrTesto) progrTesto.textContent = ok + ' di 6 campi completati correttamente.';
        if (btnSubmit) {
            btnSubmit.disabled = ok < 6;
        }
    }

    function validaNome()    { let v = campi.nome.input ? campi.nome.input.value.trim() : ''; if (v.length < 2) { mostraErrore(campi.nome, 'Nome: almeno 2 caratteri.'); return false; } mostraErrore(campi.nome, ''); return true; }
    function validaCognome() { let v = campi.cognome.input ? campi.cognome.input.value.trim() : ''; if (v.length < 2) { mostraErrore(campi.cognome, 'Cognome: almeno 2 caratteri.'); return false; } mostraErrore(campi.cognome, ''); return true; }
    function validaInd()     { let v = campi.indirizzo.input ? campi.indirizzo.input.value.trim() : ''; if (v.length < 5) { mostraErrore(campi.indirizzo, 'Indirizzo: almeno 5 caratteri.'); return false; } mostraErrore(campi.indirizzo, ''); return true; }
    function validaUser()    { let v = campi.username.input ? campi.username.input.value.trim() : ''; if (!REGEX_USR.test(v)) { mostraErrore(campi.username, 'Username non valido (inizia con lettera, 3-50 caratteri).'); return false; } mostraErrore(campi.username, ''); return true; }
    function validaPwd()     { let v = campi.password.input ? campi.password.input.value : ''; aggiornaForza(v); if (!REGEX_PWD.test(v)) { mostraErrore(campi.password, 'Password non valida (8-16 car., maiusc., minusc., numero, spec.).'); return false; } mostraErrore(campi.password, ''); return true; }
    function validaConf()    { let v = campi.conferma.input ? campi.conferma.input.value : ''; let p = campi.password.input ? campi.password.input.value : ''; if (!v) { mostraErrore(campi.conferma, 'Conferma la password.'); return false; } if (v !== p) { mostraErrore(campi.conferma, 'Le due password non coincidono.'); return false; } mostraErrore(campi.conferma, ''); return true; }

    /* Listener blur */
    campi.nome.input     && campi.nome.input.addEventListener('blur',    validaNome);
    campi.cognome.input  && campi.cognome.input.addEventListener('blur',  validaCognome);
    campi.indirizzo.input&& campi.indirizzo.input.addEventListener('blur',validaInd);
    campi.username.input && campi.username.input.addEventListener('blur', validaUser);
    campi.password.input && campi.password.input.addEventListener('blur', validaPwd);
    campi.conferma.input && campi.conferma.input.addEventListener('blur', validaConf);

    /* Listener input: aggiorna progresso */
    Object.keys(campi).forEach(function (k) {
        let el = campi[k].input;
        if (el) el.addEventListener('input', aggiornaProgresso);
    });

    /* Forza password in tempo reale */
    if (campi.password.input) {
        campi.password.input.addEventListener('input', function () {
            aggiornaForza(this.value);
            if (campi.conferma.input && campi.conferma.input.value) validaConf();
        });
    }

    /* Submit */
    form.addEventListener('submit', function (e) {
        let ok = [validaNome(), validaCognome(), validaInd(), validaUser(), validaPwd(), validaConf()];
        if (ok.indexOf(false) !== -1) {
            e.preventDefault();
            let primo = Object.keys(campi).find(function (k) { return campi[k].errore && !campi[k].errore.hidden; });
            if (primo && campi[primo].input) campi[primo].input.focus();
        }
    });

    aggiornaProgresso();
})();
