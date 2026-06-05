/**
 * footer.js — Script condiviso del footer.
 * Gestisce: URL corrente (utile a stampa), banner cookie, eliminazione cookie.
 */

'use strict';

(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const consensoPresente = document.cookie
            .split('; ')
            .some(cookie => cookie.startsWith('cookie_consenso='));

        if (!consensoPresente) {
            const faqButton = document.getElementById('faq');

            if (faqButton) {
                faqButton.style.bottom = 'max(5rem, env(safe-area-inset-bottom))';
            }
        }
    });

    /* URL corrente nel footer per il CSS di stampa */
    var elUrl = document.getElementById('footer-url-corrente');
    if (elUrl) elUrl.textContent = window.location.href;

    var footer = document.querySelector('.footer');
    if (footer) footer.setAttribute('data-url', window.location.href);

    /* Banner cookie — accettazione */
    var btnAccetta = document.getElementById('btn-accetta-cookie');
    if (btnAccetta) {
        btnAccetta.addEventListener('click', function () {
            var scad = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
            document.cookie = 'cookie_consenso=1; expires=' + scad + '; path=/; SameSite=Strict';
            var banner = document.getElementById('banner-cookie');
            var faq_button = document.getElementById('faq');
            console.log(banner, 'banner nascosto');
            if (banner) {
                banner.style.display = 'none';
                faq_button.style.bottom = 'max(1rem, env(safe-area-inset-bottom))';
            }
        });
    }

})();
