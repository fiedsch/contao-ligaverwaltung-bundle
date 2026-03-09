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
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Contao\System;

System::loadLanguageFile('default');

$GLOBALS['TL_DCA']['tl_spieler'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_mannschaft',
        'switchToEdit' => true,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'pid,member_id' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'headerFields' => ['name', 'spielort', 'liga'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['teamcaptain DESC,co_teamcaptain DESC'],
            'panelLayout' => '', // sort, search,filter etc. nicht anzeigen
            'child_record_callback' => [DCAHelper::class, 'listMemberCallback'],
            'child_record_class' => 'no_padding',
            'disableGrouping' => true,
            //'defaultSearchField' => '...' // TODO wir bräuchten hier etwas dynamisches
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
            'cut',
            'delete',
            'toggle',
            'show',
        ],
    ],

    'palettes' => [
        'default' => '{member_legend},member_id;{details_legend},teamcaptain,co_teamcaptain,active,ersatzspieler,jugendlich,haspaid',
    ],

    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'pid' => [
            'foreignKey' => 'tl_mannschaft.name',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'sql' => ['type' => 'integer', 'default' => 0],
        ],
        'member_id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spieler']['member_id'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'select',
            'options_callback' => [DCAHelper::class, 'getSpielerForSelect'],
            'eval' => ['chosen' => true, 'includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard'],
            'wizard' => [
                [DCAHelper::class, 'editMemberWizard'],
            ],
            //'foreignKey'       => 'tl_member.CONCAT(lastname, ", ", firstname)',
            'foreignKey' => 'tl_member.CONCAT(firstname, " ", lastname)',
            'relation' => ['type' => 'hasOne', 'table' => 'tl_member', 'load' => 'eager'],
            'sql' => ['type' => 'integer', 'default' => null, 'notnull' => false],
        ],
        'teamcaptain' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spieler']['teamcaptain'],
            'inputType' => 'checkbox',
            'sql' => ['type' => 'boolean', 'default' => false]
        ],
        'co_teamcaptain' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spieler']['co_teamcaptain'],
            'inputType' => 'checkbox',
            'sql' => ['type' => 'boolean', 'default' => false]
        ],
        'active' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spieler']['active'],
            'save_callback' => [[DCAHelper::class, 'spielerSaveCallback']],
            'inputType' => 'checkbox',
            'exclude' => true,
            'search' => false,
            'filter' => true,
            'sorting' => false,
            'toggle' => true,
            //'eval'       => ['tl_style'=>'w50'],
            'sql' => ['type' => 'boolean', 'default' => false]
        ],
        'ersatzspieler' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spieler']['ersatzspieler'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'search' => false,
            'filter' => true,
            'sorting' => false,
            //'eval'       => ['tl_style'=>'w50'],
            'sql' => ['type' => 'boolean', 'default' => false]
        ],
        'jugendlich' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spieler']['jugendlich'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'search' => false,
            'filter' => true,
            'sorting' => false,
            //'eval'       => ['tl_style'=>'w50'],
            'sql' => ['type' => 'boolean', 'default' => false]
        ],
        'haspaid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_spieler']['haspaid'],
            'inputType' => 'checkbox',
            'filter' => true,
            'eval' => ['tl_class' => 'w50  m12'],
            'sql' => ['type' => 'boolean', 'default' => false]
        ],

    ],
];
