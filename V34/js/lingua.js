// Traduzione lato client IT -> EN. Se non scelta da utente, presa da default del browser

'use strict';

(function () {
    console.group('[lingua] Inizializzazione traduzione');
    
    // Dizionario: chiave = testo italiano, valore = inglese
    const DIZIONARIO = {
        // Header / menu
        '\u2715 Chiudi': '\u2715 Close',
        'Tema: sistema': 'Theme: system',
        'Tema: chiaro': 'Theme: light',
        'Tema: scuro': 'Theme: dark',
        'Account': 'Account',
        'Amministratore': 'Administrator',
        'Utente': 'User',
        'Esci': 'Log out',
        'non loggato': 'not logged in',
        'Accedi': 'Log in',
        'Registrati': 'Sign up',
        // voci di navigazione
        'Home': 'Home',
        'Adozioni': 'Adoptions',
        'Volontariato': 'Volunteering',
        'Inserisci gatto': 'Add cat',
        'FAQ': 'FAQ',

        // Footer
        'Questo sito usa solo cookie tecnici di sessione, necessari al funzionamento. Nessuna profilazione di terze parti.':
            'This site only uses technical session cookies, required for it to work. No third-party profiling.',
        'Maggiori informazioni': 'More information',
        'Accetta': 'Accept',
        'Gestisci': 'Manage',
        'Collegamenti': 'Links',
        'Informativa privacy': 'Privacy policy',
        'Elimina i miei cookie': 'Delete my cookies',
        'Contatti': 'Contacts',
        'Gattile San Paolo \u00b7 Tutti i diritti riservati': 'Gattile San Paolo \u00b7 All rights reserved',

        // Home
        'Una casa, una famiglia, una seconda possibilit\u00e0': 'A home, a family, a second chance',
        'Ogni gatto che arriva al': 'Every cat that arrives at',
        'ha una storia. Alcuni sono stati abbandonati, altri recuperati dalla strada, altri ancora hanno semplicemente bisogno di una':
            'has a story. Some were abandoned, others rescued from the street, others simply need a',
        'nuova famiglia': 'new family',
        'Aiutaci a trasformare un incontro in un\'adozione e una visita in un nuovo inizio.':
            'Help us turn a meeting into an adoption and a visit into a new beginning.',
        'Perch\u00e9 adottare dal Gattile San Paolo?': 'Why adopt from Gattile San Paolo?',
        'Controlli veterinari': 'Veterinary checks',
        'Tutti i gatti vengono seguiti e monitorati prima dell\'adozione.':
            'All cats are followed and monitored before adoption.',
        'Supporto all\'adozione': 'Adoption support',
        'Ti aiutiamo a trovare il gatto pi\u00f9 adatto alla tua situazione.':
            'We help you find the cat best suited to your situation.',
        'Volontari qualificati': 'Qualified volunteers',
        'Ogni giorno persone dedicate si prendono cura dei nostri ospiti.':
            'Every day, dedicated people take care of our guests.',
        'Il nostro impatto': 'Our impact',
        'Caricamento statistiche in corso\u2026': 'Loading statistics\u2026',
        'Come funziona': 'How it works',
        'Sfoglia i gatti': 'Browse the cats',
        'disponibili nell\'area adozioni: puoi filtrare per nome, descrizione, et\u00e0 o colore del manto.':
            'available in the adoptions area: you can filter by name, description, age or coat colour.',
        'Registrati o accedi': 'Sign up or log in',
        'al tuo profilo per selezionare i gatti di cui vorresti sapere di pi\u00f9.':
            'to your profile to select the cats you would like to know more about.',
        'Prenota una visita': 'Book a visit',
        'conoscitiva direttamente dal sito, indicando la data e l\'ora che preferisci.':
            'directly from the site, choosing the date and time you prefer.',
        'In alternativa, puoi': 'Alternatively, you can',
        'diventare volontario': 'become a volunteer',
        'e scegliere le fasce orarie in cui prestare il tuo aiuto.':
            'and choose the time slots in which to offer your help.',
        'Diventa volontario': 'Become a volunteer',
        'Storie di successo': 'Success stories',
        '"Pensavamo di adottare un gatto. In realt\u00e0 abbiamo trovato un nuovo membro della famiglia."':
            '"We thought we were adopting a cat. We actually found a new member of the family."',
        '\u2014 Famiglia Rossi, Torino': '\u2014 Rossi family, Turin',
        '"Luna era timidissima quando \u00e8 arrivata. Dopo qualche settimana di pazienza ha iniziato a fidarsi: oggi dorme sul divano come se fosse sempre stata a casa nostra."':
            '"Luna was extremely shy when she arrived. After a few weeks of patience she began to trust us: today she sleeps on the sofa as if she had always lived with us."',
        '\u2014 Marco e Giulia, Moncalieri': '\u2014 Marco and Giulia, Moncalieri',
        '"Per me \u00e8 diventato come un figlio, non riuscirei pi\u00f9 a vivere senza Pippo insieme a me."':
            '"He has become like a son to me; I could no longer live without Pippo by my side."',
        '\u2014 Gabriele, Torino': '\u2014 Gabriele, Turin',
        'Nuovi arrivi': 'New arrivals',
        'Gli ultimi ospiti entrati nella struttura che aspettano una famiglia:':
            'The latest guests to enter the shelter, waiting for a family:',
        'Caricamento nuovi arrivi in corso\u2026': 'Loading new arrivals\u2026',
        'Non puoi adottare?': 'Can\u2019t adopt?',
        'Puoi comunque fare la differenza dedicando qualche ora del tuo tempo ai nostri ospiti.':
            'You can still make a difference by dedicating a few hours of your time to our guests.',
        'Ogni volontario contribuisce a migliorare la qualit\u00e0 della vita dei gatti accolti nella struttura.':
            'Every volunteer helps improve the quality of life of the cats hosted at the shelter.',
        'Scopri il volontariato': 'Discover volunteering',
        'Domande frequenti': 'Frequently asked questions',
        'Posso adottare anche se vivo in appartamento?': 'Can I adopt even if I live in a flat?',
        'Assolutamente s\u00ec. Molti dei nostri gatti sono nati in ambienti chiusi e si adattano perfettamente alla vita in appartamento, purch\u00e9 abbiano spazi per giocare e qualcuno che li ami.':
            'Absolutely. Many of our cats were born indoors and adapt perfectly to life in a flat, as long as they have space to play and someone to love them.',
        'Quanto costa adottare un gatto?': 'How much does it cost to adopt a cat?',
        'L\'adozione \u00e8 gratuita. Chiediamo solo la disponibilit\u00e0 a prendersi cura dell\'animale e a sostenere le spese veterinarie ordinarie.':
            'Adoption is free. We only ask for the willingness to care for the animal and to cover ordinary veterinary expenses.',
        'Come posso diventare volontario?': 'How can I become a volunteer?',
        'Registrati al sito': 'Sign up to the site',
        ', poi accedi alla pagina': ', then go to the page',
        'e scegli le fasce orarie in cui desideri prestare servizio. La struttura accoglie fino a due volontari per fascia.':
            'and choose the time slots in which you wish to volunteer. The shelter welcomes up to two volunteers per slot.',
        'Tutte le domande frequenti': 'All frequently asked questions',

        // Adozioni
        'I nostri ospiti felini': 'Our feline guests',
        'Seleziona i nostri gatti e prenota una visita conoscitiva.':
            'Select our cats and book a get-to-know-you visit.',
        'Per prenotare una visita devi prima': 'To book a visit you must first',
        'accedere': 'log in',
        'registrarti': 'sign up',
        '. Puoi comunque': '. You can still',
        'sfogliare e filtrare': 'browse and filter',
        'tutti i gatti disponibili.': 'all the available cats.',
        'Per prenotare una visita devi essere un utente, non un amministratore. Puoi comunque':
            'To book a visit you must be a user, not an administrator. You can still',
        'Caricamento schede gatti in corso\u2026': 'Loading cat profiles\u2026',
        'Prenota una visita conoscitiva': 'Book a get-to-know-you visit',
        'Nessun gatto selezionato. Clicca sulle card per sceglierli.':
            'No cat selected. Click the cards to choose them.',
        'Scegli data e ora della visita': 'Choose the date and time of the visit',
        'Giorno della visita': 'Day of the visit',
        'Scegli un giorno da oggi in poi.': 'Choose a day from today onwards.',
        'Orario della visita': 'Time of the visit',
        'Seleziona un orario\u2026': 'Select a time\u2026',
        'Le visite sono possibili dalle 9:00 alle 18:00.': 'Visits are possible from 9:00 to 18:00.',
        'Cancella': 'Clear',
        'Conferma prenotazione': 'Confirm booking',

        // Volontariato
        'Fai volontariato': 'Volunteer',
        'Il tuo aiuto fa la differenza. Scegli quante fasce orarie vuoi. La struttura accoglie al':
            'Your help makes the difference. Choose as many time slots as you like. The shelter welcomes at',
        'massimo 2 volontari per fascia oraria': 'most 2 volunteers per time slot',
        'Per prenotare un turno devi prima': 'To book a shift you must first',
        'Cosa fare da volontario': 'What you do as a volunteer',
        'Socializzare con i gatti e giocare con loro': 'Socialise with the cats and play with them',
        'Aiutare con la pulizia degli spazi': 'Help keep the spaces clean',
        'Supportare durante le visite dei potenziali adottanti':
            'Support during visits from potential adopters',
        'Assistere il personale nella gestione della struttura':
            'Assist the staff in running the shelter',
        'Non \u00e8 richiesta alcuna esperienza specifica. Per info:':
            'No specific experience is required. For info:',
        'Prenota i turni': 'Book the shifts',
        'Seleziona giorno e fasce orarie': 'Select day and time slots',
        'Le fasce con 2/2 volontari sono disabilitate automaticamente.':
            'Slots with 2/2 volunteers are automatically disabled.',
        'Giorno': 'Day',
        'Scegli prima un giorno: verranno mostrate solo le sue fasce orarie.':
            'Choose a day first: only its time slots will be shown.',
        'Caricamento fasce orarie\u2026': 'Loading time slots\u2026',
        'Conferma turni selezionati': 'Confirm selected shifts',

        // FAQ
        'Non trovi la risposta che cerchi?': 'Can\u2019t find the answer you\u2019re looking for?',
        'Scrivici': 'Write to us',
        'e ti risponderemo al pi\u00f9 presto.': 'and we\u2019ll get back to you as soon as possible.',
        'Adozioni': 'Adoptions',
        'Assolutamente s\u00ec. Molti dei nostri gatti sono nati o cresciuti in ambienti chiusi e si adattano perfettamente alla vita in appartamento, purch\u00e9 abbiano spazi per giocare e qualcuno che li ami.':
            'Absolutely. Many of our cats were born or raised indoors and adapt perfectly to life in a flat, as long as they have space to play and someone to love them.',
        'L\'adozione \u00e8 completamente gratuita. Chiediamo soltanto la disponibilit\u00e0 a prendersi cura dell\'animale, incluse le spese veterinarie ordinarie (vaccinazioni, antiparassitari, visite annuali).':
            'Adoption is completely free. We only ask for the willingness to care for the animal, including ordinary veterinary expenses (vaccinations, parasite treatments, annual check-ups).',
        'Devo avere un giardino per adottare?': 'Do I need a garden to adopt?',
        'No. Un appartamento ben organizzato con tiragraffi, ripiani e giochi \u00e8 pi\u00f9 che sufficiente per la maggior parte dei gatti. Alcune razze pi\u00f9 attive potrebbero beneficiare di spazi pi\u00f9 ampi, ma lo valutiamo insieme.':
            'No. A well-organised flat with scratching posts, shelves and toys is more than enough for most cats. Some more active breeds may benefit from larger spaces, but we assess that together.',
        '\u00c8 possibile adottare un gatto anziano?': 'Is it possible to adopt an elderly cat?',
        'S\u00ec, e lo consigliamo! I gatti adulti e anziani sono spesso meno adottati, ma hanno gi\u00e0 un carattere definito, sono solitamente pi\u00f9 tranquilli e richiedono meno supervisione. Ci sono tanti benefici nell\'accogliere un gatto senior.':
            'Yes, and we recommend it! Adult and elderly cats are often adopted less, but they already have a defined character, are usually calmer and need less supervision. There are many benefits to welcoming a senior cat.',
        'Come avviene il processo di adozione?': 'How does the adoption process work?',
        'al sito (se non l\'hai gi\u00e0 fatto).': 'to the site (if you haven\u2019t already).',
        'Sfoglia i gatti disponibili nella sezione': 'Browse the available cats in the section',
        'Seleziona i gatti che ti interessano e prenota una visita conoscitiva.':
            'Select the cats you\u2019re interested in and book a get-to-know-you visit.',
        'Vieni in struttura per conoscerli di persona.': 'Come to the shelter to meet them in person.',
        'Se \u00e8 amore, completeremo insieme le pratiche di affidamento.':
            'If it\u2019s love, we\u2019ll complete the adoption paperwork together.',
        'Volontariato': 'Volunteering',
        'al sito, poi accedi alla pagina': 'to the site, then go to the page',
        'e scegli le fasce orarie in cui desideri prestare servizio. La struttura accoglie al massimo due volontari per fascia oraria.':
            'and choose the time slots in which you wish to volunteer. The shelter welcomes at most two volunteers per time slot.',
        'Quante ore devo impegnarmi?': 'How many hours do I have to commit?',
        'Non esiste un minimo obbligatorio. Puoi prenotare una sola fascia oraria o quante ne vuoi. L\'importante \u00e8 presentarsi puntuale e avvisare in caso di imprevisti.':
            'There is no required minimum. You can book a single time slot or as many as you like. What matters is showing up on time and letting us know if something comes up.',
        'Serve esperienza con i gatti?': 'Do I need experience with cats?',
        'No, nessuna esperienza \u00e8 richiesta. Ti verr\u00e0 spiegato come approcciarsi agli animali, come pulire gli spazi in sicurezza e come supportare il personale durante le visite. Solo buona volont\u00e0 e amore per i felini!':
            'No, no experience is required. You\u2019ll be shown how to approach the animals, how to clean the spaces safely and how to support the staff during visits. Just goodwill and a love for cats!',
        'Posso portare i miei figli a fare volontariato?': 'Can I bring my children to volunteer?',
        'I minorenni possono partecipare se accompagnati da un genitore o tutore legale registrato al sito. Contattateci preventivamente per organizzare la visita in modo adeguato.':
            'Minors may take part if accompanied by a parent or legal guardian registered on the site. Please contact us in advance so we can organise the visit properly.',
        'La struttura': 'The shelter',
        'Dove si trova il gattile?': 'Where is the cattery?',
        'Via San Paolo 1, 10100 Torino (TO). Siamo raggiungibili con i mezzi pubblici: linea 15 (fermata Felina) o linea 52 (fermata Parco Verde).':
            'Via San Paolo 1, 10100 Turin (TO). We are reachable by public transport: line 15 (Felina stop) or line 52 (Parco Verde stop).',
        'Quali sono gli orari di apertura?': 'What are the opening hours?',
        'Il gattile \u00e8 aperto dal luned\u00ec alla domenica, dalle 09:00 alle 18:00 con orario continuato.':
            'The cattery is open from Monday to Sunday, from 09:00 to 18:00 without a break.',
        'Come posso contattarvi?': 'How can I contact you?',
        '(lun-sab, 9:00-18:00)': '(Mon-Sat, 9:00-18:00)',

        // Privacy
        'Privacy & Cookie': 'Privacy & Cookies',
        'Trasparenza totale': 'Full transparency',
        ': usiamo solo cookie tecnici, nessuna profilazione.':
            ': we only use technical cookies, no profiling.',
        'Informazioni privacy': 'Privacy information',
        'In questa pagina': 'On this page',
        'Titolare': 'Data controller',
        'Cookie usati': 'Cookies used',
        'Dati raccolti': 'Data collected',
        'I tuoi diritti': 'Your rights',
        'Elimina i cookie': 'Delete cookies',
        'Privacy': 'Privacy',
        'Titolare del trattamento': 'Data controller',
        'Cookie utilizzati': 'Cookies used',
        'Questo sito usa': 'This site uses',
        'esclusivamente cookie tecnici': 'technical cookies only',
        'necessari al funzionamento. Nessuna profilazione, nessun cookie di terze parti.':
            'required for it to work. No profiling, no third-party cookies.',
        'Tipo': 'Type',
        'Sessione (tecnico)': 'Session (technical)',
        'Durata': 'Duration',
        'Chiusura del browser': 'Browser close',
        'Finalit\u00e0': 'Purpose',
        'Gestione della sessione autenticata': 'Management of the authenticated session',
        'Info': 'Info',
        'Salvato automaticamente, senza richiesta di consenso':
            'Saved automatically, without a consent request',
        'Persistente (tecnico)': 'Persistent (technical)',
        '72 ore': '72 hours',
        'Contiene solo un token opaco per precompilare lo username al login. Il token \u00e8 associato all\'utente lato server in un file dedicato. Nessuna credenziale in chiaro.':
            'It only contains an opaque token to pre-fill the username at login. The token is linked to the user on the server in a dedicated file. No credentials in clear text.',
        '1 anno': '1 year',
        'Memorizza la lettura dell\'informativa cookie': 'Stores that the cookie notice has been read',
        'La preferenza del tema chiaro/scuro': 'The light/dark theme preference',
        'non': 'does not',
        'usa cookie: \u00e8 salvata nel': 'use cookies: it is saved in the browser\u2019s',
        'localStorage': 'localStorage',
        'del browser (chiave': '(key',
        'tema': 'theme',
        '). Trattandosi di una semplice impostazione tecnica di interfaccia, \u00e8 disponibile anche senza accettare i cookie e non viene trasmessa al server.':
            '). Being a simple technical interface setting, it is available even without accepting cookies and is never sent to the server.',
        'La preferenza di lingua (italiano/inglese)': 'The language preference (Italian/English)',
        'lingua': 'language',
        '). Senza una scelta manuale viene usata la lingua preferita del browser. Anche questa impostazione \u00e8 solo tecnica e non viene trasmessa al server.':
            '). Without a manual choice the browser\u2019s preferred language is used. This setting is technical only too and is never sent to the server.',
        'Nessun cookie di profilazione o di terze parti \u00e8 presente sul sito.':
            'No profiling or third-party cookies are present on the site.',
        'Dati personali raccolti': 'Personal data collected',
        'In fase di registrazione raccogliamo nome, cognome, indirizzo e credenziali di accesso. Questi dati servono solo a gestire il tuo profilo, l\'autenticazione e le prenotazioni di visite e turni di volontariato.':
            'During registration we collect first name, surname, address and login credentials. This data is used only to manage your profile, authentication and the booking of visits and volunteering shifts.',
        'Conserviamo i dati per il tempo necessario a fornire il servizio e non li cediamo a terzi, ne\' li usiamo per profilazione o marketing. Puoi chiederne in qualsiasi momento l\'accesso, la rettifica o la cancellazione (vedi':
            'We keep the data for as long as needed to provide the service and we do not pass it to third parties, nor do we use it for profiling or marketing. You can request access, correction or deletion at any time (see',
        'Puoi richiedere l\'accesso, la rettifica o la cancellazione dei tuoi dati e del tuo account scrivendo a':
            'You can request access, correction or deletion of your data and your account by writing to',
        'Elimina i tuoi cookie': 'Delete your cookies',
        'Rimuovi tutti i cookie e la sessione impostati da questo sito. Verrai disconnesso.':
            'Remove all cookies and the session set by this site. You will be logged out.',
        'Elimina tutti i miei cookie': 'Delete all my cookies',
        'Verrai reindirizzato a questa pagina con conferma dell\'avvenuta eliminazione.':
            'You will be redirected to this page with confirmation that the deletion was completed.',

        // Login
        'Accedi al tuo profilo': 'Log in to your profile',
        'Non hai ancora un account?': 'Don\u2019t have an account yet?',
        'Registrati gratuitamente': 'Sign up for free',
        'Credenziali di accesso': 'Login credentials',
        'Username': 'Username',
        'Password': 'Password',
        'Ricordami': 'Remember me',
        'Per usare': 'To use',
        '\u201cRicordami\u201d': '\u201cRemember me\u201d',
        'devi prima': 'you must first',
        'accettare i cookie': 'accept the cookies',
        'dal banner in basso.': 'from the banner below.',
        'Ricordami su questo browser per 72 ore': 'Remember me on this browser for 72 hours',
        'Il tuo username verr\u00e0 precompilato al prossimo accesso. La password non viene mai memorizzata.':
            'Your username will be pre-filled at the next login. The password is never stored.',
        'Campi obbligatori': 'Required fields',

        // Registrazione
        'Crea il tuo profilo': 'Create your profile',
        'Gi\u00e0 registrato?': 'Already registered?',
        'Accedi qui': 'Log in here',
        'Inserisci i tuoi dati': 'Enter your details',
        'Vai al login': 'Go to login',
        'Dati anagrafici': 'Personal details',
        'Nome': 'Name',
        'Solo lettere; almeno 2 caratteri.': 'Letters only; at least 2 characters.',
        'Cognome': 'Surname',
        'Indirizzo': 'Address',
        'Almeno 5 caratteri.': 'At least 5 characters.',
        'Inizia con una lettera; solo lettere, numeri e underscore; 3-50 caratteri.':
            'Start with a letter; letters, numbers and underscores only; 3-50 characters.',
        '8-16 caratteri: almeno una maiuscola, una minuscola, un numero e un carattere speciale.':
            '8-16 characters: at least one uppercase, one lowercase, one number and one special character.',
        'Forza della password': 'Password strength',
        'Conferma password': 'Confirm password',
        'Consenso privacy': 'Privacy consent',
        'Dichiaro di aver letto l\'': 'I declare that I have read the',
        'Informativa Privacy': 'Privacy Policy',
        'e acconsento al trattamento dei miei dati personali ai sensi del Regolamento (UE) 2016/679 (GDPR)':
            'and I consent to the processing of my personal data under Regulation (EU) 2016/679 (GDPR)',
        'Completamento modulo': 'Form completion',
        'Compila tutti i campi per procedere.': 'Fill in all the fields to proceed.',
        'Crea profilo': 'Create profile',

        // Inserisci gatto
        'Inserisci un nuovo ospite': 'Add a new guest',
        'Area amministrativa.': 'Administrative area.',
        'Il sistema assegna automaticamente un\'immagine': 'The system automatically assigns a',
        'placeholder': 'placeholder',
        '. Le foto reali saranno disponibili in una futura versione.':
            'image. Real photos will be available in a future version.',
        'Vedi tutti i gatti': 'See all the cats',
        'Aggiungi un altro gatto': 'Add another cat',
        'Identit\u00e0 del gatto': 'Cat identity',
        'Max 50 caratteri.': 'Max 50 characters.',
        'Razza': 'Breed',
        'Max 50 caratteri. Se sconosciuta, indica \u00abMeticcio\u00bb.':
            'Max 50 characters. If unknown, write \u00abMixed\u00bb.',
        'Sesso': 'Sex',
        '\u2014 Seleziona \u2014': '\u2014 Select \u2014',
        'Maschio': 'Male',
        'Femmina': 'Female',
        'Et\u00e0 (mesi)': 'Age (months)',
        'Et\u00e0 espressa in mesi: numero intero tra 0 e 300 (es. 24 = 2 anni).':
            'Age in months: whole number between 0 and 300 (e.g. 24 = 2 years).',
        'Caratteristiche fisiche': 'Physical characteristics',
        'Peso (kg)': 'Weight (kg)',
        'Peso in chilogrammi: valore tra 0.1 e 20 kg (decimali con il punto, es. 4.20).':
            'Weight in kilograms: value between 0.1 and 20 kg (decimals with a dot, e.g. 4.20).',
        'Colore del mantello': 'Coat colour',
        'Max 30 caratteri.': 'Max 30 characters.',
        'Lunghezza del pelo': 'Coat length',
        'Corto': 'Short',
        'Medio': 'Medium',
        'Lungo': 'Long',
        'Scegli tra pelo corto, medio o lungo.': 'Choose between short, medium or long coat.',
        'Colore degli occhi': 'Eye colour',
        'Arrivo in struttura': 'Arrival at the shelter',
        'Data di arrivo': 'Arrival date',
        'Giorno in cui il gatto \u00e8 arrivato in struttura. Non pu\u00f2 essere una data futura.':
            'Day the cat arrived at the shelter. It cannot be a future date.',
        'Descrizione carattere e storia': 'Description of character and story',
        'Almeno 10 caratteri. Rimanenti:': 'At least 10 characters. Remaining:',
        'Salva scheda gatto': 'Save cat profile',

        // React
        'Caricamento schede gatti in corso\u2026 ': 'Loading cat profiles\u2026 ',
        'Lista gatti disponibili': 'List of available cats',
        'Filtra e ordina i gatti': 'Filter and sort the cats',
        'Cerca per nome o descrizione': 'Search by name or description',
        'Es. giocoso, bianco\u2026': 'E.g. playful, white\u2026',
        'Ordina per': 'Sort by',
        'Data arrivo (pi\u00f9 recente)': 'Arrival date (most recent)',
        'Data arrivo (meno recente)': 'Arrival date (least recent)',
        'Et\u00e0 (pi\u00f9 giovane)': 'Age (youngest)',
        'Et\u00e0 (pi\u00f9 vecchio)': 'Age (oldest)',
        'Colore mantello (A-Z)': 'Coat colour (A-Z)',
        'Clicca su una card per selezionare il gatto.': 'Click a card to select the cat.',
        'gatto selezionato': 'cat selected',
        'gatti selezionati': 'cats selected',
        'Nessun gatto corrisponde alla ricerca \u00ab': 'No cat matches the search \u00ab',
        'Elenco gatti disponibili': 'List of available cats',
        'Impossibile caricare i gatti, riprova tra qualche minuto.':
            'Unable to load the cats, please try again in a few minutes.',
        '\u2014 selezionato': '\u2014 selected',
        '\u2014 clicca per selezionare': '\u2014 click to select',
        'Informazioni sul gatto': 'Cat information',
        'Caratteristiche principali': 'Main characteristics',
        'Nuovo': 'New',
        'Peso': 'Weight',
        'Occhi': 'Eyes',
        'Arrivato il': 'Arrived on',
        'Adotta': 'Adopt',

        // Card gatti (home.js)
        'Gatti ospitati': 'Cats hosted',
        'Incontri organizzati': 'Meetings organised',
        'Volontari attivi': 'Active volunteers',
        'Nuovi arrivi quest\u0027anno': 'New arrivals this year',
        'Nuovi arrivi quest\u2019anno': 'New arrivals this year',
        'Pelo corto': 'Short hair',
        'Pelo medio': 'Medium hair',
        'Pelo lungo': 'Long hair',
        'Nessun gatto registrato al momento. Torna presto!':
            'No cat registered at the moment. Come back soon!',
        'Statistiche non disponibili al momento. Riprova tra qualche minuto.':
            'Statistics not available at the moment. Please try again in a few minutes.',
        'Impossibile caricare i nuovi arrivi. Riprova tra qualche minuto.':
            'Unable to load the new arrivals. Please try again in a few minutes.',

        // Validazioni registrazione.js / login.js
        'Molto debole': 'Very weak',
        'Debole': 'Weak',
        'Sufficiente': 'Fair',
        'Buona': 'Good',
        'Ottima': 'Excellent',
        'Il nome \u00e8 obbligatorio.': 'First name is required.',
        'Il nome deve contenere almeno 2 caratteri.': 'First name must be at least 2 characters.',
        'Il nome contiene caratteri non validi.': 'First name contains invalid characters.',
        'Il cognome \u00e8 obbligatorio.': 'Surname is required.',
        'Il cognome deve contenere almeno 2 caratteri.': 'Surname must be at least 2 characters.',
        'Il cognome contiene caratteri non validi.': 'Surname contains invalid characters.',
        'L\'indirizzo \u00e8 obbligatorio.': 'Address is required.',
        'L\'indirizzo deve contenere almeno 5 caratteri.': 'Address must be at least 5 characters.',
        'Lo username \u00e8 obbligatorio.': 'Username is required.',
        'Lo username deve contenere almeno 3 caratteri.': 'Username must be at least 3 characters.',
        'Lo username non pu\u00f2 superare i 50 caratteri.': 'Username cannot exceed 50 characters.',
        'Lo username deve iniziare con una lettera.': 'Username must start with a letter.',
        'Sono consentiti solo lettere, numeri e underscore (_).':
            'Only letters, numbers and underscores (_) are allowed.',
        'La password \u00e8 obbligatoria.': 'Password is required.',
        'La password deve contenere almeno 8 caratteri.': 'Password must be at least 8 characters.',
        'La password non pu\u00f2 superare i 16 caratteri.': 'Password cannot exceed 16 characters.',
        'Manca almeno una lettera maiuscola.': 'At least one uppercase letter is missing.',
        'Manca almeno una lettera minuscola.': 'At least one lowercase letter is missing.',
        'Manca almeno un numero.': 'At least one number is missing.',
        'Manca almeno un carattere speciale.': 'At least one special character is missing.',
        'Confermare la password.': 'Please confirm the password.',
        'Le due password non coincidono.': 'The two passwords do not match.',
        'Devi accettare l\'Informativa Privacy per procedere.':
            'You must accept the Privacy Policy to proceed.',
        'Inserire l\'username.': 'Please enter the username.',
        'Inserire la password.': 'Please enter the password.',

        // prenotazione.js
        'Scegli il giorno della visita.': 'Choose the day of the visit.',
        'Formato data non valido.': 'Invalid date format.',
        'La data non pu\u00f2 essere nel passato.': 'The date cannot be in the past.',
        'Scegli l\u2019orario della visita.': 'Choose the time of the visit.',
        'La data e ora devono essere future.': 'The date and time must be in the future.',
        'Seleziona almeno un gatto prima di prenotare.': 'Select at least one cat before booking.',
        'Invio in corso\u2026': 'Sending\u2026',
        'Prenota un\'altra visita': 'Book another visit',
        'Torna alla home': 'Back to home',
        'Errore di rete durante la prenotazione. Controlla la connessione e riprova.':
            'Network error during booking. Check your connection and try again.',

        // volontariato.js
        'Scegli un giorno per visualizzare le fasce orarie.':
            'Choose a day to see the time slots.',
        'Nessuna fascia disponibile prima di questa data.':
            'No slot available before this date.',
        'Nessuna fascia disponibile dopo questa data.':
            'No slot available after this date.',
        'Impossibile caricare le fasce. Riprova tra qualche minuto.':
            'Unable to load the slots. Please try again in a few minutes.',
        'Impossibile caricare i turni. Riprova tra qualche minuto.':
            'Unable to load the shifts. Please try again in a few minutes.',
        'Nessuna fascia oraria disponibile nel prossimo periodo. Riprova tra qualche giorno.':
            'No time slot available in the coming period. Try again in a few days.',
        'Nessuna fascia disponibile per il giorno scelto. Prova un altro giorno.':
            'No slot available for the chosen day. Try another day.',
        'Nessuna fascia disponibile per il giorno scelto.':
            'No slot available for the chosen day.',
        'Caricamento fasce orarie in corso\u2026': 'Loading time slots\u2026',
        '\u2014 sei gi\u00e0 iscritto': '\u2014 you are already signed up',
        '\u2014 pieno': '\u2014 full',
        '\u2014 disponibile': '\u2014 available',
        'Seleziona almeno una fascia oraria.': 'Select at least one time slot.',
        'Prenota altri turni': 'Book more shifts',
        'Dettaglio:': 'Detail:',
        'Avvisi:': 'Warnings:',
        'Errore di rete. Riprova tra qualche minuto.':
            'Network error. Please try again in a few minutes.',

        // inserisci_gatto.js
        'Il nome del gatto \u00e8 obbligatorio.': 'The cat\u2019s name is required.',
        'Il nome deve contenere almeno 1 carattere.': 'The name must be at least 1 character.',
        'Il nome non pu\u00f2 superare i 50 caratteri.': 'The name cannot exceed 50 characters.',
        'La razza \u00e8 obbligatoria.': 'The breed is required.',
        'La razza deve contenere almeno 1 carattere.': 'The breed must be at least 1 character.',
        'La razza non pu\u00f2 superare i 50 caratteri.': 'The breed cannot exceed 50 characters.',
        'Selezionare il sesso.': 'Please select the sex.',
        'Valore del sesso non valido.': 'Invalid sex value.',
        'L\'et\u00e0 \u00e8 obbligatoria.': 'Age is required.',
        'L\'et\u00e0 deve essere un numero.': 'Age must be a number.',
        'L\'et\u00e0 non pu\u00f2 essere negativa.': 'Age cannot be negative.',
        'L\'et\u00e0 non pu\u00f2 superare 300 mesi.': 'Age cannot exceed 300 months.',
        'Il peso \u00e8 obbligatorio.': 'Weight is required.',
        'Il peso deve essere un numero.': 'Weight must be a number.',
        'Il peso deve essere almeno 0.1 kg.': 'Weight must be at least 0.1 kg.',
        'Il peso non pu\u00f2 superare 20 kg.': 'Weight cannot exceed 20 kg.',
        'Inserire il colore del mantello.': 'Please enter the coat colour.',
        'Selezionare la lunghezza del pelo.': 'Please select the coat length.',
        'Inserire il colore degli occhi.': 'Please enter the eye colour.',
        'La data di arrivo \u00e8 obbligatoria.': 'The arrival date is required.',
        'La data non pu\u00f2 essere futura.': 'The date cannot be in the future.',
        'La descrizione \u00e8 obbligatoria.': 'The description is required.',
        'La descrizione deve contenere almeno 10 caratteri.':
            'The description must be at least 10 characters.',
        'La descrizione supera il limite consentito.': 'The description exceeds the allowed limit.',

        // Attributi (aria-label, alt, title, placeholder)
        'Apri menu di navigazione': 'Open navigation menu',
        'Chiudi menu di navigazione': 'Close navigation menu',
        'Avviso accesso': 'Login notice',
        'Avviso prenotazione': 'Booking notice',
        'Cambia tema: Sistema / Chiaro / Scuro': 'Change theme: System / Light / Dark',
        'Completamento modulo di registrazione': 'Registration form completion',
        'Contenuto informativa': 'Notice content',
        'Elenco gatti con filtri e ordinamento': 'List of cats with filters and sorting',
        'Es. Europeo, Persiano': 'E.g. European, Persian',
        'Es. Fuffi': 'E.g. Fluffy',
        'Es. Tigrato, Bianco': 'E.g. Tabby, White',
        'Es. Verdi, Azzurri': 'E.g. Green, Blue',
        'FAQ \u2014 Domande frequenti': 'FAQ \u2014 Frequently asked questions',
        'Fasce orarie disponibili': 'Available time slots',
        'Forza password: 0 debole, 4 ottima': 'Password strength: 0 weak, 4 excellent',
        'Gatti selezionati per la visita': 'Cats selected for the visit',
        'Gestione consenso cookie': 'Cookie consent management',
        'Immagine di segnaposto': 'Placeholder image',
        'Indice della pagina': 'Page index',
        'Informativa cookie': 'Cookie notice',
        'Logo Gattile San Paolo': 'Gattile San Paolo logo',
        'Modulo di accesso': 'Login form',
        'Modulo di registrazione': 'Registration form',
        'Modulo inserimento nuovo gatto': 'New cat entry form',
        'Modulo prenotazione turni volontariato': 'Volunteering shift booking form',
        'Modulo prenotazione visita': 'Visit booking form',
        'Navigazione footer': 'Footer navigation',
        'Navigazione principale': 'Main navigation',
        'Privacy e gestione dati': 'Privacy and data management',
        'Racconta la personalit\u00e0 del gatto\u2026': 'Tell the cat\u2019s personality\u2026',
        'Stato autenticazione': 'Authentication status',
        'Torna alla Home Page': 'Back to the Home Page',
        'Via/Corso, numero, citt\u00e0': 'Street, number, city'
    };

    //testi costruiti dinamicamente con numeri o variabili.
    const REGOLE = [
        // Età in parole (funzioni_comuni.js)
        [/^(\d+) mesi$/, '$1 months'],
        [/^(\d+) mese$/, '$1 month'],
        [/^(\d+) anni$/, '$1 years'],
        [/^(\d+) anno$/, '$1 year'],
        [/^(\d+) anni e (\d+) mesi$/, '$1 years and $2 months'],
        [/^(\d+) anni e (\d+) mese$/, '$1 years and $2 month'],
        [/^(\d+) anno e (\d+) mesi$/, '$1 year and $2 months'],
        [/^(\d+) anno e (\d+) mese$/, '$1 year and $2 month'],
        // Conteggi lista gatti (GattiReact.js)
        [/^(\d+) gatti disponibili\.$/, '$1 cats available.'],
        [/^(\d+) gatti trovati su (\d+)\.$/, '$1 cats found out of $2.'],
        [/^(\d+) selezionati\.$/, '$1 selected.'],
        // Aria/title turni (volontariato.js)
        [/^Volontari iscritti: (\d+)$/, 'Volunteers signed up: $1'],
        [/^(\d+) volontari$/, '$1 volunteers'],
        // Aria card adozione (home.js / GattiReact.js)
        [/^Vai alla pagina adozioni per (.+)$/, 'Go to the adoptions page for $1'],
        [/^Placeholder di (.+)$/, 'Placeholder for $1'],
        // Tema (tema.js)
        [/^Cambia tema \(attuale: sistema\)$/, 'Change theme (current: system)'],
        [/^Cambia tema \(attuale: chiaro\)$/, 'Change theme (current: light)'],
        [/^Cambia tema \(attuale: scuro\)$/, 'Change theme (current: dark)']
    ];

    // Attributi da tradurre oltre al testo.
    const ATTRIBUTI = ['placeholder', 'title', 'alt', 'aria-label'];

    // Stato lingua

    function linguaSalvata() {
        if (typeof Storage !== 'undefined' && window.localStorage) {
            const v = localStorage.getItem('lingua');
            return (v === 'it' || v === 'en') ? v : null;
        }
        return null;
    }

    function linguaBrowser() {
        const l = (navigator.language || (navigator.languages && navigator.languages[0]) || 'it');
        return l.toLowerCase().indexOf('en') === 0 ? 'en' : 'it';
    }

    const LINGUA = linguaSalvata() || linguaBrowser();

    // Traduzione

    function normalizza(testo) {
        return testo
            .replace(/\u00a0/g, ' ')
            .replace(/[\u2018\u2019]/g, "'")
            .replace(/\s+/g, ' ')
            .trim();
    }

    // Traduce una stringa intera
    function traduciStringa(norm) {
        if (Object.prototype.hasOwnProperty.call(DIZIONARIO, norm)) {
            return DIZIONARIO[norm];
        }
        for (let i = 0; i < REGOLE.length; i++) {
            const r = REGOLE[i];
            if (r[0].test(norm)) {
                return norm.replace(r[0], r[1]);
            }
        }
        return null;
    }

    // Traduce un nodo di testo
    function traduciNodoTesto(nodo) {
        const grezzo = nodo.nodeValue;
        if (!grezzo || !/[A-Za-z\u00C0-\u017F]/.test(grezzo)) {
            return;
        }
        const norm = normalizza(grezzo);
        if (!norm) {
            return;
        }
        const tradotto = traduciStringa(norm);
        if (tradotto === null || tradotto === norm) {
            return;
        }
        const pre = grezzo.match(/^\s*/)[0];
        const post = grezzo.match(/\s*$/)[0];
        nodo.nodeValue = pre + tradotto + post;
    }

    function traduciAttributi(elemento) {
        for (let i = 0; i < ATTRIBUTI.length; i++) {
            const nome = ATTRIBUTI[i];
            if (!elemento.hasAttribute || !elemento.hasAttribute(nome)) {
                continue;
            }
            const val = elemento.getAttribute(nome);
            if (!val) {
                continue;
            }
            const norm = normalizza(val);
            const tradotto = traduciStringa(norm);
            if (tradotto !== null && tradotto !== norm) {
                elemento.setAttribute(nome, tradotto);
            }
        }
    }

    const TAG_SALTATI = { SCRIPT: true, STYLE: true, NOSCRIPT: true };

    // traduce testo e attributi.
    function traduciAlbero(radice) {
        if (radice.nodeType === Node.TEXT_NODE) {
            const p = radice.parentNode;
            if (!p || !TAG_SALTATI[p.nodeName]) {
                traduciNodoTesto(radice);
            }
            return;
        }
        if (radice.nodeType !== Node.ELEMENT_NODE) {
            return;
        }

        if (TAG_SALTATI[radice.nodeName]) {
            return;
        }

        traduciAttributi(radice);

        const camminatore = document.createTreeWalker(
            radice, NodeFilter.SHOW_TEXT, {
            acceptNode: function (n) {
                return TAG_SALTATI[n.parentNode && n.parentNode.nodeName]
                    ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
            }
        }
        );
        let n;
        while ((n = camminatore.nextNode())) {
            traduciNodoTesto(n);
        }
        // Attributi degli elementi discendenti
        const elementi = radice.querySelectorAll('[placeholder],[title],[alt],[aria-label]');
        for (let i = 0; i < elementi.length; i++) {
            traduciAttributi(elementi[i]);
        }
    }

    // Pulsante di scelta manuale

    function aggiornaPulsante() {
        const bottone = document.getElementById('toggle-lingua');
        if (!bottone) return;
        const testo = bottone.querySelector('.testo-lingua');
        const corrente = LINGUA === 'en' ? 'English' : 'Italiano';
        const prossima = LINGUA === 'en' ? 'Italiano' : 'English';
        if (testo) testo.textContent = corrente;
        bottone.setAttribute('lang', LINGUA);
        bottone.setAttribute('aria-label',
            LINGUA === 'en'
                ? 'Change language (current: English)'
                : 'Cambia lingua (attuale: Italiano)');
        bottone.setAttribute('title',
            LINGUA === 'en'
                ? 'Switch to ' + prossima
                : 'Passa a ' + prossima);
    }

    function collegaPulsante() {
        const bottone = document.getElementById('toggle-lingua');
        if (!bottone) return;
        bottone.addEventListener('click', function () {
            const nuova = LINGUA === 'en' ? 'it' : 'en';
            if (typeof Storage !== 'undefined' && window.localStorage) {
                localStorage.setItem('lingua', nuova);
            } location.reload();
        });
    }

    // Avvio

    function avvia() {
        console.info('[lingua] Lingua attiva:', LINGUA);
        aggiornaPulsante();
        collegaPulsante();

        // italiano: nessuna traduzione
        if (LINGUA !== 'en') {
            console.info('[lingua] Italiano selezionato, nessuna traduzione');
            console.log('✓ Lingua inizializzata');
            console.groupEnd();
            return;
        }

        console.info('[lingua] English selezionato, inizio traduzione');
        document.documentElement.lang = 'en';
        traduciAlbero(document.body);
        console.info('[lingua] DOM principale tradotto');

        // Contenuti dinamici (React, fetch, tema)
        // MutationObserver si attiva quando viene modificato il DOM
        const osservatore = new MutationObserver(function (mutazioni) {
            for (let i = 0; i < mutazioni.length; i++) {
                const m = mutazioni[i];
                if (m.type === 'childList') {
                    for (let j = 0; j < m.addedNodes.length; j++) {
                        traduciAlbero(m.addedNodes[j]);
                    }
                } else if (m.type === 'characterData') {
                    if (m.target.parentNode && !TAG_SALTATI[m.target.parentNode.nodeName]) {
                        traduciNodoTesto(m.target);
                    }
                } else if (m.type === 'attributes' && m.target.nodeType === Node.ELEMENT_NODE) {
                    traduciAttributi(m.target);
                }
            }
        });
        osservatore.observe(document.body, {
            subtree: true,
            childList: true,
            characterData: true,
            attributes: true,
            attributeFilter: ATTRIBUTI
        });
        console.info('[lingua] MutationObserver attivato per contenuti dinamici');
        console.log('✓ Lingua inizializzata');
        console.groupEnd();
    }

    if (document.readyState === 'loading') {
        console.info('[lingua] Attesa DOMContentLoaded');
        document.addEventListener('DOMContentLoaded', avvia);
    } else {
        avvia();
    }
})();
