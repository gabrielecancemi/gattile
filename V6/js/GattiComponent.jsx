/**
 * GattiComponent.jsx — Componente React: lista, filtro, ordinamento e selezione gatti.
 *
 * Struttura HTML identica alle card PHP (index.php) per coerenza visiva.
 * Comunicazione verso il form VanillaJS tramite CustomEvent 'gattiSelezionatiAggiornati'.
 * Motivazione CustomEvent: disaccoppiamento nativo tra React e Vanilla JS
 * senza variabili globali, funziona tra librerie diverse, testabile in isolamento.
 */
'use strict';

(function () {
    const { useState, useEffect, useCallback } = React;

    /* ── Utilità ─────────────────────────────────────────────────── */

    function formattaEta(mesi) {
        if (mesi < 12) return mesi + ' ' + (mesi === 1 ? 'mese' : 'mesi');
        const anni = Math.floor(mesi / 12);
        const resto = mesi % 12;
        let s = anni + ' ' + (anni === 1 ? 'anno' : 'anni');
        if (resto > 0) s += ' e ' + resto + ' ' + (resto === 1 ? 'mese' : 'mesi');
        return s;
    }

    function emettiSelezione(gatti) {
        document.dispatchEvent(new CustomEvent('gattiSelezionatiAggiornati', {
            detail: { gatti },
            bubbles: true,
        }));
        console.log('[GattiComponent] Selezione aggiornata:', gatti.length, 'gatti');
    }

    /* ── Card singola — struttura identica al PHP di index.php ───── */

    function CardGatto({ gatto, selezionabile, selezionata, onToggle }) {
        const etichettaSesso = gatto.sesso === 'M' ? 'Maschio' : 'Femmina';

        function handleClick() { if (selezionabile) onToggle(gatto); }
        function handleKeyDown(e) {
            if (selezionabile && (e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();
                onToggle(gatto);
            }
        }

        return (
            <li>
                <article
                    className={'card-gatto' + (selezionata ? ' selezionata' : '')}
                    onClick={handleClick}
                    onKeyDown={handleKeyDown}
                    tabIndex={selezionabile ? 0 : undefined}
                    role={selezionabile ? 'checkbox' : undefined}
                    aria-checked={selezionabile ? selezionata : undefined}
                    aria-label={
                        gatto.nome +
                        (selezionabile
                            ? (selezionata ? ' — selezionato' : ' — clicca per selezionare')
                            : '')
                    }
                >
                    {/* Badge selezione — nascosto finché non selezionato */}
                    <mark className="card-badge-selezione" aria-hidden="true">✓</mark>

                    <figure>
                        <img
                            src={gatto.img}
                            alt={'Sagoma di un gatto — foto di ' + gatto.nome + ' non ancora disponibile'}
                            width="320"
                            height="240"
                            loading="lazy"
                        />
                        <figcaption className="sr-solo">Placeholder foto per {gatto.nome}</figcaption>
                    </figure>

                    {/* Corpo card — sezione semantica come nel PHP */}
                    <section className="card-gatto-corpo">
                        <h3>{gatto.nome}</h3>

                        <ul className="card-gatto-meta" aria-label="Caratteristiche principali">
                            <li className="tag">{etichettaSesso}</li>
                            <li className="tag">{formattaEta(gatto.eta)}</li>
                            <li className="tag">{gatto.colore_mantello}</li>
                            <li className="tag">{gatto.lunghezza_pelo}</li>
                            <li className="tag">{gatto.razza}</li>
                        </ul>

                        <p>{gatto.descrizione}</p>

                        <dl>
                            <dt>Peso</dt>
                            <dd>
                                <data value={gatto.peso}>{gatto.peso} kg</data>
                            </dd>
                            <dt>Occhi</dt>
                            <dd>{gatto.colore_occhi}</dd>
                            <dt>Arrivato il</dt>
                            <dd>
                                <time dateTime={gatto.data_arrivo}>
                                    {new Date(gatto.data_arrivo).toLocaleDateString('it-IT')}
                                </time>
                            </dd>
                        </dl>
                    </section>
                </article>
            </li>
        );
    }

    /* ── Componente principale ───────────────────────────────────── */

    function GattiApp({ utenteLoggato, isAdmin }) {
        const [gatti, setGatti] = useState([]);
        const [caricamento, setCaricamento] = useState(true);
        const [errore, setErrore] = useState('');
        const [ricerca, setRicerca] = useState('');
        const [ordinamento, setOrdinamento] = useState('data_arrivo_desc');
        const [selezionati, setSelezionati] = useState(new Set());

        /* Recupera gatti dal backend */
        useEffect(function () {
            setCaricamento(true);
            setErrore('');
            fetch('api/gatti.php', { credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) throw new Error('Risposta server: ' + r.status);
                    return r.json();
                })
                .then(function (data) {
                    if (data.errore) throw new Error(data.errore);
                    setGatti(data.gatti || []);
                })
                .catch(function (err) {
                    console.error('[GattiComponent] Errore:', err.message);
                    setErrore('Impossibile caricare i gatti: ' + err.message);
                })
                .finally(function () { setCaricamento(false); });
        }, []);

        /* Emette CustomEvent ogni volta che la selezione cambia */
        useEffect(function () {
            emettiSelezione(gatti.filter(function (g) { return selezionati.has(g.id); }));
        }, [selezionati, gatti]);

        const toggleSelezione = useCallback(function (gatto) {
            setSelezionati(function (prev) {
                const nuovi = new Set(prev);
                if (nuovi.has(gatto.id)) { nuovi.delete(gatto.id); } else { nuovi.add(gatto.id); }
                return nuovi;
            });
        }, []);

        /* Filtra e ordina */
        const gattiVisibili = gatti
            .filter(function (g) {
                if (!ricerca.trim()) return true;
                const q = ricerca.trim().toLowerCase();
                return g.nome.toLowerCase().includes(q) || g.descrizione.toLowerCase().includes(q);
            })
            .sort(function (a, b) {
                switch (ordinamento) {
                    case 'eta_asc': return a.eta - b.eta;
                    case 'eta_desc': return b.eta - a.eta;
                    case 'colore_asc': return a.colore_mantello.localeCompare(b.colore_mantello);
                    case 'data_arrivo_asc': return new Date(a.data_arrivo) - new Date(b.data_arrivo);
                    default: return new Date(b.data_arrivo) - new Date(a.data_arrivo);
                }
            });

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
                    <abbr aria-hidden="true">⚠</abbr> {errore}
                </output>
            );
        }

        return (
            <section aria-labelledby="titolo-lista-gatti">
                <h2 id="titolo-lista-gatti" className="sr-solo">Lista gatti disponibili</h2>

                {/* Barra ricerca e ordinamento */}
                <search className="barra-controlli" aria-label="Filtra e ordina i gatti">
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
                            <option value="colore_asc">Colore mantello (A→Z)</option>
                        </select>
                    </label>
                </search>

                {/* Stato per screen reader */}
                <p aria-live="polite" className="sr-solo" role="status">
                    {gattiVisibili.length === gatti.length
                        ? gatti.length + ' gatti disponibili.'
                        : gattiVisibili.length + ' gatti trovati su ' + gatti.length + '.'}
                    {selezionati.size > 0 ? ' ' + selezionati.size + ' selezionati.' : ''}
                </p>

                {/* Istruzione selezione */}
                {utenteLoggato && !isAdmin && (
                    <p className="aiuto-campo" aria-live="polite">
                        Clicca su una card per selezionare il gatto.
                        {selezionati.size > 0 && (
                            <strong> {selezionati.size} {selezionati.size === 1 ? 'gatto selezionato' : 'gatti selezionati'}.</strong>
                        )}
                    </p>
                )}

                {gattiVisibili.length === 0 ? (
                    <p role="status" aria-live="polite">
                        Nessun gatto corrisponde alla ricerca «{ricerca}».
                    </p>
                ) : (
                    <ul id="lista-gatti" className="griglia-gatti"
                        aria-label="Elenco gatti disponibili">
                        {gattiVisibili.map(function (gatto) {
                            return (
                                <CardGatto
                                    key={gatto.id}
                                    gatto={gatto}
                                    selezionabile={utenteLoggato && !isAdmin}
                                    selezionata={selezionati.has(gatto.id)}
                                    onToggle={toggleSelezione}
                                />
                            );
                        })}
                    </ul>
                )}
            </section>
        );
    }

    /* ── Mount ───────────────────────────────────────────────────── */

    const radice = document.getElementById('react-gatti-root');
    if (!radice) {
        console.error('[GattiComponent] Elemento root non trovato');
        return;
    }

    const utenteLoggato = radice.getAttribute('data-utente-loggato') === 'true';
    const isAdmin = radice.getAttribute('data-is-admin') === 'true';
    radice.removeAttribute('aria-busy');

    ReactDOM.createRoot(radice).render(
        React.createElement(GattiApp, { utenteLoggato, isAdmin })
    );

    console.log('[GattiComponent] Montato — loggato:', utenteLoggato, '| admin:', isAdmin);
})();
