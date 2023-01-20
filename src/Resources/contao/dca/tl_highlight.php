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

use Fiedsch\LigaverwaltungBundle\Helper\DCAHelper;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\HighlightModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;

$GLOBALS['TL_DCA']['tl_highlight'] = [
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => 0, // Sorting mode: not sorted
            'flag' => 11, // Sorting flag: ascending
            'fields' => ['spieler_id', 'type'],
            'panelLayout' => 'sort,filter;search,limit',
            'headerFields' => ['name'],
            'filter' => [['begegnung_id IN (SELECT b.id FROM tl_begegnung b LEFT JOIN tl_liga l ON (b.pid=l.id) WHERE l.aktiv=?)', '1']],
        ],
        'label' => [
            'fields' => ['spieler_id'],
            'format' => '%s',
            'label_callback' => static function ($row) {
                $options = HighlightModel::getOptionsArray();
                $begegnung = BegegnungModel::findById($row['begegnung_id']);
                $spieler = SpielerModel::findById($row['spieler_id']);

                $result = sprintf('<strong>%s: %s</strong>', $options[$row['type']], $row['value']);

                if ($spieler) {
                    $result .= ' von '.$spieler->getFullName();
                }

                if ($begegnung) {
                    $result .= ' in der Begegnung '.$begegnung->getLabel('short');
                }
                //$result .= '<br>[Daten: ' . json_encode(func_get_args()) . ' ]';
                return $result;
            },
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
                'label' => &$GLOBALS['TL_LANG']['tl_highlight']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_highlight']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_highlight']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_highlight']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{details_legend},begegnung_id,spieler_id;{score_legend},type,value',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'begegnung_id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_highlight']['begegnung_id'],
            'inputType' => 'select',
            'filter' => true,
            'foreignKey' => 'tl_begegnung.id',
            'options_callback' => [DCAHelper::class, 'getBegegnungenForHighlight'],
            'eval' => ['submitOnChange' => true, 'chosen' => true, 'includeBlankOption' => true, 'mandatory' => false, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'spieler_id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_highlight']['spieler_id'],
            'inputType' => 'select',
            'sorting' => false,
            'foreignKey' => 'tl_spieler.id',
            'options_callback' => [DCAHelper::class, 'getSpielerForHighlight'],
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'mandatory' => false, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_highlight']['typ'],
            'inputType' => 'select',
            'sorting' => false,
            'filter' => true,
            'options' => HighlightModel::getOptionsArray(),
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 clr'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'value' => [
            'label' => &$GLOBALS['TL_LANG']['tl_highlight']['value'],
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'rgxp' => 'csvdigit'],
            'save_callback' => [[DCAHelper::class, 'cleanCsvDigitList']],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];
