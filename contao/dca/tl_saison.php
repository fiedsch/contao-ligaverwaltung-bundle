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


use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_saison'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_UNSORTED,
            //'flag'        => DataContainer::SORT_ASC,
            'fields' => ['name'],
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label' => [
            'fields' => ['alias', 'name'],
            'format' => '[<code>%s</code>] %s',
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
            'edit',
            'copy',
            'delete',
            'show',
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},alias,name',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_saison']['name'],
            'sorting' => true,
            'exclude' => true,
            'flag' => DataContainer::SORT_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(128) default NULL",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_saison']['alias'],
            'sorting' => true,
            'exclude' => true,
            'flag' => DataContainer::SORT_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => true],
            'sql' => "varchar(128) default NULL",
        ],
    ],
];
