// Validazione del form di inserimento gatto (area admin). Lo stato "in
// esaurimento" del contatore caratteri è gestito via classe CSS, mai stile
// in-line.
'use strict';

(function () {
    const form = document.getElementById('form-inserisci-gatto');
    if (!form) return;

    const campi = {
        nome:            { el: document.getElementById('gatto-nome'),            err: document.getElementById('err-gatto-nome')           },
        razza:           { el: document.getElementById('gatto-razza'),           err: document.getElementById('err-gatto-razza')          },
        sesso:           { el: document.getElementById('gatto-sesso'),           err: document.getElementById('err-gatto-sesso')          },
        eta:             { el: document.getElementById('gatto-eta'),             err: document.getElementById('err-gatto-eta')            },
        peso:            { el: document.getElementById('gatto-peso'),            err: document.getElementById('err-gatto-peso')           },
        colore_mantello: { el: document.getElementById('gatto-colore-mantello'), err: document.getElementById('err-gatto-colore-mantello') },
        lunghezza_pelo:  { el: document.getElementById('gatto-lunghezza-pelo'),  err: document.getElementById('err-gatto-lunghezza-pelo') },
        colore_occhi:    { el: document.getElementById('gatto-colore-occhi'),    err: document.getElementById('err-gatto-colore-occhi')   },
        data_arrivo:     { el: document.getElementById('gatto-data-arrivo'),     err: document.getElementById('err-gatto-data-arrivo')    },
        descrizione:     { el: document.getElementById('gatto-descrizione'),     err: document.getElementById('err-gatto-descrizione')    },
    };

    const textarea  = campi.descrizione.el;
    const contatore = document.getElementById('contatore-desc');
    const max_descrizione = textarea ? parseInt(textarea.getAttribute('maxlength') || '2000', 10) : 2000;

    if (textarea && contatore) {
        textarea.addEventListener('input', function () {
            const rimanenti = max_descrizione - this.value.length;
            contatore.textContent = String(rimanenti);
            contatore.classList.toggle('contatore-basso', rimanenti < 50);
        });
    }

    function mostraErrore(info, messaggio) {
        if (!info || !info.err) return;
        if (messaggio) {
            info.err.textContent = messaggio;
            info.err.hidden = false;
            if (info.el) info.el.setAttribute('aria-invalid', 'true');
        } else {
            info.err.hidden = true;
            info.err.textContent = '';
            if (info.el) info.el.removeAttribute('aria-invalid');
        }
    }

    function validaNome()           { const v = campi.nome.el ? campi.nome.el.value.trim() : ''; if (!v || v.length > 50) { mostraErrore(campi.nome, 'Nome: obbligatorio, 1-50 caratteri.'); return false; } mostraErrore(campi.nome, ''); return true; }
    function validaRazza()          { const v = campi.razza.el ? campi.razza.el.value.trim() : ''; if (!v || v.length > 50) { mostraErrore(campi.razza, 'Razza: obbligatoria, max 50 caratteri.'); return false; } mostraErrore(campi.razza, ''); return true; }
    function validaSesso()          { const v = campi.sesso.el ? campi.sesso.el.value : ''; if (!['M','F'].includes(v)) { mostraErrore(campi.sesso, 'Sesso: seleziona un valore.'); return false; } mostraErrore(campi.sesso, ''); return true; }
    function validaEta()            { const v = campi.eta.el ? parseInt(campi.eta.el.value, 10) : NaN; if (isNaN(v) || v < 0 || v > 300) { mostraErrore(campi.eta, 'Età: numero di mesi tra 0 e 300.'); return false; } mostraErrore(campi.eta, ''); return true; }
    function validaPeso()           { const v = campi.peso.el ? parseFloat(campi.peso.el.value) : NaN; if (isNaN(v) || v < 0.1 || v > 20) { mostraErrore(campi.peso, 'Peso: valore in kg tra 0.1 e 20.'); return false; } mostraErrore(campi.peso, ''); return true; }
    function validaColoreMantello() { const v = campi.colore_mantello.el ? campi.colore_mantello.el.value.trim() : ''; if (!v) { mostraErrore(campi.colore_mantello, 'Colore del mantello: campo obbligatorio.'); return false; } mostraErrore(campi.colore_mantello, ''); return true; }
    function validaLunghezzaPelo()  { const v = campi.lunghezza_pelo.el ? campi.lunghezza_pelo.el.value : ''; if (!v) { mostraErrore(campi.lunghezza_pelo, 'Lunghezza del pelo: seleziona un valore.'); return false; } mostraErrore(campi.lunghezza_pelo, ''); return true; }
    function validaColoreOcchi()    { const v = campi.colore_occhi.el ? campi.colore_occhi.el.value.trim() : ''; if (!v) { mostraErrore(campi.colore_occhi, 'Colore degli occhi: campo obbligatorio.'); return false; } mostraErrore(campi.colore_occhi, ''); return true; }
    function validaDataArrivo()     { const v = campi.data_arrivo.el ? campi.data_arrivo.el.value : ''; if (!v) { mostraErrore(campi.data_arrivo, 'Data di arrivo: campo obbligatorio.'); return false; } const dt = new Date(v); if (isNaN(dt.getTime()) || dt > new Date()) { mostraErrore(campi.data_arrivo, 'Data di arrivo: inserisci una data valida (non futura).'); return false; } mostraErrore(campi.data_arrivo, ''); return true; }
    function validaDescrizione()    { const v = campi.descrizione.el ? campi.descrizione.el.value.trim() : ''; if (v.length < 10) { mostraErrore(campi.descrizione, 'Descrizione: almeno 10 caratteri.'); return false; } mostraErrore(campi.descrizione, ''); return true; }

    const validatori = {
        'gatto-nome':            validaNome,
        'gatto-razza':           validaRazza,
        'gatto-sesso':           validaSesso,
        'gatto-eta':             validaEta,
        'gatto-peso':            validaPeso,
        'gatto-colore-mantello': validaColoreMantello,
        'gatto-lunghezza-pelo':  validaLunghezzaPelo,
        'gatto-colore-occhi':    validaColoreOcchi,
        'gatto-data-arrivo':     validaDataArrivo,
        'gatto-descrizione':     validaDescrizione,
    };

    Object.keys(campi).forEach(function (chiave) {
        const info = campi[chiave];
        if (!info.el) return;
        info.el.addEventListener('blur', function () { const fn = validatori[this.id]; if (fn) fn(); });
        info.el.addEventListener('input', function () { if (info.err && !info.err.hidden) { const fn = validatori[this.id]; if (fn) fn(); } });
    });

    form.addEventListener('submit', function (evento) {
        const esiti = [
            validaNome(), validaRazza(), validaSesso(), validaEta(), validaPeso(),
            validaColoreMantello(), validaLunghezzaPelo(), validaColoreOcchi(),
            validaDataArrivo(), validaDescrizione()
        ];
        if (esiti.indexOf(false) !== -1) {
            evento.preventDefault();
            const primo = Object.keys(campi).find(function (chiave) { return campi[chiave].err && !campi[chiave].err.hidden; });
            if (primo && campi[primo].el) campi[primo].el.focus();
        }
    });
})();
