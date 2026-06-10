// Script condiviso del footer: banner cookie (accettazione + ricarica) e
// posizionamento del pulsante FAQ.
//
// Quando l'utente accetta i cookie:
//   1. scrive il cookie di consenso;
//   2. ricarica la pagina.
// La preferenza del tema NON dipende dal consenso: è salvata nel localStorage
// (vedi tema.js) ed è quindi disponibile anche senza accettare i cookie.
'use strict';

(function () {
    const bottone_faq = document.getElementById('faq');

    // URL corrente esposto come attributo data- per il CSS di stampa.
    const footer = document.querySelector('.footer');
    if (footer) {
        footer.setAttribute('data-url', window.location.href);
    }

    const consenso_presente = document.cookie
        .split('; ')
        .some(function (cookie) { return cookie.startsWith('consenso_cookie='); });

    if (bottone_faq && !consenso_presente) {
        bottone_faq.classList.add('faq-button--alzato');
    }

    const bottone_accetta = document.getElementById('btn-accetta-cookie');
    if (bottone_accetta) {
        bottone_accetta.addEventListener('click', function () {
            const scadenza = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
            document.cookie = 'consenso_cookie=1; expires=' + scadenza + '; path=/; SameSite=Strict';
            window.location.reload();
        });
    }
})();
