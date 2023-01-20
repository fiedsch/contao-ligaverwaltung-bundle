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

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_legend'] = 'Ligaverwaltung';

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_exclusive_model'] = [
    'Spielberechtigung',
    'Diese Einstellung regelt, ob und wie ein Spieler gleichzeitig in verschiedenen Mannschaften spielen darf. Siehe Definitionen in der "Ligaverwaltung".',
];

$GLOBALS['TL_LANG']['tl_settings']['teampage'] = [
    'Mannschaftsseite',
    'Die Seite, auf der das Mannschaftsseiten Reader Modul eingebunden ist.',
];

$GLOBALS['TL_LANG']['tl_settings']['spielberichtpage'] = [
    'Spielberichtsseite',
    'Die Seite, auf der das Spielbericht Reader Modul eingebunden ist.',
];

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_ranking_model'] = [
    'Sortierung Spieler-Ranking ("Punktevergabe")',
    'Regelt die Punktevergabe und damit die Sortierlogik in den Spieler-Rankings.',
];

$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_dataentry_compatibility_mode'] = [
    'Erfassung Spielberichtsbögen (Begegnungen)',
    'Bei der Erfassung der Spielberichtsbögen Symbole anzeigen, um Begenungen sowohl im "alten" (Spiele einzeln) als auch im "neuen" Modus (nur noch Eingabemaske) zu erfassen.',
];
