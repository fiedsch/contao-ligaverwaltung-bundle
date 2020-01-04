<?php

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2018 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

use Fiedsch\LigaverwaltungBundle\ContentElement\ContentBegegnungsauswahl;

array_insert($GLOBALS['BE_MOD'], 2, [
    'liga' => [
        'liga.spielort' => [
            'tables' => ['tl_spielort'],
        ],
        'liga.aufsteller' => [
            'tables' => ['tl_aufsteller'],
        ],
        'liga.saison' => [
            'tables' => ['tl_saison'],
        ],
        'liga.verband' => [
            'tables' => ['tl_verband', 'tl_liga', 'tl_begegnung', 'tl_spiel'],
        ],
        'liga.mannschaft' => [
            'tables' => ['tl_mannschaft', 'tl_spieler'],
        ],
        'liga.begegnung' => [
            'tables' => ['tl_begegnung', 'tl_spiel'],
        ],
        // 'liga.highlight' => [
        //     'tables' => ['tl_highlight'],
        //     'javascript' => 'bundles/fiedschligaverwaltung/tl_highlight.js',
        //     'stylesheet' => 'bundles/fiedschligaverwaltung/tl_highlight.css',
        // ],
    ],
]);

/*
 * Contentelemente
 */
$GLOBALS['TL_CTE']['ligaverwaltung']['ligenliste'] = 'ContentLigenliste';
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftsliste'] = '\Fiedsch\LigaverwaltungBundle\ContentMannschaftsliste';
$GLOBALS['TL_CTE']['ligaverwaltung']['spielbericht'] = '\Fiedsch\LigaverwaltungBundle\ContentSpielbericht';
$GLOBALS['TL_CTE']['ligaverwaltung']['spielerliste'] = '\Fiedsch\LigaverwaltungBundle\ContentSpielerliste';
$GLOBALS['TL_CTE']['ligaverwaltung']['spielplan'] = '\Fiedsch\LigaverwaltungBundle\ContentSpielplan';
$GLOBALS['TL_CTE']['ligaverwaltung']['spielortinfo'] = '\Fiedsch\LigaverwaltungBundle\ContentSpielortinfo';
$GLOBALS['TL_CTE']['ligaverwaltung']['ranking'] = '\Fiedsch\LigaverwaltungBundle\ContentRanking';
$GLOBALS['TL_CTE']['ligaverwaltung']['highlightranking'] = '\Fiedsch\LigaverwaltungBundle\ContentHighlightRanking';
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftsseite'] = '\Fiedsch\LigaverwaltungBundle\ContentMannschaftsseite';
$GLOBALS['TL_CTE']['ligaverwaltung']['spielortseite'] = '\Fiedsch\LigaverwaltungBundle\ContentSpielortseite';
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftenuebersicht'] = '\Fiedsch\LigaverwaltungBundle\ContentMannschaftenuebersicht';
$GLOBALS['TL_CTE']['ligaverwaltung']['begegnungsauswahl'] = ContentBegegnungsauswahl::class;

/*
 * Module
 */
$GLOBALS['FE_MOD']['ligaverwaltung']['mannschaftsseitenreader'] = '\Fiedsch\LigaverwaltungBundle\ModuleMannschaftsseitenReader';
$GLOBALS['FE_MOD']['ligaverwaltung']['spielortseitenreader'] = '\Fiedsch\LigaverwaltungBundle\ModuleSpielortseitenReader';
$GLOBALS['FE_MOD']['ligaverwaltung']['spielberichtreader'] = '\Fiedsch\LigaverwaltungBundle\ModuleSpielberichtReader';

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'addCustomRegexp'];

/* Add to Backend CSS */
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/fiedschligaverwaltung/backend.css';
}
