<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_liga'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_verband',
        'ctable'           => ['tl_begegnung'],
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'              => 'primary',
                'pid,name,saison' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'                  => 4, // Displays the child records of a parent record
            'flag'                  => 11, // sort ascending
            'fields'                => ['name'],
            'panelLayout'           => 'sort,filter;search,limit',
            'headerFields'          => ['name'],
            'child_record_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'ligaListCallback'],
            'child_record_class'    => 'no_padding',
            'disableGrouping'       => true,
        ],
        'label'             => [
            'fields'         => ['name'],
            'format'         => '%s',
            'label_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'ligaLabelCallback'],
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
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['edit'],
                //'href'  => 'act=edit',
                'href'  => 'table=tl_begegnung',
                'icon'  => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.svg',
            ],

            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_liga']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_liga']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ],
            'toggle' => [
                'label'                => &$GLOBALS['TL_LANG']['tl_liga']['toggle'],
                'attributes'           => 'onclick="Backend.getScrollOffset();"',
                'haste_ajax_operation' => [
                    'field'   => 'aktiv',
                    'options' => [
                        [
                            'value' => '',
                            'icon'  => 'invisible.svg',
                        ],
                        [
                            'value' => '1',
                            'icon'  => 'visible.svg',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},name,saison,aktiv,spielstaerke,spielplan',
    ],

    'fields' => [
        'id'           => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'       => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'          => [
            'foreignKey' => 'tl_verband.name',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'name'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_liga']['name'],
            'sorting'   => true,
            'flag'      => 11, // sort ascending
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'saison'       => [
            'label'      => &$GLOBALS['TL_LANG']['tl_liga']['saison'],
            'inputType'  => 'select',
            'filter'     => true,
            'exclude'    => true,
            'foreignKey' => 'tl_saison.name',
            'eval'       => ['chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
        ],
        'aktiv'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_liga']['aktiv'],
            'inputType' => 'checkbox',
            'filter'    => true,
            'exclude'   => true,
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'spielstaerke' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_liga']['spielstaerke'],
            'inputType' => 'text',
            'filter'    => true,
            'exclude'   => true,
            'eval'      => ['rgxp' => 'digit', 'tl_class' => 'w50 clr'],
            'sql'       => "int(10) NOT NULL default '0'",
        ],
        'spielplan'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_liga']['spielplan'],
            'inputType' => 'select',
            'exclude'   => true,
            'options'   => [
                    \Contao\LigaModel::SPIELPLAN_16E2D => '16E,2D',
                    \Contao\LigaModel::SPIELPLAN_16E4D => '16E,4D',
                    \Contao\LigaModel::SPIELPLAN_6E3D  => '6E,3D',
                    \Contao\LigaModel::SPIELPLAN_16E   => '16E',
                ],
            'eval'      => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];