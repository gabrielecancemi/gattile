// Script condiviso del footer: banner cookie e bottone FAQ.

'use strict';

(function () {
    const bottone_faq = document.getElementById('faq');

    // per il CSS di stampa.
    const footer = document.querySelector('.footer');
    if (footer) {
        footer.setAttribute('data-url', window.location.href);
    }

    // Cerca il cookie di consenso tra i cookie esistenti.
    const pezzi_cookie = document.cookie.split('; ');
    let consenso_presente = false;
    for (let i = 0; i < pezzi_cookie.length; i++) {
        if (pezzi_cookie[i].startsWith('consenso_cookie=')) {
            consenso_presente = true;
        }
    }

    if (bottone_faq && !consenso_presente) {
        bottone_faq.className = bottone_faq.className + ' faq-button--alzato';
    }

    const bottone_accetta = document.getElementById('btn-accetta-cookie');
    if (bottone_accetta) {
        bottone_accetta.addEventListener('click', function () {
            // Scadenza impostata con max-age.
            const un_anno = 365 * 24 * 3600;
            document.cookie = 'consenso_cookie=1; max-age=' + un_anno + '; path=/; SameSite=Strict';
            // Ricarica la pagina riassegnando l'URL corrente (oggetto location).
            window.location.href = window.location.href;
        });
    }
})();
