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
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_liga'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_verband',
        'ctable' => ['tl_begegnung'],
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,name,saison' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'flag' => DataContainer::SORT_ASC,
            'fields' => ['name'],
            'panelLayout' => 'sort,filter;search,limit',
            'headerFields' => ['name'],
            'child_record_callback' => [DCAHelper::class, 'ligaListCallback'],
            'child_record_class' => 'no_padding',
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
            'label_callback' => [DCAHelper::class, 'ligaLabelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['edit'],
                //'href'  => 'act=edit',
                'href' => 'table=tl_begegnung',
                'icon' => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
            ],

            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
            'toggle' => array
            (
                'href'                => 'act=toggle&amp;field=aktiv',
                'icon'                => 'visible.svg',
            ),
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},name,saison,aktiv,spielstaerke,spielplan;{abrechnung_legend},rechnungsbetrag_spielort,rechnungsbetrag_aufsteller',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid' => [
            'foreignKey' => 'tl_verband.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_liga']['name'],
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(128) default NULL",
        ],
        'saison' => [
            'label' => &$GLOBALS['TL_LANG']['tl_liga']['saison'],
            'inputType' => 'select',
            'filter' => true,
            'exclude' => true,
            'foreignKey' => 'tl_saison.alias',
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'mandatory' => true],
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'aktiv' => [
            'label' => &$GLOBALS['TL_LANG']['tl_liga']['aktiv'],
            'inputType' => 'checkbox',
            'filter' => true,
            'toggle' => true,
            'exclude' => true,
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'spielstaerke' => [
            'label' => &$GLOBALS['TL_LANG']['tl_liga']['spielstaerke'],
            'inputType' => 'text',
            'filter' => true,
            'exclude' => true,
            'eval' => ['rgxp' => 'digit', 'tl_class' => 'w50 clr'],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'spielplan' => [
            'label' => &$GLOBALS['TL_LANG']['tl_liga']['spielplan'],
            'inputType' => 'select',
            'exclude' => true,
            'options' => [
                LigaModel::SPIELPLAN_16E2D => '16E,2D',
                LigaModel::SPIELPLAN_16E4D => '16E,4D',
                LigaModel::SPIELPLAN_8E2D => '8E,2D',
                LigaModel::SPIELPLAN_6E3D => '6E,3D',
                LigaModel::SPIELPLAN_16E => '16E',
                LigaModel::SPIELPLAN_4E1D => '4E1D',
            ],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'rechnungsbetrag_spielort' => [
            'label' => &$GLOBALS['TL_LANG']['tl_liga']['rechnungsbetrag_spielort'],
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [static function ($value) {
                if ('' === $value) {
                    return $value;
                }

                if (!preg_match('/^\d+(,*\\d{1,2})?$/', $value)) {
                    throw new Exception("Ungültiger Wert: $value");
                }

                return $value;
            }],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'rechnungsbetrag_aufsteller' => [
            'label' => &$GLOBALS['TL_LANG']['tl_liga']['rechnungsbetrag_aufsteller'],
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [static function ($value) {
                if ('' === $value) {
                    return $value;
                }

                if (!preg_match('/^\d+(,*\\d{1,2})?$/', $value)) {
                    throw new Exception("UngültigerWert: $value");
                }

                return $value;
            }],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
    ],
];
