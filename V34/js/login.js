// Validazione del form di login

'use strict';

(function () {
    console.group('[login] Inizializzazione validazione login');
    const form = document.getElementById('form-login');
    const campo_username = document.getElementById('in-login-username');
    const campo_password = document.getElementById('in-login-password');
    const errore_username = document.getElementById('err-username');
    const errore_password = document.getElementById('err-password');

    if (!form) {
        console.warn('[login] Form login non trovato');
        console.groupEnd();
        return;
    }
    console.info('[login] Form trovato, inizializzo validatori');

    function validaUsername() {
        const valore = campo_username ? campo_username.value.trim() : '';
        if (!valore) { 
            mostraErroreCampo(campo_username, errore_username, 'Inserire l\'username.');
            console.warn('[login] Username vuoto');
            return false; 
        }
        if (!/^[a-zA-Z]/.test(valore)) { 
            mostraErroreCampo(campo_username, errore_username, 'Lo username deve iniziare con una lettera.');
            console.warn('[login] Username non valido (non inizia con lettera)');
            return false; 
        }
        mostraErroreCampo(campo_username, errore_username, '');
        console.info('[login] Username valido:', valore);
        return true;
    }

    function validaPassword() {
        if (!campo_password || !campo_password.value) { 
            mostraErroreCampo(campo_password, errore_password, 'Inserire la password.');
            console.warn('[login] Password vuota');
            return false; 
        }
        mostraErroreCampo(campo_password, errore_password, '');
        console.info('[login] Password inserita');
        return true;
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
        console.group('[login] Invio form');
        const ok_username = validaUsername();
        const ok_password = validaPassword();
        if (!ok_username || !ok_password) {
            console.warn('[login] Form non valido');
            evento.preventDefault();
            if (!ok_username && campo_username) campo_username.focus();
            else if (!ok_password && campo_password) campo_password.focus();
            console.groupEnd();
        } else {
            console.info('[login] Form valido, invio autorizzato');
            console.groupEnd();
        }
    });
    console.log('✓ Validazione login inizializzata');
    console.groupEnd();
})();
