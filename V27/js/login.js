// Validazione del form di login

'use strict';

(function () {
    const form = document.getElementById('form-login');
    const campo_username = document.getElementById('username');
    const campo_password = document.getElementById('password');
    const errore_username = document.getElementById('err-username');
    const errore_password = document.getElementById('err-password');

    if (!form) return;

    function mostraErrore(campo, elemento, messaggio) {
        if (!elemento) return;
        if (messaggio) {
            elemento.textContent = messaggio;
            elemento.hidden = false;
            if (campo) campo.setAttribute('aria-invalid', 'true');
        } else {
            elemento.hidden = true;
            elemento.textContent = '';
            if (campo) campo.removeAttribute('aria-invalid');
        }
    }

    function validaUsername() {
        const valore = campo_username ? campo_username.value.trim() : '';
        if (!valore) { mostraErrore(campo_username, errore_username, 'Inserire l\'username.'); return false; }
        if (!/^[a-zA-Z]/.test(valore)) { mostraErrore(campo_username, errore_username, 'Lo username deve iniziare con una lettera.'); return false; }
        mostraErrore(campo_username, errore_username, ''); return true;
    }

    function validaPassword() {
        if (!campo_password || !campo_password.value) { mostraErrore(campo_password, errore_password, 'Inserire la password.'); return false; }
        mostraErrore(campo_password, errore_password, ''); return true;
    }

    if (campo_username) {
        campo_username.addEventListener('blur', validaUsername);
        campo_username.addEventListener('input', function () { mostraErrore(campo_username, errore_username, ''); });
    }
    if (campo_password) {
        campo_password.addEventListener('blur', validaPassword);
        campo_password.addEventListener('input', function () { mostraErrore(campo_password, errore_password, ''); });
    }

    // submit del form
    form.addEventListener('submit', function (evento) {
        const ok_username = validaUsername();
        const ok_password = validaPassword();
        if (!ok_username || !ok_password) {
            evento.preventDefault();
            if (!ok_username && campo_username) campo_username.focus();
            else if (!ok_password && campo_password) campo_password.focus();
        }
    });
})();
