<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_spiel'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ptable'           => 'tl_begegnung',
        'sql'              => [
            'keys' => [
                'id'   => 'primary',
                'pid'  => 'index',
                'home' => 'index',
                'away' => 'index',
                //'pid,home,away' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'                  => 4, // Displays the child records of a parent record
            'flag'                  => 11, // sort ascending
            'fields'                => ['slot'],
            'panelLayout'           => 'sort,filter;search,limit',
            'headerFields'          => ['home', 'away', 'pid'],
            'child_record_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'listSpielCallback'],
            'child_record_class'    => 'no_padding',
            'disableGrouping'       => true,
        ],
        'label'             => [
            'fields' => ['home', 'away'],
            'format' => '%s : %s',
            //'label_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'begegnungLabelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_spiel']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spiel']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_spiel']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spiel']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        '__selector__' => ['spieltype'],
        'default'      => '{title_legend},pid,spieltype,slot,score_home,score_away',
    ],

    'subpalettes' => [
        'spieltype_' . \SpielModel::TYPE_EINZEL => 'home,away',
        'spieltype_' . \SpielModel::TYPE_DOPPEL => 'home,away,home2,away2',
    ],

    'fields' => [
        'id'         => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'        => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'slot'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spiel']['slot'],
            'inputType' => 'text',
            'sorting'   => true,
            'eval'      => ['rgxp' => 'digit', 'minval' => 1],
            'sql'       => "int(10) unsigned NOT NULL default '1'",
        ],
        'home'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['home'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50 clr', 'chosen' => true, 'mandatory' => false, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_spieler', 'load' => 'lazy'],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getHomeSpielerForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'away'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['away'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50', 'chosen' => true, 'mandatory' => false, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_spieler', 'load' => 'lazy'],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAwaySpielerForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'score_home' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spiel']['score_home'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50 clr', 'mandatory' => true, 'rgxp' => 'digit'],
            'sql'       => "int(10) NOT NULL default '0'",
        ],
        'score_away' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spiel']['score_away'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'rgxp' => 'digit'],
            'sql'       => "int(10) NOT NULL default '0'",
        ],

        'spieltype' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spiel']['spieltype'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => [\SpielModel::TYPE_EINZEL => 'Einzel', \SpielModel::TYPE_DOPPEL => 'Doppel'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'submitOnChange' => true, 'includeBlankOption' => true],
            'sql'       => "int(10) NOT NULL default '0'",
        ],
        'home2'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['home2'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50 clr', 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_spieler', 'load' => 'lazy'],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getHomeSpielerForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
        'away2'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spiel']['away2'],
            'filter'           => true,
            'exclude'          => true,
            'sorting'          => true,
            'flag'             => 11, // sort ascending
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50', 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true],
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_spieler', 'load' => 'lazy'],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAwaySpielerForSelect'],
            'sql'              => "int(10) NOT NULL default '0'",
        ],
    ],
];