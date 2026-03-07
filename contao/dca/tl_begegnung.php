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
 *
 * TODO: disable turbo for the complete data entry form so that toggling [] published forces a reload of the vue app with toggled "disabled" data field
 */

use Contao\DC_Table;
use Contao\System;
use Contao\DataContainer;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;

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
        // 'onload_callback' => [
        //     [], // TODO (?) analog array('tl_page', 'addBreadcrumb'),
        // ]
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'flag' => DataContainer::SORT_ASC,
            'panelLayout' => 'filter;sort,limit',
            'headerFields' => ['pid', 'name', 'saison'],
            'child_record_callback' => [DCAHelper::class, 'labelBegegnungCallbackChildView'],
            'disableGrouping' => true,
            //'defaultSearchField' => '...' // TODO wir bräuchten hier etwas dynamisches
        ],
        'label' => [
            'fields' => ['pid', 'spiel_tag', 'spiel_am', 'home', 'away'],
            'format' => '%s, %s, %s, %s : %s, %s',
            'showColumns' => true,
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
            'edit' => [
                'href' => 'act=edit',
                'primary' => true,
                'icon' => 'edit.svg',
                'prefetch' => false,
            ],
            // 'children', // entfällt zugunsten des Vue-Widgets, mit dem die tl_spiel-Records automatisch generiert bzw. bearbeitet werden
            // 'copy', // ergibt hier keinen Sinn
            // 'cut', // ergibt hier keinen Sinn
            'delete',
            'toggle',
            'show',
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},pid,home,away;{details_legend},spiel_tag,spiel_am,published,postponed,kommentar;{vueapp_legend},vue_app',
    ],

    'fields' => [
        'id' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['id'],
            'search' => true,
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'pid' => [
            //'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['pid'],
            'filter' => false,
            'exclude' => true,
            'sorting' => false,
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
            'eval' => ['tl_class' => 'w50,clr'/*,'submitOnChange' => true*/],
            'sql' => ['type' => 'boolean', 'default' => false]
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
            // Data not shown in back end
            // 'inputType' => 'yamlWidget',
            'exclude' => true,
            'default' => '',
            'sql' => 'blob NOT NULL',
        ],
        'vue_app' => [
            'inputType' => 'begegnungdataentry_widget',
            'eval'      => ['tl_class' => 'clr long', 'doNotSaveEmpty' => true],
            'sql' => null,
        ],
        'postponed' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['postponed'],
            'inputType' => 'checkbox',
            'filter' => true,
            'exclude' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => false]
        ],
        'erfasst' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'sql' => ['type' => 'boolean', 'default' => false]
        ]
    ],
];
