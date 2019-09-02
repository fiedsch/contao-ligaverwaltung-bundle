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

$GLOBALS['TL_DCA']['tl_begegnung'] = [
    'config' => [
        'dataContainer' => 'Table',
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
            'mode'        => 2,
            'flag'        => 11, // sort ascending
            'fields'      => ['pid','home','away'],
            'panelLayout' => 'sort,filter;search,limit',
            */
            /* */
            'mode' => 4, // Displays the child records of a parent record
            'flag' => 11, // sort ascending
            'fields' => ['pid', 'home', 'away'],
            'panelLayout' => 'sort,filter;search,limit',
            'headerFields' => ['name', 'saison'],
            'child_record_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'labelBegegnungCallback'],
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['home', 'away'],
            'format' => '%s : %s',
            'label_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'labelBegegnungCallback'],
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
            /*
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['edit'],
                'href'  => 'table=tl_spiel',
                'icon'  => 'edit.svg',
            ],
            */
            'edit' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['edit'],
            'href' => 'do=liga.begegnungserfassung',
            'icon' => 'editheader.svg',
            'button_callback' => function ($arrRow,
                                          $href,
                                          $label,
                                          $title,
                                          $icon,
                                          $attributes,
                                          $strTable,
                                          $arrRootIds,
                                          $arrChildRecordIds,
                                          $blnCircularReference,
                                          $strPrevious,
                                          $strNext) {
                $spiele = \Contao\SpielModel::findByPid($arrRow['id']);
                if ($spiele) {
                    return sprintf('<a href="contao/main.php?%s&rt=%s&id=%d" title="" class="edit">%s</a>',
                        'do=liga.begegnung&table=tl_spiel',
                        REQUEST_TOKEN,
                        $arrRow['id'],
                        //'<span style="width:6em;display:inline-block">bearbeiten</span>' //json_encode(func_get_args())
                        '<img src="system/themes/flexible/icons/edit.svg" width="12" height="16" alt="Begegnung bearbeiten">&nbsp;'
                    );
                }

                return sprintf('<a href="contao/main.php?%s&id=%d" title="" class="edit">%s</a>',
                    $href,
                    $arrRow['id'],
                    '<img src="system/themes/flexible/icons/edit.svg" width="12" height="16" alt="Begegnung erfassen">&nbsp;'
                );
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
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{title_legend},pid,home,away;{details_legend},spiel_tag,spiel_am,kommentar',
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
            //'flag'             => 11, // sort ascending
            'inputType' => 'select',
            'foreignKey' => 'tl_liga.name',
            'eval' => ['submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAktiveLigenForSelect'],
            'relation' => ['type' => 'belongsTo'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'home' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['home'],
            'filter' => true,
            'exclude' => true,
            'sorting' => true,
            //'flag'             => 11, // sort ascending
            'inputType' => 'select',
            'foreignKey' => 'tl_mannschaft.name',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50 clr', 'chosen' => true, 'includeBlankOption' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getMannschaftenForSelect'],
            'sql' => "int(10) NOT NULL default '0'",
        ],
        'away' => [
            'label' => &$GLOBALS['TL_LANG']['tl_begegnung']['away'],
            'filter' => true,
            'exclude' => true,
            'sorting' => true,
            'flag' => 11, // sort ascending
            'foreignKey' => 'tl_mannschaft.name',
            'inputType' => 'select',
            // 'mandatory' => false da "kein Gegner angegeben === Spielfrei"
            'eval' => ['mandatory' => false, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getMannschaftenForSelect'],
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
            'eval' => ['tl_class' => 'clr long', 'maxlength' => 255, 'rte' => false],
            'sql' => 'mediumtext NULL',
        ],
    ],
];

/* Bei Aufruf "nicht als child record */
if ('liga.begegnung' === \Input::get('do')) {
    $GLOBALS['TL_DCA']['tl_begegnung']['list']['sorting'] = [
        'mode' => 2,
        'flag' => 11, // sort ascending
        'fields' => ['pid', 'home', 'away'],
        'panelLayout' => 'sort,filter;search,limit',
        'disableGrouping' => false,
        'filter' => [
            ['pid IN (SELECT id FROM tl_liga WHERE aktiv=?)', '1']
        ]
    ];
}
