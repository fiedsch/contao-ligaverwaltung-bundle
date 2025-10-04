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

use Fiedsch\LigaverwaltungBundle\Element\ContentBegegnungsauswahl;
use Fiedsch\LigaverwaltungBundle\Element\ContentHighlightRanking;
use Fiedsch\LigaverwaltungBundle\Element\ContentLigenliste;
use Fiedsch\LigaverwaltungBundle\Element\ContentMannschaftenuebersicht;
use Fiedsch\LigaverwaltungBundle\Element\ContentMannschaftsliste;
use Fiedsch\LigaverwaltungBundle\Element\ContentMannschaftsseite;
use Fiedsch\LigaverwaltungBundle\Element\ContentRanking;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielbericht;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielerliste;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielortinfo;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielortseite;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielplan;
use Fiedsch\LigaverwaltungBundle\Element\ContentTeamsAndPlayersOverview;
use Fiedsch\LigaverwaltungBundle\Helper\DCAHelper;
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
use Fiedsch\LigaverwaltungBundle\Module\ModuleMannschaftsseitenReader;
use Fiedsch\LigaverwaltungBundle\Module\ModuleSpielberichtReader;
use Fiedsch\LigaverwaltungBundle\Module\ModuleSpielortseitenReader;
use Fiedsch\LigaverwaltungBundle\Widget\Backend\VueWidget;
use Contao\ArrayUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Fiedsch\LigaverwaltungBundle\Callback\BegegnungDataEntryForm;

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 2, [
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
        // 'liga.editbegegnung' => [ // hidden in backend.css (if it is the last li of the group (uses li:last-child))
        //     'callback' => BegegnungDataEntryForm::class,
        //     'tables' => ['tl_begegnung'],
        // ],
    ],
]);

/*
 * Contentelemente
 */

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
