/**
 * footer.js — Script condiviso del footer.
 * Gestisce: banner cookie (accettazione + ricarica pagina) e posizionamento FAQ.
 *
 * Quando l'utente accetta i cookie:
 *   1. Scrive il cookie di consenso.
 *   2. Emette l'evento 'cookieAccettati' (ascoltato da tema.js).
 *   3. Ricarica la pagina (così il server legge il nuovo cookie e
 *      può inviare il tema corretto nell'header HTTP, evitando FOUC).
 */
'use strict';

(function () {
    const faqButton = document.getElementById('faq');

    // URL corrente esposto come attributo data- per il CSS di stampa
    const footer = document.querySelector('.footer');
    if (footer) {
        footer.setAttribute('data-url', window.location.href);
    }

    // Se il consenso cookie non è presente, il pulsante FAQ si solleva
    const consensoPresente = document.cookie
        .split('; ')
        .some(function (cookie) { return cookie.startsWith('cookie_consenso='); });

    if (faqButton && !consensoPresente) {
        faqButton.classList.add('faq-button--alzato');
    }

    // Banner cookie — accettazione
    const btnAccetta = document.getElementById('btn-accetta-cookie');
    if (btnAccetta) {
        btnAccetta.addEventListener('click', function () {
            // 1. Scrivi il cookie di consenso (1 anno)
            const scad = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
            document.cookie = 'cookie_consenso=1; expires=' + scad + '; path=/; SameSite=Strict';

            // 2. Notifica tema.js che ora può usare i cookie
            document.dispatchEvent(new CustomEvent('cookieAccettati'));

            // 3. Ricarica la pagina (il server può ora leggere il cookie tema)
            window.location.reload();
        });
    }
})();
