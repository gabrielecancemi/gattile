// Pulsante "Elimina tutti i miei cookie" nella pagina privacy
'use strict';

(function () {
    const bottone = document.getElementById('btn-elimina-cookie-privacy');
    if (!bottone) return;
    bottone.addEventListener('click', function () {
        // Si invia la richiesta
        fetch('interfaccia/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function () { window.location.href = 'privacy.php?eliminati=1'; },
                function () { window.location.href = 'privacy.php?eliminati=1'; });
    });
})();
