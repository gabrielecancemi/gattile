// Script condiviso del footer: banner cookie e bottone FAQ.

'use strict';

(function () {
    console.group('[footer] Inizializzazione footer');
    const bottone_faq = document.getElementById('faq');
    console.info('Bottone FAQ trovato:', !!bottone_faq);

    // per il CSS di stampa.
    const footer = document.querySelector('.footer');
    if (footer) {
        footer.setAttribute('data-url', window.location.href);
        console.info('[footer] URL pagina impostato:', window.location.href);
    }

    // Cerca il cookie di consenso tra i cookie esistenti.
    const pezzi_cookie = document.cookie.split('; ');
    let consenso_presente = false;
    for (let i = 0; i < pezzi_cookie.length; i++) {
        if (pezzi_cookie[i].startsWith('consenso=')) {
            consenso_presente = true;
            console.info('[footer] Cookie consenso trovato');
        }
    }
    if (!consenso_presente) {
        console.info('[footer] Cookie consenso NON trovato');
    }

    if (bottone_faq && !consenso_presente) {
        bottone_faq.className = bottone_faq.className + ' faq-button--alzato';
        console.log('[footer] Bottone FAQ alzato perché consenso non presente');
    }

    const bottone_accetta = document.getElementById('btn-accetta-cookie');
    if (bottone_accetta) {
        bottone_accetta.addEventListener('click', function () {
            console.group('[footer] Consenso cookie accettato');
            // Scadenza impostata con max-age.
            const un_anno = 365 * 24 * 3600;
            document.cookie = 'consenso=1; max-age=' + un_anno + '; path=/; SameSite=Strict';
            console.info('Cookie consenso impostato con scadenza: 1 anno');
            console.info('Pagina in ricaricamento...');
            console.groupEnd();
            // Ricarica la pagina riassegnando l'URL corrente (oggetto location).
            window.location.href = window.location.href;
        });
    }
    console.log('✓ Footer inizializzato');
    console.groupEnd();
})();
