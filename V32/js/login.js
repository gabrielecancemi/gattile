// Validazione del form di login

'use strict';

(function () {
    const form = document.getElementById('form-login');
    const campo_username = document.getElementById('username');
    const campo_password = document.getElementById('password');
    const errore_username = document.getElementById('err-username');
    const errore_password = document.getElementById('err-password');

    if (!form) return;

    function validaUsername() {
        const valore = campo_username ? campo_username.value.trim() : '';
        if (!valore) { mostraErroreCampo(campo_username, errore_username, 'Inserire l\'username.'); return false; }
        if (!/^[a-zA-Z]/.test(valore)) { mostraErroreCampo(campo_username, errore_username, 'Lo username deve iniziare con una lettera.'); return false; }
        mostraErroreCampo(campo_username, errore_username, ''); return true;
    }

    function validaPassword() {
        if (!campo_password || !campo_password.value) { mostraErroreCampo(campo_password, errore_password, 'Inserire la password.'); return false; }
        mostraErroreCampo(campo_password, errore_password, ''); return true;
    }

    if (campo_username) {
        campo_username.addEventListener('blur', validaUsername);
        campo_username.addEventListener('input', function () { mostraErroreCampo(campo_username, errore_username, ''); });
    }
    if (campo_password) {
        campo_password.addEventListener('blur', validaPassword);
        campo_password.addEventListener('input', function () { mostraErroreCampo(campo_password, errore_password, ''); });
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
