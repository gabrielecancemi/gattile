/**
 * footer.js — Script condiviso del footer.
 * Gestisce: URL corrente, banner cookie, eliminazione cookie.
 * Nessun inline JS nelle pagine PHP.
 */
'use strict';

(function () {

    /* ── URL corrente nel footer (anche per stampa) ── */
    var elUrl = document.getElementById('footer-url-corrente');
    if (elUrl) elUrl.textContent = window.location.href;

    var footer = document.querySelector('.footer');
    if (footer) footer.setAttribute('data-url', window.location.href);

    /* ── Banner cookie ── */
    var btnAccetta = document.getElementById('btn-accetta-cookie');
    if (btnAccetta) {
        btnAccetta.addEventListener('click', function () {
            var scad = new Date(Date.now() + 365 * 24 * 3600 * 1000).toUTCString();
            document.cookie = 'cookie_consenso=1; expires=' + scad + '; path=/; SameSite=Strict';
            var banner = document.getElementById('banner-cookie');
            if (banner) banner.hidden = true;
        });
    }

    /* ── Elimina cookie ── */
    var btnElimina = document.getElementById('btn-elimina-cookie');
    if (btnElimina) {
        btnElimina.addEventListener('click', function () {
            if (!confirm('Sei sicuro di voler eliminare tutti i cookie? Verrai disconnesso.')) return;
            fetch('api/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function ()  { window.location.href = 'privacy.php?eliminati=1'; })
                .catch(function () { window.location.href = 'privacy.php?eliminati=1'; });
        });
    }

})();
