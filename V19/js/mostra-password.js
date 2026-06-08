// Pulsante mostra/nascondi password. Commuta il campo indicato da aria-controls
// tra type="password" e type="text"; lo stato dell'icona è gestito via classe.
'use strict';

(function () {
    const pulsanti = document.querySelectorAll('.mostra-password');
    if (!pulsanti.length) return;

    pulsanti.forEach(function (pulsante) {
        const id_campo = pulsante.getAttribute('aria-controls');
        const campo = id_campo ? document.getElementById(id_campo) : null;
        if (!campo) return;

        pulsante.addEventListener('click', function () {
            const mostra = campo.getAttribute('type') === 'password';
            campo.setAttribute('type', mostra ? 'text' : 'password');
            pulsante.setAttribute('aria-pressed', String(mostra));
            pulsante.classList.toggle('password-visibile', mostra);
            pulsante.setAttribute('aria-label', mostra ? 'Nascondi la password' : 'Mostra la password');
            campo.focus();
        });
    });
})();
