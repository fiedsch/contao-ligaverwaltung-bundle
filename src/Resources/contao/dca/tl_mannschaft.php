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

$GLOBALS['TL_DCA']['tl_mannschaft'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_spieler'],
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'liga' => 'index',
                'liga,name' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => 2, // Records are sorted by a switchable field
            'fields' => ['name', 'liga'],
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'liga'],
            'format' => '%s %s',
            'label_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'mannschaftLabelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['edit'],
                'href' => 'table=tl_spieler',
                'icon' => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'cut' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},name,spielort,liga;{details_legend},active', /*,teampage',*/
    ],

    'fields' => [
        'id' => [
            'search' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],

        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'liga' => [
            'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['liga'],
            'inputType' => 'select',
            'filter' => true,
            'sorting' => true,
            'flag' => 1, // Sort by initial letter ascending
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'foreignKey' => 'tl_liga.name',
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getLigaForSelect'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['name'],
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'filter' => false,
            'sorting' => true,
            'flag' => 1, // Sort by initial letter ascending
            'eval' => ['mandatory' => true, 'maxlength' => 255],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'spielort' => [
            'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['spielort'],
            'inputType' => 'select',
            'exclude' => true,
            'search' => false,
            'filter' => true,
            'sorting' => false,
            'eval' => ['mandatory' => true, 'chosen' => true, 'includeBlankOption' => true],
            'foreignKey' => 'tl_spielort.name',
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'active' => [
            'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['active'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'search' => false,
            'filter' => true,
            'sorting' => false,
            //'eval'       => ['tl_style'=>'w50'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
    ],
];
