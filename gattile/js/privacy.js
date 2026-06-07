// Pulsante "Elimina tutti i miei cookie" nella pagina privacy.
'use strict';

(function () {
    const bottone = document.getElementById('btn-elimina-cookie-privacy');
    if (!bottone) return;
    bottone.addEventListener('click', function () {
        fetch('api/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function () { window.location.href = 'privacy.php?eliminati=1'; })
            .catch(function () { window.location.href = 'privacy.php?eliminati=1'; });
    });
})();
