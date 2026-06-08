// Validazione del form di registrazione: indicatore forza password (meter),
// barra di completamento, controlli su blur e submit.
'use strict';

(function () {
    let form = document.getElementById('form-registrazione');
    if (!form) return;

    let campi = {
        nome:      { input: document.getElementById('reg-nome'),      errore: document.getElementById('err-nome') },
        cognome:   { input: document.getElementById('reg-cognome'),   errore: document.getElementById('err-cognome') },
        indirizzo: { input: document.getElementById('reg-indirizzo'), errore: document.getElementById('err-indirizzo') },
        username:  { input: document.getElementById('reg-username'),  errore: document.getElementById('err-reg-username') },
        password:  { input: document.getElementById('reg-password'),  errore: document.getElementById('err-reg-password') },
        conferma:  { input: document.getElementById('reg-conferma'),  errore: document.getElementById('err-reg-conferma') },
    };

    let meter_forza  = document.getElementById('forza-password');
    let testo_forza  = document.getElementById('forza-password-testo');
    let barra_progresso = document.getElementById('progresso-form');
    let testo_progresso = document.getElementById('progresso-testo');
    let bottone_invia   = document.getElementById('btn-registra');

    let REGEX_PASSWORD = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,16}$/;
    let REGEX_USERNAME = /^[a-zA-Z][a-zA-Z0-9_]{2,49}$/;
    let ETICHETTE_FORZA = ['Molto debole', 'Debole', 'Sufficiente', 'Buona', 'Ottima'];

    function mostraErrore(info, messaggio) {
        if (!info || !info.errore) return;
        if (messaggio) {
            info.errore.textContent = messaggio;
            info.errore.hidden = false;
            if (info.input) info.input.setAttribute('aria-invalid', 'true');
        } else {
            info.errore.hidden = true;
            info.errore.textContent = '';
            if (info.input) info.input.removeAttribute('aria-invalid');
        }
    }

    function calcolaForza(password) {
        let punti = 0;
        if (password.length >= 8) punti++;
        if (/[A-Z]/.test(password)) punti++;
        if (/[a-z]/.test(password)) punti++;
        if (/\d/.test(password)) punti++;
        if (/[^a-zA-Z\d]/.test(password)) punti++;
        return Math.min(punti, 4);
    }

    function aggiornaForza(password) {
        if (!meter_forza) return;
        let forza = calcolaForza(password);
        meter_forza.value = forza;
        if (testo_forza) testo_forza.textContent = password ? ETICHETTE_FORZA[forza] : '';
    }

    function aggiornaProgresso() {
        let completati = 0;
        if (campi.nome.input && campi.nome.input.value.trim().length >= 2) completati++;
        if (campi.cognome.input && campi.cognome.input.value.trim().length >= 2) completati++;
        if (campi.indirizzo.input && campi.indirizzo.input.value.trim().length >= 5) completati++;
        if (campi.username.input && REGEX_USERNAME.test(campi.username.input.value.trim())) completati++;
        if (campi.password.input && REGEX_PASSWORD.test(campi.password.input.value)) completati++;
        if (campi.conferma.input && campi.password.input &&
            campi.conferma.input.value === campi.password.input.value &&
            campi.conferma.input.value.length > 0) completati++;

        if (barra_progresso) barra_progresso.value = completati;
        if (testo_progresso) testo_progresso.textContent = completati + ' di 6 campi completati correttamente.';
        if (bottone_invia) {
            bottone_invia.disabled = completati < 6;
        }
    }

    function validaNome() { let v = campi.nome.input ? campi.nome.input.value.trim() : ''; if (v.length < 2) { mostraErrore(campi.nome, 'Nome: almeno 2 caratteri.'); return false; } mostraErrore(campi.nome, ''); return true; }
    function validaCognome() { let v = campi.cognome.input ? campi.cognome.input.value.trim() : ''; if (v.length < 2) { mostraErrore(campi.cognome, 'Cognome: almeno 2 caratteri.'); return false; } mostraErrore(campi.cognome, ''); return true; }
    function validaIndirizzo() { let v = campi.indirizzo.input ? campi.indirizzo.input.value.trim() : ''; if (v.length < 5) { mostraErrore(campi.indirizzo, 'Indirizzo: almeno 5 caratteri.'); return false; } mostraErrore(campi.indirizzo, ''); return true; }
    function validaUsername() { let v = campi.username.input ? campi.username.input.value.trim() : ''; if (!REGEX_USERNAME.test(v)) { mostraErrore(campi.username, 'Username: inizia con una lettera; solo lettere, numeri e underscore; 3-50 caratteri.'); return false; } mostraErrore(campi.username, ''); return true; }
    function validaPassword() { let v = campi.password.input ? campi.password.input.value : ''; aggiornaForza(v); if (!REGEX_PASSWORD.test(v)) { mostraErrore(campi.password, 'Password: 8-16 caratteri con almeno una maiuscola, una minuscola, un numero e un carattere speciale.'); return false; } mostraErrore(campi.password, ''); return true; }
    function validaConferma() { let v = campi.conferma.input ? campi.conferma.input.value : ''; let p = campi.password.input ? campi.password.input.value : ''; if (!v) { mostraErrore(campi.conferma, 'Conferma password: ripeti la password.'); return false; } if (v !== p) { mostraErrore(campi.conferma, 'Le due password non coincidono.'); return false; } mostraErrore(campi.conferma, ''); return true; }

    campi.nome.input && campi.nome.input.addEventListener('blur', validaNome);
    campi.cognome.input && campi.cognome.input.addEventListener('blur', validaCognome);
    campi.indirizzo.input && campi.indirizzo.input.addEventListener('blur', validaIndirizzo);
    campi.username.input && campi.username.input.addEventListener('blur', validaUsername);
    campi.password.input && campi.password.input.addEventListener('blur', validaPassword);
    campi.conferma.input && campi.conferma.input.addEventListener('blur', validaConferma);

    Object.keys(campi).forEach(function (chiave) {
        let elemento = campi[chiave].input;
        if (elemento) elemento.addEventListener('input', aggiornaProgresso);
    });

    if (campi.password.input) {
        campi.password.input.addEventListener('input', function () {
            aggiornaForza(this.value);
            if (campi.conferma.input && campi.conferma.input.value) validaConferma();
        });
    }

    form.addEventListener('submit', function (evento) {
        let esiti = [validaNome(), validaCognome(), validaIndirizzo(), validaUsername(), validaPassword(), validaConferma()];
        if (esiti.indexOf(false) !== -1) {
            evento.preventDefault();
            let primo = Object.keys(campi).find(function (chiave) { return campi[chiave].errore && !campi[chiave].errore.hidden; });
            if (primo && campi[primo].input) campi[primo].input.focus();
        }
    });

    aggiornaProgresso();
})();
