<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2025 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

use Contao\ArrayUtil;
use Contao\System;
use Fiedsch\Ligaverwaltung\Element\ContentBegegnungsauswahl;
use Fiedsch\Ligaverwaltung\Element\ContentHighlightRanking;
use Fiedsch\Ligaverwaltung\Element\ContentLigenliste;
use Fiedsch\Ligaverwaltung\Element\ContentMannschaftenuebersicht;
use Fiedsch\Ligaverwaltung\Element\ContentMannschaftsliste;
use Fiedsch\Ligaverwaltung\Element\ContentMannschaftsseite;
use Fiedsch\Ligaverwaltung\Element\ContentSpielbericht;
use Fiedsch\Ligaverwaltung\Element\ContentSpielerliste;
use Fiedsch\Ligaverwaltung\Element\ContentSpielortinfo;
use Fiedsch\Ligaverwaltung\Element\ContentSpielortseite;
use Fiedsch\Ligaverwaltung\Element\ContentSpielplan;
use Fiedsch\Ligaverwaltung\Element\ContentTeamsAndPlayersOverview;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Model\AufstellerModel;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Model\HighlightModel;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Model\SpielModel;
use Fiedsch\Ligaverwaltung\Model\SpielortModel;
use Fiedsch\Ligaverwaltung\Model\VerbandModel;
use Fiedsch\Ligaverwaltung\Module\ModuleMannschaftsseitenReader;
use Fiedsch\Ligaverwaltung\Module\ModuleSpielberichtReader;
use Fiedsch\Ligaverwaltung\Module\ModuleSpielortseitenReader;
use Fiedsch\Ligaverwaltung\Widget\Backend\VueWidget;
use Symfony\Component\HttpFoundation\Request;

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 2, [
    'liga' => [
        'liga_spielort' => [
            'tables' => ['tl_spielort'],
        ],
        'liga_aufsteller' => [
            'tables' => ['tl_aufsteller'],
        ],
        'liga_saison' => [
            'tables' => ['tl_saison'],
        ],
        'liga_verband' => [
            'tables' => ['tl_verband', 'tl_liga', 'tl_begegnung', 'tl_spiel'],
        ],
        'liga_mannschaft' => [
            'tables' => ['tl_mannschaft', 'tl_spieler'],
        ],
    ],
]);

/*
 * Contentelemente
 */

// $GLOBALS['TL_CTE']['ligaverwaltung']['ligenliste'] = ContentLigenliste::class; // now a service registered using attributes (see LigenlisteController)
// $GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftsliste'] = ContentMannschaftsliste::class; // now a service registered using attributes (see LigenlisteController)
// $GLOBALS['TL_CTE']['ligaverwaltung']['spielbericht'] = ContentSpielbericht::class; // now a service registered using attributes (see LigenlisteController)
// $GLOBALS['TL_CTE']['ligaverwaltung']['spielerliste'] = ContentSpielerliste::class; // now a service registered using attributes (see LigenlisteController)
$GLOBALS['TL_CTE']['ligaverwaltung']['spielplan'] = ContentSpielplan::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['spielortinfo'] = ContentSpielortinfo::class;
// $GLOBALS['TL_CTE']['ligaverwaltung']['ranking'] = ContentRanking::class; // now a service registered using attributes (see RankingController)
$GLOBALS['TL_CTE']['ligaverwaltung']['highlightranking'] = ContentHighlightRanking::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftsseite'] = ContentMannschaftsseite::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['spielortseite'] = ContentSpielortseite::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['mannschaftenuebersicht'] = ContentMannschaftenuebersicht::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['begegnungsauswahl'] = ContentBegegnungsauswahl::class;
$GLOBALS['TL_CTE']['ligaverwaltung']['teamsandplayersoverview'] = ContentTeamsAndPlayersOverview::class;

/*
 * Module
 */

$GLOBALS['FE_MOD']['ligaverwaltung']['mannschaftsseitenreader'] = ModuleMannschaftsseitenReader::class;
$GLOBALS['FE_MOD']['ligaverwaltung']['spielortseitenreader'] = ModuleSpielortseitenReader::class;
$GLOBALS['FE_MOD']['ligaverwaltung']['spielberichtreader'] = ModuleSpielberichtReader::class;

/*
 * Hooks
 */

$GLOBALS['TL_HOOKS']['addCustomRegexp'][] = [DCAHelper::class, 'addCustomRegexp'];






/* Add to Backend CSS */
if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(
    System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create('')
)) {
    $GLOBALS['TL_CSS'][] = 'bundles/fiedschligaverwaltung/backend.css';
}

/*
 * Models
 */

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


/*
 * Widgets
 */
$GLOBALS['BE_FFL']['vue_widget'] = VueWidget::class;
