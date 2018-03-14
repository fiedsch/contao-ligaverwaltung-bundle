<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_saison'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'   => 'primary',
                'name' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'        => 0,
            //'flag'        => 11,
            'fields'      => ['name'],
            'panelLayout' => 'sort,filter;search,limit',
        ],
        'label'             => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_saison']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_saison']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_saison']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_saison']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},name',
    ],

    'fields' => [
        'id'     => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_saison']['name'],
            'sorting'   => true,
            'exclude'   => true,
            'flag'      => 11, // sort ascending
            'inputType' => 'text',
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],

    ],
];