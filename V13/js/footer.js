// Script condiviso del footer: banner cookie (accettazione + ricarica) e
// posizionamento del pulsante FAQ.
//
// Quando l'utente accetta i cookie:
//   1. scrive il cookie di consenso;
//   2. emette l'evento 'cookieAccettati' (ascoltato da tema.js);
//   3. ricarica la pagina, così il server legge il nuovo cookie e può inviare
//      il tema corretto nell'header HTTP (niente FOUC).
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
        .some(function (cookie) { return cookie.startsWith('cookie_consenso='); });

    if (bottone_faq && !consenso_presente) {
        bottone_faq.classList.add('faq-button--alzato');
    }

    const bottone_accetta = document.getElementById('btn-accetta-cookie');
    if (bottone_accetta) {
        bottone_accetta.addEventListener('click', function () {
            const scadenza = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
            document.cookie = 'cookie_consenso=1; expires=' + scadenza + '; path=/; SameSite=Strict';
            document.dispatchEvent(new CustomEvent('cookieAccettati'));
            window.location.reload();
        });
    }
})();
