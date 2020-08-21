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
use Fiedsch\LigaverwaltungBundle\Element\ContentLigenliste;
use Fiedsch\LigaverwaltungBundle\Element\ContentMannschaftsliste;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielbericht;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielerliste;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielplan;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielortinfo;
use Fiedsch\LigaverwaltungBundle\Element\ContentRanking;
use Fiedsch\LigaverwaltungBundle\Element\ContentHighlightRanking;
use Fiedsch\LigaverwaltungBundle\Element\ContentMannschaftsseite;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielortseite;
use Fiedsch\LigaverwaltungBundle\Element\ContentMannschaftenuebersicht;
use Fiedsch\LigaverwaltungBundle\Element\ContentBegegnungsauswahl;

$GLOBALS['TL_CTE']['ligaverwaltung']['ligenliste'] = ContentLigenliste::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftsliste'] = ContentMannschaftsliste::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['spielbericht'] = ContentSpielbericht::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['spielerliste'] = ContentSpielerliste::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['spielplan'] = ContentSpielplan::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['spielortinfo'] = ContentSpielortinfo::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['ranking'] = ContentRanking::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['highlightranking'] = ContentHighlightRanking::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftsseite'] = ContentMannschaftsseite::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['spielortseite'] = ContentSpielortseite::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftenuebersicht'] = ContentMannschaftenuebersicht::class;
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
use Fiedsch\LigaverwaltungBundle\Helper\DCAHelper;
$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = [DCAHelper::class, 'addCustomRegexp'];

/* Add to Backend CSS */
if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/fiedschligaverwaltung/backend.css';
}

/**
 * Models
 */
use Fiedsch\LigaverwaltungBundle\Model\AufstellerModel;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\HighlightModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;
use Fiedsch\LigaverwaltungBundle\Model\VerbandModel;


$GLOBALS['TL_MODELS']['tl_aufsteller'] = AufstellerModel::class;
$GLOBALS['TL_MODELS']['tl_begegnung'] = BegegnungModel::class;
$GLOBALS['TL_MODELS']['tl_highlight'] = HighlightModel::class;
$GLOBALS['TL_MODELS']['tl_liga'] = LigaModel::class;
$GLOBALS['TL_MODELS']['tl_mannschaft'] = MannschaftModel::class;
$GLOBALS['TL_MODELS']['tl_saison'] = SaisonModel::class;
$GLOBALS['TL_MODELS']['tl_spieler'] = SpielerModel::class;
$GLOBALS['TL_MODELS']['tl_spiel'] = SpielModel::class;
$GLOBALS['TL_MODELS']['tl_spielort'] = SpielortModel::class;
$GLOBALS['TL_MODELS']['tl_verband'] = VerbandModel::class;
