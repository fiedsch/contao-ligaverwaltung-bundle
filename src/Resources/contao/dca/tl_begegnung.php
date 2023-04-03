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

use Contao\Config;
use Contao\Input;
use Contao\System;
use Contao\DataContainer;
use Contao\DC_Table;
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
            'editform' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['editform'],
                // 'href' => '', // see button_callback for URL generation
                'button_callback' => static function ($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext) {
                    $numSpiele = SpielModel::countBy('pid', $arrRow['id']);

                    // Legacy-Check: wenn wir bereits tl_spiel Records haben, aber keine Daten
                    // in begegnung_data gespeichert (=alte Dateneingabe), dann diesen Button
                    // "disablen". Änderung dann (wie früher) nur noch über die einzelnen
                    // tl_spiel-Records!
                    if ('0' === $arrRow['away']) {
                        return '<img src="bundles/fiedschligaverwaltung/icons/all_.svg" title="Spielfrei!">&nbsp;';
                    }

                    if ($numSpiele > 0 && '' === $arrRow['begegnung_data']) {
                        return '<img src="bundles/fiedschligaverwaltung/icons/all_.svg" title="Begegnung wurde mit dem alten System erfasst!">&nbsp;';
                    }

                    return sprintf('<a href="%s" title="die Begegnung bearbeiten (neuer Modus)" class="edit">%s</a>',
                        System::getContainer()->get('router')->generate('begegnung_dataentry_form', ['begegnung' => $arrRow['id']]),
                        '<img src="bundles/fiedschligaverwaltung/icons/all.svg" alt="erfassen">&nbsp;'
                    );

                // <a
                    //   href="contao?do=liga.begegnung&amp;act=copy&amp;id=2904&amp;rt=7cyllBJISr6p5rN8YH8wOYBIlRUnnUPfusPaGVAc25Y&amp;ref=yC4LvTWP"
                    //   title=""
                    //   class="copy"
                    // >
                    //   <img src="system/themes/flexible/icons/copy.svg" width="16" height="16" alt="kopieren">
                    // </a>
                },
            ],
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['edit'],
                'href' => 'table=tl_spiel', // also see 'button_callback'!
                'icon' => 'edit.svg',
                'button_callback' => static function ($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext) {
                    // Legacy-Check: nur anzeigen, wenn
                    // im tl_begegnung-Record keine 'begegnung_data'-Daten gespeichert sind
                    // und gleichzeitig aber tl_spiel Daten vorhanden sind.
                    $numSpiele = SpielModel::countBy('pid', $arrRow['id']);
                    // Negierung der Bedingung beim 'editform'-'button_callback', also immer nur einen
                    // von beiden Buttons anzeigen
                    if ('0' === $arrRow['away']) {
                        return '<img src="system/themes/flexible/icons/edit_.svg" title="Spielfrei!">&nbsp;';
                    }

                    if ('' !== $arrRow['begegnung_data'] || 0 === $numSpiele) {
                        return '<img src="system/themes/flexible/icons/edit_.svg" title="Begegnung wird über die Eingabemaske verwaltet!">&nbsp;';
                    }

                    return "<a href='contao?do=liga.begegnung&table=tl_spiel&id=$arrRow[id]' title='die Begegnung bearbeiten (alter Modus)' class='edit'><img src='system/themes/flexible/icons/edit.svg' alt='bearbeiten'></a>&nbsp;";
                },
            ],

            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['toggle'],
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},pid,home,away;{details_legend},spiel_tag,spiel_am,published,postponed,kommentar,{internal_legend},begegnung_data',
    ],

    'fields' => [
        'id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['id'],
            'search' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['pid'],
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
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['published'],
            'inputType' => 'checkbox',
            'filter' => true,
            'exclude' => true,
            'toggle' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'home' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['home'],
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
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['away'],
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
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['spiel_tag'],
            'exclude' => true,
            'sorting' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'minval' => 1, 'mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '1'",
        ],
        'spiel_am' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['spiel_am'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(11) NOT NULL default ''",
        ],
        'kommentar' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['kommentar'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr long', 'maxlength' => 255],
            'sql' => 'mediumtext NULL',
        ],
        'begegnung_data' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['begegnung_data'],
            'inputType' => 'yamlWidget',
            'exclude' => true,
            'eval' => ['rte' => 'ace|yaml' /*, 'helpwizard' => true*/],
            'default' => '',
            'sql' => 'blob NOT NULL',
            //'explanation' => 'begegnung_data_explanation',
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
        'fields' => ['pid', 'home', 'away'],
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
