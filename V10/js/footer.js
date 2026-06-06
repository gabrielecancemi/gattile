/**
 * footer.js — Script condiviso del footer.
 * Gestisce: banner cookie (accettazione) e posizionamento del pulsante FAQ.
 * Lo stile NON è impostato da JS: si commutano solo classi CSS (classList).
 */
'use strict';

(function () {
    const faqButton = document.getElementById('faq');

    // URL corrente esposto come attributo data- per il CSS di stampa
    // (non è uno stile: è un dato usato da attr() nel foglio di stampa).
    const footer = document.querySelector('.footer');
    if (footer) {
        footer.setAttribute('data-url', window.location.href);
    }

    // Se il consenso cookie non è presente, il banner è visibile: il
    // pulsante FAQ va alzato. Lo stato è espresso con una classe CSS.
    const consensoPresente = document.cookie
        .split('; ')
        .some((cookie) => cookie.startsWith('cookie_consenso='));

    if (faqButton && !consensoPresente) {
        faqButton.classList.add('faq-button--alzato');
    }

    // Banner cookie — accettazione
    const btnAccetta = document.getElementById('btn-accetta-cookie');
    if (btnAccetta) {
        btnAccetta.addEventListener('click', () => {
            const scad = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
            document.cookie = 'cookie_consenso=1; expires=' + scad + '; path=/; SameSite=Strict';

            const banner = document.getElementById('banner-cookie');
            if (banner) {
                banner.hidden = true;
            }
            if (faqButton) {
                faqButton.classList.remove('faq-button--alzato');
            }
        });
    }
})();
