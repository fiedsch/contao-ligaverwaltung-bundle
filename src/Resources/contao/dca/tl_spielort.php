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

use Contao\DataContainer;
use Contao\DC_Table;

\Contao\System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_spielort'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
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
            'mode' => DataContainer::MODE_SORTED,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['name'],
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label' => [
            'fields' => ['name'],
            'format' => '%s',
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
                'label' => &$GLOBALS['TL_LANG']['tl_spielort']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_spielort']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_spielort']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_spielort']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},name;{details_legend},phone,website,street,postal,city,spielortpage,aufsteller',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['name'],
            'sorting' => true,
            'search' => true,
            'exclude' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => "varchar(128) default NULL",
        ],
        'street' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['street'],
            'sorting' => false,
            'search' => false,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 128, 'tl_class' => 'clr long'],
            'sql' => "varchar(128) NOT NULL default ''",
        ],
        'postal' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['postal'],
            'sorting' => false,
            'search' => false,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 32, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'city' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['city'],
            'sorting' => false,
            'search' => false,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'phone' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['phone'],
            'sorting' => false,
            'search' => false,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'tl_class' => 'w50', 'rgxp' => 'phone'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'website' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['website'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'aufsteller' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['aufsteller'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_aufsteller.name',
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'spielortpage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spielort']['spielortpage'],
            'inputType' => 'pageTree',
            'exclude' => true,
            'search' => false,
            'filter' => false,
            'sorting' => false,
            'eval' => ['mandatory' => false, 'multiple' => false, 'fieldType' => 'radio', 'tl_class' => 'w50'],
            'sql' => 'blob NULL',
        ],
    ],
];
