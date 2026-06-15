// Validazione del form di inserimento gatto

'use strict';

(function () {
    const form = document.getElementById('form-inserisci-gatto');
    if (!form) return;

    const campi = {
        nome: { el: document.getElementById('in-gatto-nome'), err: document.getElementById('err-gatto-nome') },
        razza: { el: document.getElementById('in-gatto-razza'), err: document.getElementById('err-gatto-razza') },
        sesso: { el: document.getElementById('in-gatto-sesso'), err: document.getElementById('err-gatto-sesso') },
        eta: { el: document.getElementById('in-gatto-eta'), err: document.getElementById('err-gatto-eta') },
        peso: { el: document.getElementById('in-gatto-peso'), err: document.getElementById('err-gatto-peso') },
        colore_mantello: { el: document.getElementById('in-gatto-mantello'), err: document.getElementById('err-gatto-colore-mantello') },
        lunghezza_pelo: { el: document.getElementById('in-gatto-pelo'), err: document.getElementById('err-gatto-lunghezza-pelo') },
        colore_occhi: { el: document.getElementById('in-gatto-occhi'), err: document.getElementById('err-gatto-colore-occhi') },
        data_arrivo: { el: document.getElementById('in-gatto-arrivo'), err: document.getElementById('err-gatto-data-arrivo') },
        descrizione: { el: document.getElementById('in-gatto-descrizione'), err: document.getElementById('err-gatto-descrizione') },
    };

    const textarea = campi.descrizione.el;
    const contatore = document.getElementById('contatore-desc');
    // maxLength è la proprietà DOM corrispondente all'attributo maxlength.
    const max_descrizione = textarea && textarea.maxLength > 0 ? textarea.maxLength : 2000;

    if (textarea && contatore) {
        textarea.addEventListener('input', function () {
            const rimanenti = max_descrizione - this.value.length;
            contatore.textContent = String(rimanenti);
            contatore.className = rimanenti < 50 ? 'contatore-basso' : '';
        });
    }

    function mostraErrore(info, messaggio) {
        if (!info) return;
        mostraErroreCampo(info.el, info.err, messaggio);
    }

    function validaNome() {

        const v = campi.nome.el ? campi.nome.el.value.trim() : '';

        if (!v) {
            mostraErrore(campi.nome, 'Il nome del gatto è obbligatorio.');
            return false;
        }

        if (v.length < 1) {
            mostraErrore(campi.nome, 'Il nome deve contenere almeno 1 carattere.');
            return false;
        }

        if (v.length > 50) {
            mostraErrore(campi.nome, 'Il nome non può superare i 50 caratteri.');
            return false;
        }

        mostraErrore(campi.nome, '');
        return true;
    }

    function validaRazza() {

        const v = campi.razza.el ? campi.razza.el.value.trim() : '';

        if (!v) {
            mostraErrore(campi.razza, 'La razza è obbligatoria.');
            return false;
        }

        if (v.length < 1) {
            mostraErrore(campi.razza, 'La razza deve contenere almeno 1 carattere.');
            return false;
        }

        if (v.length > 50) {
            mostraErrore(campi.razza, 'La razza non può superare i 50 caratteri.');
            return false;
        }

        mostraErrore(campi.razza, '');
        return true;
    }

    function validaSesso() {

        const v = campi.sesso.el ? campi.sesso.el.value : '';

        if (!v) {
            mostraErrore(campi.sesso, 'Selezionare il sesso.');
            return false;
        }

        if (!['M', 'F'].includes(v)) {
            mostraErrore(campi.sesso, 'Valore del sesso non valido.');
            return false;
        }

        mostraErrore(campi.sesso, '');
        return true;
    }

    function validaEta() {

        const valore = campi.eta.el ? campi.eta.el.value.trim() : '';

        if (!valore) {
            mostraErrore(campi.eta, 'L\'età è obbligatoria.');
            return false;
        }

        const v = parseInt(valore, 10);

        if (isNaN(v)) {
            mostraErrore(campi.eta, 'L\'età deve essere un numero.');
            return false;
        }

        if (v < 0) {
            mostraErrore(campi.eta, 'L\'età non può essere negativa.');
            return false;
        }

        if (v > 300) {
            mostraErrore(campi.eta, 'L\'età non può superare 300 mesi.');
            return false;
        }

        mostraErrore(campi.eta, '');
        return true;
    }

    function validaPeso() {

        const valore = campi.peso.el ? campi.peso.el.value.trim() : '';

        if (!valore) {
            mostraErrore(campi.peso, 'Il peso è obbligatorio.');
            return false;
        }

        const v = parseFloat(valore);

        if (isNaN(v)) {
            mostraErrore(campi.peso, 'Il peso deve essere un numero.');
            return false;
        }

        if (v < 0.1) {
            mostraErrore(campi.peso, 'Il peso deve essere almeno 0.1 kg.');
            return false;
        }

        if (v > 20) {
            mostraErrore(campi.peso, 'Il peso non può superare 20 kg.');
            return false;
        }

        mostraErrore(campi.peso, '');
        return true;
    }

    function validaColoreMantello() {

        const v = campi.colore_mantello.el
            ? campi.colore_mantello.el.value.trim()
            : '';

        if (!v) {
            mostraErrore(
                campi.colore_mantello,
                'Inserire il colore del mantello.'
            );
            return false;
        }

        mostraErrore(campi.colore_mantello, '');
        return true;
    }

    function validaLunghezzaPelo() {

        const v = campi.lunghezza_pelo.el
            ? campi.lunghezza_pelo.el.value
            : '';

        if (!v) {
            mostraErrore(
                campi.lunghezza_pelo,
                'Selezionare la lunghezza del pelo.'
            );
            return false;
        }

        mostraErrore(campi.lunghezza_pelo, '');
        return true;
    }

    function validaColoreOcchi() {

        const v = campi.colore_occhi.el
            ? campi.colore_occhi.el.value.trim()
            : '';

        if (!v) {
            mostraErrore(
                campi.colore_occhi,
                'Inserire il colore degli occhi.'
            );
            return false;
        }

        mostraErrore(campi.colore_occhi, '');
        return true;
    }

    function validaDataArrivo() {

        const v = campi.data_arrivo.el
            ? campi.data_arrivo.el.value
            : '';

        if (!v) {
            mostraErrore(
                campi.data_arrivo,
                'La data di arrivo è obbligatoria.'
            );
            return false;
        }

        const dt = new Date(v);

        if (isNaN(dt.getFullYear())) {
            mostraErrore(
                campi.data_arrivo,
                'Formato data non valido.'
            );
            return false;
        }

        if (dt > new Date()) {
            mostraErrore(
                campi.data_arrivo,
                'La data non può essere futura.'
            );
            return false;
        }

        mostraErrore(campi.data_arrivo, '');
        return true;
    }

    function validaDescrizione() {

        const v = campi.descrizione.el
            ? campi.descrizione.el.value.trim()
            : '';

        if (!v) {
            mostraErrore(
                campi.descrizione,
                'La descrizione è obbligatoria.'
            );
            return false;
        }

        if (v.length < 10) {
            mostraErrore(
                campi.descrizione,
                'La descrizione deve contenere almeno 10 caratteri.'
            );
            return false;
        }

        if (v.length > max_descrizione) {
            mostraErrore(
                campi.descrizione,
                'La descrizione supera il limite consentito.'
            );
            return false;
        }

        mostraErrore(campi.descrizione, '');
        return true;
    }

    const validatori = {
        'in-gatto-nome': validaNome,
        'in-gatto-razza': validaRazza,
        'in-gatto-sesso': validaSesso,
        'in-gatto-eta': validaEta,
        'in-gatto-peso': validaPeso,
        'in-gatto-mantello': validaColoreMantello,
        'in-gatto-pelo': validaLunghezzaPelo,
        'in-gatto-occhi': validaColoreOcchi,
        'in-gatto-arrivo': validaDataArrivo,
        'in-gatto-descrizione': validaDescrizione,
    };

    for (const chiave in campi) {
        const info = campi[chiave];
        if (!info.el) continue;

        info.el.addEventListener('blur', function () {
            const fn = validatori[this.id];
            if (fn) fn();
        });

        info.el.addEventListener('input', function () {
            if (info.err && !info.err.hidden) {
                const fn = validatori[this.id];
                if (fn) fn();
            }
        });
    }

    // submit del form
    form.addEventListener('submit', function (evento) {
        const esiti = [
            validaNome(), validaRazza(), validaSesso(), validaEta(), validaPeso(),
            validaColoreMantello(), validaLunghezzaPelo(), validaColoreOcchi(),
            validaDataArrivo(), validaDescrizione()
        ];
        if (esiti.indexOf(false) !== -1) {
            evento.preventDefault();
            // decide a chi mettere il focus
            let primo;
            for (const chiave in campi) {
                if (campi[chiave].err && !campi[chiave].err.hidden) {
                    primo = chiave;
                    break;
                }
            }
            if (primo && campi[primo].el) {
                campi[primo].el.focus();
            }
        }
    });
})();
