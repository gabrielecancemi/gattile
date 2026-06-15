// Componente React dei gatti

'use strict';

(function () {
    const { useState, useEffect } = React;

    function emettiSelezione(gatti) {
        document.dispatchEvent(new CustomEvent('gattiSelezionatiAggiornati', {
            detail: { gatti },
            bubbles: true,
        }));
    }

    // Converte una data ISO nel formato GG/MM/AAAA.
    function dataItaliana(iso) {
        if (!iso) return '';
        const parti = String(iso).slice(0, 10).split('-');
        if (parti.length !== 3) return '';
        return parti[2] + '/' + parti[1] + '/' + parti[0];
    }

    // Card singola

    function CardGatto({ gatto, selezionabile, selezionata, onToggle, nuovo }) {
        const etichetta_sesso = gatto.sesso === 'M' ? 'Maschio' : 'Femmina';

        function gestisciClick() { if (selezionabile) onToggle(gatto); }
        function gestisciTasto(evento) {
            if (selezionabile && (evento.key === 'Enter' || evento.key === ' ')) {
                evento.preventDefault();
                onToggle(gatto);
            }
        }

        return (
            <li>
                <article
                    className={'card-gatto' + (selezionata ? ' selezionata' : '')}
                    onClick={gestisciClick}
                    onKeyDown={gestisciTasto}
                    tabIndex={selezionabile ? 0 : undefined}
                    aria-labelledby={'gatto-' + gatto.id}
                    aria-label={
                        selezionabile
                            ? gatto.nome + (selezionata ? ' — selezionato' : ' — clicca per selezionare')
                            : undefined
                    }
                >
                    <h3 className="sr-solo">Informazioni sul gatto</h3>
                    {/* Badge selezione — SVG */}
                    <svg className="card-badge-selezione" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
                        <circle cx="16" cy="16" r="16" fill="saddlebrown" />
                        <path d="M9 16.5l4.5 4.5L23 11" fill="none" stroke="white" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                    <picture>
                        <source srcSet={gatto.foto} />
                        <img
                            src={gatto.foto}
                            alt={'Placeholder di ' + gatto.nome}
                            loading="lazy"
                            decoding="async"
                            className="foto-gatto"
                        />
                    </picture>

                    <section className="card-gatto-corpo">
                        <h4 id={'gatto-' + gatto.id}>
                            {gatto.nome}
                            {nuovo && <strong className="badge-nuovo">Nuovo</strong>}
                        </h4>

                        <ul className="card-gatto-meta" aria-label="Caratteristiche principali">
                            <li className="tag">{etichetta_sesso}</li>
                            <li className="tag">{etaInParole(gatto.eta)}</li>
                            <li className="tag">{gatto.colore_mantello}</li>
                            <li className="tag">Pelo {gatto.lunghezza_pelo}</li>
                            <li className="tag">{gatto.razza}</li>
                        </ul>

                        <p className="card-gatto-descr">{gatto.descrizione}</p>

                        <dl>
                            <dt>Peso</dt>
                            <dd><data value={gatto.peso}>{gatto.peso} kg</data></dd>
                            <dt>Occhi</dt>
                            <dd>{gatto.colore_occhi}</dd>
                            <dt>Arrivato il</dt>
                            <dd>
                                <time dateTime={gatto.data_arrivo}>
                                    {dataItaliana(gatto.data_arrivo)}
                                </time>
                            </dd>
                        </dl>
                    </section>
                </article>
            </li>
        );
    }

    // Componente principale

    function GattiApp({ utenteLoggato, isAdmin }) {
        const [gatti, setGatti] = useState([]);
        const [caricamento, setCaricamento] = useState(true);
        const [errore, setErrore] = useState('');
        const [ricerca, setRicerca] = useState('');
        const [ordinamento, setOrdinamento] = useState('data_arrivo_desc');
        // Gli ID dei gatti selezionati sono tenuti in un semplice array.
        const [selezionati, setSelezionati] = useState([]);

        // Recupera i gatti dal backend al primo render.
        useEffect(function () {
            setCaricamento(true);
            setErrore('');
            // Errori di risposta
            function gestisciErrore() {
                console.error('[GattiReact] errore');
                setErrore('Impossibile caricare i gatti, riprova tra qualche minuto.');
                setCaricamento(false);
            }
            fetch('interfaccia/recupera_gatti.php', { credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) {
                        return null;
                    }
                    return r.json();
                })
                .then(function (dati) {
                    if (dati === null || dati.errore) {
                        gestisciErrore();
                        return;
                    }
                    setGatti(dati.gatti || []);
                    setCaricamento(false);
                });
        }, []);

        // A ogni cambio di selezione notifica il form Vanilla JS.
        useEffect(function () {
            // Elenco dei gatti selezionati.
            const selezionatiOggetti = [];
            for (let i = 0; i < gatti.length; i++) {
                if (selezionati.indexOf(gatti[i].id) !== -1) {
                    selezionatiOggetti.push(gatti[i]);
                }
            }
            emettiSelezione(selezionatiOggetti);
        }, [selezionati, gatti]);

        // Azzeramento selezione dopo prenotazione
        useEffect(function () {
            function azzera() { setSelezionati([]); }
            document.addEventListener('gattiDeselezionaTutti', azzera);
            return function () { document.removeEventListener('gattiDeselezionaTutti', azzera); };
        }, []);

        const cambiaSelezione = function (gatto) {
            setSelezionati(function (precedenti) {
                if (precedenti.indexOf(gatto.id) !== -1) {
                    // Già presente: lo rimuove.
                    const ridotti = [];
                    for (let i = 0; i < precedenti.length; i++) {
                        if (precedenti[i] !== gatto.id) {
                            ridotti.push(precedenti[i]);
                        }
                    }
                    return ridotti;
                }
                // Non presente: aggiunge l'id in coda.
                return precedenti.concat([gatto.id]);
            });
        };

        // Filtro per testo
        const termine = ricerca.trim().toLowerCase();
        const gatti_filtrati = [];
        for (let i = 0; i < gatti.length; i++) {
            const g = gatti[i];
            if (termine === '' ||
                g.nome.toLowerCase().includes(termine) ||
                g.descrizione.toLowerCase().includes(termine)) {
                gatti_filtrati.push(g);
            }
        }
        const gatti_visibili = gatti_filtrati
            .sort(function (a, b) {
                switch (ordinamento) {
                    case 'eta_asc': return a.eta - b.eta;
                    case 'eta_desc': return b.eta - a.eta;
                    case 'colore_asc':
                        // Confronto alfabetico.
                        if (a.colore_mantello < b.colore_mantello) return -1;
                        if (a.colore_mantello > b.colore_mantello) return 1;
                        return 0;
                    case 'data_arrivo_asc': return new Date(a.data_arrivo) - new Date(b.data_arrivo);
                    default: return new Date(b.data_arrivo) - new Date(a.data_arrivo);
                }
            });

        // Gli ultimi 2 gatti hanno il badge "Nuovo"
        const copia = gatti.slice();

        copia.sort(function (a, b) {
            return new Date(b.data_arrivo) - new Date(a.data_arrivo);
        });

        const duePiuRecenti = copia.slice(0, 2);

        const id_nuovi = [];

        for (let i = 0; i < duePiuRecenti.length; i++) {
            id_nuovi.push(duePiuRecenti[i].id);
        }

        // Aiuti per utente
        if (caricamento) {
            return (
                <p className="caricamento" role="status" aria-live="polite">
                    Caricamento schede gatti in corso…
                </p>
            );
        }

        if (errore) {
            return (
                <output className="messaggio messaggio-errore" role="alert" aria-live="assertive">
                    {errore}
                </output>
            );
        }

        return (
            <section className="react-gatti-wrap" aria-labelledby="titolo-lista-gatti">
                <h2 id="titolo-lista-gatti" className="sr-solo">Lista gatti disponibili</h2>

                {/* Barra ricerca e ordinamento*/}
                <form
                    id="form-ricerca-gatti"
                    className="barra-controlli"
                    role="search"
                    aria-label="Filtra e ordina i gatti"
                    onSubmit={function (e) { e.preventDefault(); }}
                >
                    <label htmlFor="ricerca-gatto">
                        Cerca per nome o descrizione
                        <input
                            type="search"
                            id="ricerca-gatto"
                            value={ricerca}
                            onChange={function (e) { setRicerca(e.target.value); }}
                            placeholder="Es. giocoso, bianco…"
                            aria-controls="lista-gatti"
                        />
                    </label>
                    <label htmlFor="ordina-gatti">
                        Ordina per
                        <select
                            id="ordina-gatti"
                            value={ordinamento}
                            onChange={function (e) { setOrdinamento(e.target.value); }}
                        >
                            <option value="data_arrivo_desc">Data arrivo (più recente)</option>
                            <option value="data_arrivo_asc">Data arrivo (meno recente)</option>
                            <option value="eta_asc">Età (più giovane)</option>
                            <option value="eta_desc">Età (più vecchio)</option>
                            <option value="colore_asc">Colore mantello (A-Z)</option>
                        </select>
                    </label>
                </form>

                {/* Stato per screen reader */}
                <p aria-live="polite" className="sr-solo" role="status">
                    {gatti_visibili.length === gatti.length
                        ? gatti.length + ' gatti disponibili.'
                        : gatti_visibili.length + ' gatti trovati su ' + gatti.length + '.'}
                    {selezionati.length > 0 ? ' ' + selezionati.length + ' selezionati.' : ''}
                </p>

                {/* Istruzione selezione */}
                {utenteLoggato && !isAdmin && (
                    <p aria-live="polite">
                        Clicca su una card per selezionare il gatto.
                        {selezionati.length > 0 && (
                            <strong> {selezionati.length} {selezionati.length === 1 ? 'gatto selezionato' : 'gatti selezionati'}.</strong>
                        )}
                    </p>
                )}

                {
                    gatti_visibili.length === 0 ? (
                        <p role="status" aria-live="polite">
                            Nessun gatto corrisponde alla ricerca «{ricerca}».
                        </p>
                    ) : (
                        <ul
                            id="lista-gatti"
                            className="griglia-gatti"
                            aria-label="Elenco gatti disponibili"
                        >
                            {(() => {
                                const cards = [];

                                for (let i = 0; i < gatti_visibili.length; i++) {
                                    const gatto = gatti_visibili[i];

                                    cards.push(
                                        <CardGatto
                                            key={gatto.id}
                                            gatto={gatto}
                                            selezionabile={utenteLoggato && !isAdmin}
                                            selezionata={selezionati.indexOf(gatto.id) !== -1}
                                            onToggle={cambiaSelezione}
                                            nuovo={id_nuovi.indexOf(gatto.id) !== -1}
                                        />
                                    );
                                }

                                return cards;
                            })()}
                        </ul>
                    )
                }
            </section>
        );
    }

    // Creazione

    const radice = document.getElementById('react-gatti-root');
    if (!radice) {
        console.error('[GattiReact] elemento root non trovato');
        return;
    }

    const utente_loggato = radice.getAttribute('data-utente-loggato') === 'true';
    const is_admin = radice.getAttribute('data-is-admin') === 'true';
    radice.removeAttribute('aria-busy');

    ReactDOM.createRoot(radice).render(
        React.createElement(GattiApp, { utenteLoggato: utente_loggato, isAdmin: is_admin })
    );
})();
