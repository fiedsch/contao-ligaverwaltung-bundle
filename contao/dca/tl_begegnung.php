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

use Contao\DC_Table;
use Contao\Config;
use Contao\Input;
use Contao\System;
use Contao\DataContainer;
use Fiedsch\LigaverwaltungBundle\Helper\DCAHelper;
use Fiedsch\LigaverwaltungBundle\Model\SpielModel;

System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_begegnung'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ptable' => 'tl_liga',
        'ctable' => ['tl_spiel'],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'home' => 'index',
                'away' => 'index',
                // Jede Mannschaft spielt (in einer Liga) maximal einmal gegen eine andere:
                //'pid,home,away' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            // note: these settings will be used if we are not called as a child record
            // see if () {} at the end of this file
            /*
            'mode'        => DataContainer::MODE_SORTABLE,
            'flag'        => DataContainer::SORT_ASC,
            'fields'      => ['pid','home','away'],
            'panelLayout' => 'sort,filter;search,limit',
            */
            'mode' => DataContainer::MODE_PARENT,
            'flag' => DataContainer::SORT_ASC,
            'fields' => ['pid', 'home', 'away'],
            'panelLayout' => 'sort,filter;search,limit',
            'headerFields' => ['name', 'saison'],
            'child_record_callback' => [DCAHelper::class, 'labelBegegnungCallback'],
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['home', 'away'],
            'format' => '%s : %s',
            'label_callback' => [DCAHelper::class, 'labelBegegnungCallback'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            // 'edit' => [
            //     //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['edit'],
            //     'href' => 'table=tl_spiel',
            //     'icon' => 'edit.svg',
            // ],
            // 'editheader' => [ // siehe 'children' unten!
            //     'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['editheader'],
            //     'href' => 'act=edit',
            //     'icon' => 'header.svg',
            // ],
            // // 'editform' => [
            // //     'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['editform'],
            // //     'button_callback' => static function ($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext) {
            // //         // TODO (?): check if begegnung is already published and then disable icon (using children_.svg)
            // //         // $icon = $arrRow['published'] ? 'children_.svg' : 'children.svg';
            // //         // Problem hierbei: ein klick auf das Auge ändert zwar den Status in published, aber nicht gleichzeitig das 'children' Icon.
            // //         // Im Zweiewlsfall gibt es aber eine Meldung vom LigaverwaltungBackendController::begegnungDataSaveAction(). Daher:
            // //         $icon = 'children.svg';
            // //         return "<a href='contao?do=liga.editbegegnung&table=tl_begegnung&id=$arrRow[id]' title='die Spiele der Begegnung bearbeiten' class='edit'><img src='system/themes/flexible/icons/$icon' alt='bearbeiten'></a>&nbsp;";
            // //
            // //         // return sprintf('<a href="%s" title="die Begegnung bearbeiten (neuer Modus)" class="edit">%s</a>',
            // //         //     System::getContainer()->get('router')->generate('begegnung_dataentry_form', ['begegnung' => $arrRow['id']]),
            // //         //     '<img src="bundles/fiedschligaverwaltung/icons/all.svg" alt="erfassen">&nbsp;'
            // //         // );
            // //     },
            // // ],
            'edit',
            // 'children', // entfällt zugunsten des Vue-Widgets, mit dem die tl_spiel-Records automatisch generiert bzw. bearbeitet werden (TODO (?): für Admin-User einblenden; Problem: beim Bearbeiten von tl_spiel-Records könnten Inkonsistenzen mit tl_begegnung.begegnung_data entstehen)
            // 'copy',
            // 'cut',
            'delete',
            'toggle',
            'show',
        ],
    ],

    'palettes' => [
        // Note: never show {internal_legend},begegnung_data as this will break our data saving as we (a) save the vue_app's data in its callback to begegnung_data ant (b) save (the old value) of begegnung_data itself.
        // If we don't show it (b) won't happen!
        'default' => '{title_legend},pid,home,away;{details_legend},spiel_tag,spiel_am,published,postponed,kommentar;{vueapp_legend},vue_app',
    ],

    'fields' => [
        'id' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['id'],
            'search' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['pid'],
            'filter' => true,
            'exclude' => true,
            'sorting' => true,
            //'flag'             => DataContainer::SORT_ASC,
            'inputType' => 'select',
            'foreignKey' => 'tl_liga.name',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'mandatory' => true],
            'options_callback' => [DCAHelper::class, 'getAktiveLigenForSelect'],
            'relation' => ['type' => 'belongsTo'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'published' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['published'],
            'inputType' => 'checkbox',
            'filter' => true,
            'exclude' => true,
            'toggle' => true,
            'eval' => ['tl_class' => 'w50,clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'home' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['home'],
            'filter' => true,
            'exclude' => true,
            'sorting' => true,
            //'flag'             => DataContainer::SORT_ASC,
            'inputType' => 'select',
            'foreignKey' => 'tl_mannschaft.name',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50 clr', 'chosen' => true, 'includeBlankOption' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
            'options_callback' => [DCAHelper::class, 'getMannschaftenForSelect'],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'away' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['away'],
            'filter' => true,
            'exclude' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'foreignKey' => 'tl_mannschaft.name',
            'inputType' => 'select',
            // 'mandatory' => false da "kein Gegner angegeben === Spielfrei"
            'eval' => ['mandatory' => false, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
            'options_callback' => [DCAHelper::class, 'getMannschaftenForSelect'],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'spiel_tag' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['spiel_tag'],
            'exclude' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'minval' => 1, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '1'",
        ],
        'spiel_am' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['spiel_am'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(11) NOT NULL default ''",
        ],
        'kommentar' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['kommentar'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr long', 'maxlength' => 255],
            'sql' => 'mediumtext NULL',
        ],
        'begegnung_data' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['begegnung_data'],
            'inputType' => 'yamlWidget',
            'exclude' => true,
            'eval' => ['rte' => 'ace|yaml' /*, 'helpwizard' => true*/],
            'default' => '',
            'sql' => 'blob NOT NULL',
            //'explanation' => 'begegnung_data_explanation',
        ],
        'vue_app' => [
            'inputType' => 'vue_widget',
            'eval'      => ['tl_class' => 'clr long', 'doNotSaveEmpty' => true],
            'sql' => null,
        ],
        'postponed' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['postponed'],
            'inputType' => 'checkbox',
            'filter' => true,
            'exclude' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

/* Bei Aufruf "nicht als child record von liga.verband */
if ('liga.begegnung' === Input::get('do')) {
    $GLOBALS['TL_DCA']['tl_begegnung']['list']['sorting'] = [
        'mode' => DataContainer::MODE_SORTABLE,
        'flag' => DataContainer::SORT_ASC,
        'fields' => ['pid'],
        'panelLayout' => 'sort,filter;search,limit',
        'disableGrouping' => false,
        'filter' => [
            ['pid IN (SELECT id FROM tl_liga WHERE aktiv=?)', '1'],
        ],
    ];
}

if (!Config::get('ligaverwaltung_dataentry_compatibility_mode')) {
    unset($GLOBALS['TL_DCA']['tl_begegnung']['list']['operations']['edit']);
}
