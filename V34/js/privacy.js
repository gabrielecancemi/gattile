// Pulsante "Elimina tutti i miei cookie" nella pagina privacy
'use strict';

(function () {
    console.group('[privacy] Inizializzazione privacy');
    const bottone = document.getElementById('btn-privacy-elimina-cookie');
    if (!bottone) {
        console.warn('[privacy] Bottone eliminazione cookie non trovato');
        console.groupEnd();
        return;
    }
    console.info('[privacy] Bottone trovato');
    
    bottone.addEventListener('click', function () {
        console.info('[privacy] Richiesta eliminazione cookie');
        // Si invia la richiesta
        fetch('interfaccia/elimina_cookie.php', { method: 'POST', credentials: 'same-origin' })
            .then(function (r) { 
                console.info('[privacy] Risposta ricevuta');
                return r.json(); 
            })
            .then(function () { 
                console.log('✓ Cookie eliminati');
                window.location.href = 'privacy.php?eliminati=1'; 
            },
            function () { 
                console.warn('[privacy] Errore durante eliminazione');
                window.location.href = 'privacy.php?eliminati=1'; 
            });
    });
    console.log('✓ Privacy inizializzato');
    console.groupEnd();
})();
