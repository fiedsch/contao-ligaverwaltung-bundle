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
use Contao\DataContainer;
use Contao\DC_Table;

\Contao\System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_verband'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_liga'],
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'name' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['name'],
            'panelLayout' => 'sort,filter;search,limit',
            'headerFields' => ['home'],
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
            'label_callback' => [DCAHelper::class, 'verbandLabelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_verband']['edit'],
                'href' => 'table=tl_liga',
                'icon' => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_verband']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_verband']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_verband']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},name',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_verband']['name'],
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'exclude' => true,
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
    ],
];
