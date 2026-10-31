<?php
require_once __DIR__.'/request.inc.php';
require_once __DIR__ . '/search-request.inc.php';

function u5SearchLanguage() {
    $language = isset($_GET['l']) && is_string($_GET['l']) ? strtolower(($_GET['l'] ?? '')) : '';
    if ($language === '') $language = strtolower((string) ($GLOBALS['lan1na'] ?? 'en'));
    return in_array($language, array('de', 'fr', 'it', 'rm', 'en'), true) ? $language : 'en';
}

function u5SearchLanguageSlot() {
    $requested = isset($_GET['l']) && is_string($_GET['l']) ? $_GET['l'] : '';
    for ($slot = 1; $slot <= 5; $slot++) {
        if (isset($GLOBALS['lan' . $slot . 'na']) && $requested === (string) $GLOBALS['lan' . $slot . 'na']) return $slot;
    }
    return 1;
}

function u5SearchLabel($key) {
    /* Keep the installation's existing editable search/button translations. */
    $legacyPrefix = $key === 'search' ? 'recherche_' : ($key === 'enter' ? 'term_' : null);
    if ($legacyPrefix !== null) {
        $configured = (string) ($GLOBALS[$legacyPrefix . u5SearchLanguageSlot()] ?? '');
        if (trim($configured) === '') {
            for ($slot = 1; $slot <= 5; $slot++) {
                $configured = (string) ($GLOBALS[$legacyPrefix . $slot] ?? '');
                if (trim($configured) !== '') break;
            }
        }
        if (trim($configured) !== '') return u5SearchFromLegacy($configured);
    }
    static $labels = null;
    if ($labels === null) $labels = json_decode(<<<'U5SEARCHLABELS'
{"de":{"search":"Suchen","help":"Mehrere W\u00f6rter suchen nach allen Begriffen. \"Wortgruppen\" bleiben zusammen.","enter":"Bitte geben Sie einen Suchbegriff ein.","results":"Treffer f\u00fcr","no_results":"Keine Treffer f\u00fcr","partial":"Treffer mit einem Teil der Suchbegriffe","partial_help":"Keine Seite enth\u00e4lt alle Suchbegriffe. Diese Seiten enthalten einen Teil davon.","suggestions":"Meinten Sie:","without_quotes":"Ohne Anf\u00fchrungszeichen suchen","protected":"F\u00fcr diesen Textauszug ist eine Anmeldung erforderlich.","title_only":"Fundstelle im Seitentitel:","previous":"Zur\u00fcck","next":"Weiter","page":"Seite","of":"von","unavailable":"Die Suche ist zurzeit nicht verf\u00fcgbar. Bitte versuchen Sie es sp\u00e4ter erneut.","too_long":"Die Suchanfrage darf h\u00f6chstens 512 Zeichen enthalten.","too_many_terms":"Bitte verwenden Sie h\u00f6chstens 16 Suchbegriffe oder Wortgruppen.","unclosed_quote":"Bitte schliessen Sie die Anf\u00fchrungszeichen.","empty_phrase":"Bitte geben Sie zwischen den Anf\u00fchrungszeichen eine Wortgruppe ein.","invalid_control":"Bitte entfernen Sie die nicht druckbaren Zeichen aus der Suchanfrage.","results_label":"Suchergebnisse","matched_terms":"Suchbegriffe gefunden"},"fr":{"search":"Rechercher","help":"Plusieurs mots recherchent tous les termes. Les \"groupes de mots\" restent ensemble.","enter":"Veuillez saisir un terme de recherche.","results":"r\u00e9sultats pour","no_results":"Aucun r\u00e9sultat pour","partial":"R\u00e9sultats contenant une partie des termes","partial_help":"Aucune page ne contient tous les termes. Ces pages en contiennent une partie.","suggestions":"Vouliez-vous dire :","without_quotes":"Rechercher sans guillemets","protected":"Une connexion est n\u00e9cessaire pour afficher cet extrait.","title_only":"Correspondance dans le titre :","previous":"Pr\u00e9c\u00e9dent","next":"Suivant","page":"Page","of":"sur","unavailable":"La recherche est momentan\u00e9ment indisponible. Veuillez r\u00e9essayer plus tard.","too_long":"La recherche peut contenir au maximum 512 caract\u00e8res.","too_many_terms":"Veuillez utiliser au maximum 16 termes ou groupes de mots.","unclosed_quote":"Veuillez fermer les guillemets.","empty_phrase":"Veuillez saisir un groupe de mots entre les guillemets.","invalid_control":"Veuillez supprimer les caract\u00e8res non imprimables de la recherche.","results_label":"R\u00e9sultats de recherche","matched_terms":"termes trouv\u00e9s"},"it":{"search":"Cerca","help":"Pi\u00f9 parole cercano tutti i termini. I \"gruppi di parole\" rimangono uniti.","enter":"Inserisca un termine di ricerca.","results":"risultati per","no_results":"Nessun risultato per","partial":"Risultati con parte dei termini di ricerca","partial_help":"Nessuna pagina contiene tutti i termini. Queste pagine ne contengono una parte.","suggestions":"Forse cercava:","without_quotes":"Cercare senza virgolette","protected":"Per visualizzare questo estratto \u00e8 necessario accedere.","title_only":"Corrispondenza nel titolo:","previous":"Precedente","next":"Successivo","page":"Pagina","of":"di","unavailable":"La ricerca non \u00e8 al momento disponibile. Riprovi pi\u00f9 tardi.","too_long":"La ricerca pu\u00f2 contenere al massimo 512 caratteri.","too_many_terms":"Utilizzi al massimo 16 termini o gruppi di parole.","unclosed_quote":"Chiuda le virgolette.","empty_phrase":"Inserisca un gruppo di parole tra le virgolette.","invalid_control":"Rimuova i caratteri non stampabili dalla ricerca.","results_label":"Risultati della ricerca","matched_terms":"termini trovati"},"rm":{"search":"Tschertgar","help":"Plirs pleds tschertgan tut ils terms. \"Gruppas da pleds\" restan ensemen.","enter":"Endatai in term da tschertga.","results":"resultats per","no_results":"Nagins resultats per","partial":"Resultats cun ina part dals terms","partial_help":"Nagina pagina cuntegna tut ils terms. Questas paginas cuntegnan ina part dad els.","suggestions":"Avais Vus manegi\u00e0:","without_quotes":"Tschertgar senza virgulettas","protected":"Per mussar quest extract \u00e8si necessari da s'annunziar.","title_only":"Resultat en il titel:","previous":"Enavos","next":"Enavant","page":"Pagina","of":"da","unavailable":"La tschertga n'\u00e8 actualmain betg disponibla. Empruvai pli tard.","too_long":"La tschertga dastga cuntegnair maximalmain 512 caracters.","too_many_terms":"Duvrai maximalmain 16 terms u gruppas da pleds.","unclosed_quote":"Serrai las virgulettas.","empty_phrase":"Endatai ina gruppa da pleds tranter las virgulettas.","invalid_control":"Allontanai ils caracters betg stampabels da la tschertga.","results_label":"Resultats da tschertga","matched_terms":"terms chattads"},"en":{"search":"Search","help":"Multiple words search for every term. \"Word groups\" stay together.","enter":"Please enter a search term.","results":"results for","no_results":"No results for","partial":"Results containing some search terms","partial_help":"No page contains every search term. These pages contain some of them.","suggestions":"Did you mean:","without_quotes":"Search without quotation marks","protected":"Please sign in to see this excerpt.","title_only":"Match in the page title:","previous":"Previous","next":"Next","page":"Page","of":"of","unavailable":"Search is temporarily unavailable. Please try again later.","too_long":"The search query must not exceed 512 characters.","too_many_terms":"Please use no more than 16 search terms or phrases.","unclosed_quote":"Please close the quotation marks.","empty_phrase":"Please enter a phrase between the quotation marks.","invalid_control":"Please remove non-printable characters from the query.","results_label":"Search results","matched_terms":"search terms found"}}
U5SEARCHLABELS
    , true);
    $language = u5SearchLanguage();
    return isset($labels[$language][$key]) ? $labels[$language][$key] : $labels['en']['unavailable'];
}

function u5SearchUrl($resource, $query, $page = null) {
    $parameters = array('c' => $resource, 'l' => isset($_GET['l']) && is_string($_GET['l']) ? $_GET['l'] : '', 'q' => $query, 'qv' => '2');
    if ($page !== null && $page > 1) $parameters['sp'] = (int) $page;
    return 'index.php?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
}

function u5SearchForm($large = false, $resource = '_search') {
    if ($large) u5SearchRenderStyles();
    $query = u5SearchRequest();
    $input = $large ? 'searchlarge' : 'search_Input';
    $form = $large ? 'frm_Search2' : 'frm_Search';
    $name = $large ? 'fsearch2' : 'fsearch';
    $label = u5SearchHtml(u5SearchLabel('search'));
    echo '<form role="search"' . ($large ? ' class="unibeform u5-search-form"' : '') . ' id="' . $form . '" name="' . $name . '" method="get" action="index.php" accept-charset="UTF-8">';
    echo '<input type="hidden" name="c" value="' . u5SearchHtml($resource) . '">';
    echo '<input type="hidden" name="l" value="' . u5SearchHtml(isset($_GET['l']) && is_string($_GET['l']) ? $_GET['l'] : '') . '">';
    echo '<input type="hidden" name="qv" value="2">';
    echo '<input type="search" name="q" id="' . $input . '" value="' . u5SearchHtml($query['raw']) . '" aria-label="' . $label . '" required maxlength="1024"';
    if ($large) echo ' aria-describedby="u5-search-help"';
    echo '>';
    echo '<input type="submit" class="btnSubmit"' . ($large ? '' : ' id="search_Submit"') . ' aria-label="' . $label . '" value="' . ($large ? $label : '') . '">';
    if ($large) echo '<p id="u5-search-help">' . u5SearchHtml(u5SearchLabel('help')) . '</p>';
    echo '</form>';
}

function u5SearchRenderStyles() {
    static $rendered = false;
    if ($rendered) return;
    $rendered = true;
    $version = substr(hash_file('sha256', __DIR__ . '/search-results.css'), 0, 12);
    echo '<link rel="stylesheet" href="search-results.css?v=' . $version . '">';
}

function u5SearchRenderHighlightScript() {
    static $rendered = false;
    $query = u5SearchRequest();
    if ($rendered || $query['error'] !== null || !$query['terms']) return;
    $rendered = true;
    u5SearchRenderStyles();
    $mapVersion = substr(hash_file('sha256', __DIR__ . '/search-normalization.json'), 0, 12);
    $scriptVersion = substr(hash_file('sha256', __DIR__ . '/js/search-highlight.js'), 0, 12);
    $config = array('terms' => $query['terms'], 'normalizationUrl' => 'search-normalization.json?v=' . $mapVersion, 'rootSelector' => '#content');
    echo '<script>window.u5SearchConfig=' . u5SearchJson($config) . ';</script>';
    echo '<script defer src="js/search-highlight.js?v=' . $scriptVersion . '"></script>';
}
