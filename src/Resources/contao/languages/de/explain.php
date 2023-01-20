<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2023 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_LANG']['XPL']['begegnung_data_explanation'] = [
    [
        'Interne Daten',
        'In den internen Daten der Begegnungserfassung werden die im Formular eingegebenen Daten gespeichert, '
        .'damit dies erneut aufgerufen werden kann um (z.B.) die erfassten Ergebnisse zu modifizieren. <br>'
        .'Für den "normalen" User sind sie nicht von interesse und können in der Konfiguration einer Benutzergruppe '
        .' ausgeblendet werden.<br>'
        .'Administratoren könnten hier Fehler bereinigen, die dazu führen, daß die Erfassungsmaske (eine Vue.js App) '
        .'nicht korrekt angezeigt wird.',
    ],
];
