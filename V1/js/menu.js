(function () {
    'use strict';
    const btn = document.querySelector('.menu-toggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
        const aperto = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!aperto));
        document.getElementById('menu-principale')
            ?.classList.toggle('aperto', !aperto);
        document.getElementById('userStatusBox')
            ?.classList.toggle('aperto', !aperto);
    });
})();