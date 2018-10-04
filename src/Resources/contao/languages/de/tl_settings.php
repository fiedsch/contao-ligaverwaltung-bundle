<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_legend'] = 'Ligaverwaltung';

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_exclusive_model'] = [
    'Spielberechtigung',
    'Diese Einstellung regelt, ob und wie ein Spieler gleichzeitig in verschiedenen Mannschaften spielen darf. Siehe Definitionen in der "Ligaverwaltung".',
];

$GLOBALS['TL_LANG']['tl_settings']['teampage'] = [
    'Mannschaftsseite',
    'Die Seite, auf der das Mannschaftsseiten Reader Modul eingebunden ist.',
];

$GLOBALS['TL_LANG']['tl_settings']['spielberichtpage']  = [
    'Spielberichtsseite',
    'Die Seite, auf der das Spielbericht Reader Modul eingebunden ist.',
];

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_ranking_model'] = [
    'Sortierung Ranking ("Punktevergabe")',
    'Regelt die Punktevergabe und damit die Sortierlogik in den Ranking.'
];

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_ranking_model_ties'] = [
    'Sortierung Ranking bei Gleichstand',
    'Regelt, wie bei Gleichstand weiter entschieden wird (betrachten der nächsten "Dimension", d.h. gleiche Punkte → Spiele betrachten, gleiche Spiele → Legs betrachten).',
];
