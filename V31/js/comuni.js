'use strict';

function ripuliscihtml(stringa) {
    return String(stringa)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function mostraErroreCampo(campo, output, messaggio) {
    if (!output) return;
    if (messaggio) {
        output.textContent = messaggio;
        output.hidden = false;
        if (campo) campo.setAttribute('aria-invalid', 'true');
    } else {
        output.textContent = '';
        output.hidden = true;
        if (campo) campo.removeAttribute('aria-invalid');
    }
}

function etaInParole(mesi) {
    if (mesi < 12) return mesi + ' ' + (mesi === 1 ? 'mese' : 'mesi');
    const anni = Math.floor(mesi / 12);
    const resto = mesi % 12;
    let testo = anni + ' ' + (anni === 1 ? 'anno' : 'anni');
    if (resto > 0) testo += ' e ' + resto + ' ' + (resto === 1 ? 'mese' : 'mesi');
    return testo;
}

function messaggioErrore(testo) {
    return '<output class="messaggio messaggio-errore" role="alert" aria-live="assertive">'
        + ripuliscihtml(testo) + '</output>';
}

function mostraMessaggioComune(elemento, testo, tipo) {
    if (!elemento) return;
    elemento.textContent = testo;
    // Riassegna className: 'sr-solo' sparisce e l'elemento torna visibile.
    elemento.className = 'messaggio messaggio-' + tipo;
}

function bottoniConferma(elemento, bottoni) {
    if (!elemento) return;
    let html = '<p>';
    bottoni.forEach(function (b) {
        html += '<a href="' + ripuliscihtml(b.href) + '" class="btn btn-primario">'
            + ripuliscihtml(b.testo) + '</a> ';
    });
    html += '</p>';
    elemento.innerHTML += html;
}
