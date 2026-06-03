/**
 * registrazione.js — Validazione completa del form di registrazione.
 * Gestisce errori inline, indicatore forza password, progress completamento.
 * Vanilla JavaScript, 'use strict'.
 */
'use strict';

(function () {
    const form        = document.getElementById('form-registrazione');
    if (!form) return;

    const campi = {
        nome:     { input: document.getElementById('reg-nome'),     errore: document.getElementById('err-nome')     },
        cognome:  { input: document.getElementById('reg-cognome'),  errore: document.getElementById('err-cognome')  },
        indirizzo:{ input: document.getElementById('reg-indirizzo'),errore: document.getElementById('err-indirizzo')},
        username: { input: document.getElementById('reg-username'), errore: document.getElementById('err-reg-username')},
        password: { input: document.getElementById('reg-password'), errore: document.getElementById('err-reg-password')},
        conferma: { input: document.getElementById('reg-conferma'), errore: document.getElementById('err-reg-conferma')},
    };

    const forzaMeter   = document.getElementById('forza-password');
    const forzaTesto   = document.getElementById('forza-password-testo');
    const progresso    = document.getElementById('progresso-form');
    const progressoTxt = document.getElementById('progresso-testo');
    const btnSubmit    = document.getElementById('btn-registra');

    /** Pattern password: 8-16 car, ≥1 maiusc, ≥1 minusc, ≥1 cifra, ≥1 spec */
    const REGEX_PWD = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,16}$/;
    /** Pattern username: inizia con lettera, 3-50 caratteri alfanumerici o _ */
    const REGEX_USR = /^[a-zA-Z][a-zA-Z0-9_]{2,49}$/;

    // ── Helpers ──────────────────────────────────────────────────────────────

    function mostraErrore(info, messaggio) {
        if (messaggio) {
            info.errore.textContent = '⚠ ' + messaggio;
            info.errore.hidden = false;
            info.input.setAttribute('aria-invalid', 'true');
        } else {
            info.errore.hidden = true;
            info.errore.textContent = '';
            info.input.removeAttribute('aria-invalid');
        }
    }

    /** Calcola un punteggio grezzo per la password (0-4) */
    function calcolaForza(pwd) {
        let punteggio = 0;
        if (pwd.length >= 8)            punteggio++;
        if (/[A-Z]/.test(pwd))          punteggio++;
        if (/[a-z]/.test(pwd))          punteggio++;
        if (/\d/.test(pwd))             punteggio++;
        if (/[^a-zA-Z\d]/.test(pwd))   punteggio++;
        return Math.min(punteggio, 4);
    }

    const ETICHETTE_FORZA = ['Molto debole', 'Debole', 'Sufficiente', 'Buona', 'Ottima'];

    function aggiornaForzaMeter(pwd) {
        const f = calcolaForza(pwd);
        if (forzaMeter) {
            forzaMeter.value = f;
            forzaTesto.textContent = pwd ? ETICHETTE_FORZA[f] : '';
        }
    }

    /** Aggiorna la barra di progresso in base ai campi validi */
    function aggiornaProgresso() {
        let completati = 0;
        const totale = 6;

        if (campi.nome.input.value.trim().length >= 2)              completati++;
        if (campi.cognome.input.value.trim().length >= 2)           completati++;
        if (campi.indirizzo.input.value.trim().length >= 5)         completati++;
        if (REGEX_USR.test(campi.username.input.value.trim()))      completati++;
        if (REGEX_PWD.test(campi.password.input.value))             completati++;
        if (campi.conferma.input.value === campi.password.input.value
            && campi.conferma.input.value.length > 0)               completati++;

        if (progresso)    progresso.value = completati;
        if (progressoTxt) progressoTxt.textContent = `${completati} di ${totale} campi completati correttamente.`;

        // Abilita submit solo se tutto è compilato
        const pronto = (completati === totale);
        if (btnSubmit) {
            btnSubmit.disabled = !pronto;
            btnSubmit.setAttribute('aria-disabled', String(!pronto));
        }
    }

    // ── Validatori ───────────────────────────────────────────────────────────

    function validaNome() {
        const v = campi.nome.input.value.trim();
        if (v.length < 2) { mostraErrore(campi.nome, 'Il nome deve avere almeno 2 caratteri.'); return false; }
        mostraErrore(campi.nome, ''); return true;
    }

    function validaCognome() {
        const v = campi.cognome.input.value.trim();
        if (v.length < 2) { mostraErrore(campi.cognome, 'Il cognome deve avere almeno 2 caratteri.'); return false; }
        mostraErrore(campi.cognome, ''); return true;
    }

    function validaIndirizzo() {
        const v = campi.indirizzo.input.value.trim();
        if (v.length < 5) { mostraErrore(campi.indirizzo, 'Inserisci un indirizzo valido (almeno 5 caratteri).'); return false; }
        mostraErrore(campi.indirizzo, ''); return true;
    }

    function validaUsername() {
        const v = campi.username.input.value.trim();
        if (!REGEX_USR.test(v)) {
            mostraErrore(campi.username, 'Username non valido: inizia con lettera, solo lettere/numeri/underscore, 3-50 caratteri.');
            return false;
        }
        mostraErrore(campi.username, ''); return true;
    }

    function validaPassword() {
        const v = campi.password.input.value;
        aggiornaForzaMeter(v);
        if (!REGEX_PWD.test(v)) {
            mostraErrore(campi.password, 'Password non valida: 8-16 caratteri con maiuscola, minuscola, numero e carattere speciale.');
            return false;
        }
        mostraErrore(campi.password, ''); return true;
    }

    function validaConferma() {
        if (campi.conferma.input.value !== campi.password.input.value) {
            mostraErrore(campi.conferma, 'Le due password non coincidono.'); return false;
        }
        if (!campi.conferma.input.value) {
            mostraErrore(campi.conferma, 'Conferma la password.'); return false;
        }
        mostraErrore(campi.conferma, ''); return true;
    }

    // ── Contatore caratteri descrizione (riusato dall'inserisci_gatto.js) ───

    const descrizione = document.getElementById('gatto-descrizione');
    if (descrizione) {
        const contatore = document.getElementById('contatore-desc');
        const max = parseInt(descrizione.getAttribute('maxlength') || '2000', 10);
        descrizione.addEventListener('input', () => {
            if (contatore) contatore.textContent = String(max - descrizione.value.length);
        });
    }

    // ── Listener ─────────────────────────────────────────────────────────────

    // Blur: validazione singolo campo
    campi.nome.input.addEventListener('blur',     validaNome);
    campi.cognome.input.addEventListener('blur',  validaCognome);
    campi.indirizzo.input.addEventListener('blur',validaIndirizzo);
    campi.username.input.addEventListener('blur', validaUsername);
    campi.password.input.addEventListener('blur', validaPassword);
    campi.conferma.input.addEventListener('blur', validaConferma);

    // Input: aggiorna progresso in tempo reale
    Object.values(campi).forEach(function(c) {
        c.input.addEventListener('input', aggiornaProgresso);
    });

    // Aggiornamento meter forza su input password
    campi.password.input.addEventListener('input', function() {
        aggiornaForzaMeter(this.value);
        // Rivalida conferma se già compilata
        if (campi.conferma.input.value) validaConferma();
    });

    // Submit: validazione completa
    form.addEventListener('submit', function(evento) {
        const risultati = [
            validaNome(),
            validaCognome(),
            validaIndirizzo(),
            validaUsername(),
            validaPassword(),
            validaConferma(),
        ];

        if (risultati.includes(false)) {
            evento.preventDefault();
            // Focus sul primo campo con errore
            const primoErrore = Object.values(campi).find(c => c.errore && !c.errore.hidden);
            if (primoErrore) primoErrore.input.focus();
            console.warn('[Registrazione] Submit bloccata: validazione fallita');
        } else {
            console.log('[Registrazione] Form valido, invio in corso');
        }
    });

    // Inizializzazione barra progresso
    aggiornaProgresso();

    console.log('[Registrazione] Script validazione caricato');
})();
