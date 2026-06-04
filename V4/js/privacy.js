/**
 * privacy.js — Gestisce il pulsante "Elimina tutti i miei cookie" nella pagina privacy.
 */
'use strict';

(function () {
    var btn = document.getElementById('btn-elimina-cookie-privacy');
    if (!btn) return;
    btn.addEventListener('click', function () {
        if (!confirm('Confermi l\'eliminazione di tutti i cookie? Verrai disconnesso.')) return;
        fetch('api/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function ()  { window.location.href = 'privacy.php?eliminati=1'; })
            .catch(function () { window.location.href = 'privacy.php?eliminati=1'; });
    });
})();
