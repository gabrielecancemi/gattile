// Validazione del form di registrazione

'use strict';

(function () {
    let form = document.getElementById('form-registrazione');
    if (!form) return;

    let campi = {
        nome: { input: document.getElementById('in-reg-nome'), errore: document.getElementById('err-nome') },
        cognome: { input: document.getElementById('in-reg-cognome'), errore: document.getElementById('err-cognome') },
        indirizzo: { input: document.getElementById('in-reg-indirizzo'), errore: document.getElementById('err-indirizzo') },
        username: { input: document.getElementById('in-reg-username'), errore: document.getElementById('err-reg-username') },
        password: { input: document.getElementById('in-reg-password'), errore: document.getElementById('err-reg-password') },
        conferma: { input: document.getElementById('in-reg-conferma'), errore: document.getElementById('err-reg-conferma') },
        gdpr: { input: document.getElementById('in-reg-gdpr'), errore: document.getElementById('err-gdpr') }
    };

    let meter_forza = document.getElementById('forza-password');
    let testo_forza = document.getElementById('forza-password-testo');
    let barra_progresso = document.getElementById('progresso-form');
    let testo_progresso = document.getElementById('progresso-testo');

    let ETICHETTE_FORZA = [
        'Molto debole',
        'Debole',
        'Sufficiente',
        'Buona',
        'Ottima'
    ];

    function mostraErrore(info, messaggio) {
        if (!info) return;
        mostraErroreCampo(info.input, info.errore, messaggio);
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
        if (!meter_forza) {
            return;
        }

        let forza = calcolaForza(password);

        meter_forza.value = forza;

        if (testo_forza) {
            testo_forza.textContent = password
                ? ETICHETTE_FORZA[forza]
                : '';
        }
    }

    // barra progresso form
    function aggiornaProgresso() {
        let completati = 0;

        if (validaNome(false)) completati++;
        if (validaCognome(false)) completati++;
        if (validaIndirizzo(false)) completati++;
        if (validaUsername(false)) completati++;
        if (validaPassword(false)) completati++;
        if (validaConferma(false)) completati++;
        if (validaGdpr(false)) completati++;

        if (barra_progresso) {
            barra_progresso.value = completati;
        }

        if (testo_progresso) {
            testo_progresso.textContent =
                completati + ' di 7 campi completati correttamente.';
        }
    }

    function validaNome(mostra = true) {
        let v = campi.nome.input ? campi.nome.input.value.trim() : '';

        if (!v) {
            if (mostra) mostraErrore(campi.nome, 'Il nome è obbligatorio.');
            return false;
        }

        if (v.length < 2) {
            if (mostra) mostraErrore(campi.nome, 'Il nome deve contenere almeno 2 caratteri.');
            return false;
        }

        if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(v)) {
            if (mostra) mostraErrore(campi.nome, 'Il nome contiene caratteri non validi.');
            return false;
        }

        if (mostra) {
            mostraErrore(campi.nome, '');
        }
        return true;
    }

    function validaCognome(mostra = true) {
        let v = campi.cognome.input ? campi.cognome.input.value.trim() : '';

        if (!v) {
            if (mostra) mostraErrore(campi.cognome, 'Il cognome è obbligatorio.');
            return false;
        }

        if (v.length < 2) {
            if (mostra) mostraErrore(campi.cognome, 'Il cognome deve contenere almeno 2 caratteri.');
            return false;
        }

        if (!/^[a-zA-ZÀ-ÿ\s'-]+$/.test(v)) {
            if (mostra) mostraErrore(campi.cognome, 'Il cognome contiene caratteri non validi.');
            return false;
        }

        if (mostra) {
            mostraErrore(campi.cognome, '');
        }
        return true;
    }

    function validaIndirizzo(mostra = true) {
        let v = campi.indirizzo.input ? campi.indirizzo.input.value.trim() : '';

        if (!v) {
            if (mostra) mostraErrore(campi.indirizzo, 'L\'indirizzo è obbligatorio.');
            return false;
        }

        if (v.length < 5) {
            if (mostra) mostraErrore(campi.indirizzo, 'L\'indirizzo deve contenere almeno 5 caratteri.');
            return false;
        }

        if (mostra) {
            mostraErrore(campi.indirizzo, '');
        }
        return true;
    }

    function validaUsername(mostra = true) {
        let v = campi.username.input ? campi.username.input.value.trim() : '';

        if (!v) {
            if (mostra) mostraErrore(campi.username, 'Lo username è obbligatorio.');
            return false;
        }

        if (v.length < 3) {
            if (mostra) mostraErrore(campi.username, 'Lo username deve contenere almeno 3 caratteri.');
            return false;
        }

        if (v.length > 50) {
            if (mostra) mostraErrore(campi.username, 'Lo username non può superare i 50 caratteri.');
            return false;
        }

        if (!/^[a-zA-Z]/.test(v)) {
            if (mostra) mostraErrore(campi.username, 'Lo username deve iniziare con una lettera.');
            return false;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(v)) {
            if (mostra) mostraErrore(campi.username, 'Sono consentiti solo lettere, numeri e underscore (_).');
            return false;
        }

        if (mostra) {
            mostraErrore(campi.username, '');
        }
        return true;
    }

    function validaPassword(mostra = true) {
        let v = campi.password.input ? campi.password.input.value : '';

        aggiornaForza(v);

        if (!v) {
            if (mostra) mostraErrore(campi.password, 'La password è obbligatoria.');
            return false;
        }

        if (v.length < 8) {
            if (mostra) mostraErrore(campi.password, 'La password deve contenere almeno 8 caratteri.');
            return false;
        }

        if (v.length > 16) {
            if (mostra) mostraErrore(campi.password, 'La password non può superare i 16 caratteri.');
            return false;
        }

        if (!/[A-Z]/.test(v)) {
            if (mostra) mostraErrore(campi.password, 'Manca almeno una lettera maiuscola.');
            return false;
        }

        if (!/[a-z]/.test(v)) {
            if (mostra) mostraErrore(campi.password, 'Manca almeno una lettera minuscola.');
            return false;
        }

        if (!/\d/.test(v)) {
            if (mostra) mostraErrore(campi.password, 'Manca almeno un numero.');
            return false;
        }

        if (!/[^a-zA-Z\d]/.test(v)) {
            if (mostra) mostraErrore(campi.password, 'Manca almeno un carattere speciale.');
            return false;
        }

        if (mostra) {
            mostraErrore(campi.password, '');
        }
        return true;
    }

    function validaConferma(mostra = true) {
        let v = campi.conferma.input ? campi.conferma.input.value : '';
        let p = campi.password.input ? campi.password.input.value : '';

        if (!v) {
            if (mostra) mostraErrore(campi.conferma, 'Confermare la password.');
            return false;
        }

        if (v !== p) {
            if (mostra) mostraErrore(campi.conferma, 'Le due password non coincidono.');
            return false;
        }

        if (mostra) {
            mostraErrore(campi.conferma, '');
        }
        return true;
    }

    function validaGdpr(mostra = true) {
        let el = campi.gdpr.input;
        if (!el || el.checked) {
            if (mostra) mostraErrore(campi.gdpr, '');
            return true;
        }
        if (mostra) {
            mostraErrore(campi.gdpr, 'Devi accettare l\'Informativa Privacy per procedere.');
        }
        return false;
    }

    campi.nome.input?.addEventListener('blur', validaNome);
    campi.cognome.input?.addEventListener('blur', validaCognome);
    campi.indirizzo.input?.addEventListener('blur', validaIndirizzo);
    campi.username.input?.addEventListener('blur', validaUsername);
    campi.password.input?.addEventListener('blur', validaPassword);
    campi.conferma.input?.addEventListener('blur', validaConferma);
    campi.gdpr.input?.addEventListener('change', validaGdpr);

    const validatori = {
        nome: validaNome,
        cognome: validaCognome,
        indirizzo: validaIndirizzo,
        username: validaUsername,
        password: validaPassword,
        conferma: validaConferma,
        gdpr: validaGdpr
    };

    for (let chiave in campi) {

        let info = campi[chiave];
        let elemento = info.input;

        if (!elemento) {
            continue;
        }

        // Il checkbox gdpr usa 'change'
        if (chiave === 'gdpr') {
            elemento.addEventListener('change', function () {
                aggiornaProgresso();
                validaGdpr();
            });
            continue;
        }

        elemento.addEventListener('input', function () {

            aggiornaProgresso();

            if (info.errore && !info.errore.hidden) {
                let valida = validatori[chiave];

                if (valida) {
                    valida();
                }
            }
        });
    }

    if (campi.password.input) {
        campi.password.input.addEventListener('input', function () {
            aggiornaForza(this.value);

            if (campi.conferma.input?.value) {
                validaConferma();
            }
        });
    }

    // submit del form
    form.addEventListener('submit', function (evento) {

        let esiti = [
            validaNome(),
            validaCognome(),
            validaIndirizzo(),
            validaUsername(),
            validaPassword(),
            validaConferma(),
            validaGdpr()
        ];

        if (esiti.includes(false)) {
            evento.preventDefault();

            let primo = null;

            for (let chiave in campi) {
                if (campi[chiave].errore &&
                    !campi[chiave].errore.hidden) {

                    primo = chiave;
                    break;
                }
            }

            if (primo && campi[primo].input) {
                campi[primo].input.focus();
            }
        }
    });

    aggiornaProgresso();
})();