<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_spieler'] = [

    'config' => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_mannschaft',
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'            => 'primary',
                'pid'           => 'index',
                'pid,member_id' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'                  => 4, // Displays the child records of a parent record
            'headerFields'          => ['name', 'spielort', 'liga'],
            // TODO(?): wird flag bei mode 4 nicht berücksichtigt?
            // Workarround: DESC als Teil des Feldnamens angeben
            'flag'                  => 1,
            'fields'                => ['teamcaptain DESC,co_teamcaptain DESC'],
            'panelLayout'           => '', // sort, search,filter etc. nicht anzeigen
            'child_record_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'listMemberCallback'],
            'child_record_class'    => 'no_padding',
            'disableGrouping'       => true,
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
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['copy'],
                'href'  => 'act=paste&amp;mode=copy',
                'icon'  => 'copy.svg',
            ],
            'cut'    => [
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.svg',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_spieler']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_spieler']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{member_legend},member_id;{details_legend},teamcaptain,co_teamcaptain,active,ersatzspieler,avatar',
    ],

    'fields' => [
        'id'          => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'         => [
            'foreignKey' => 'tl_mannschaft.name',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'      => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'member_id'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_spieler']['member_id'],
            'exclude'          => true,
            'search'           => true,
            'sorting'          => true,
            'inputType'        => 'select',
            'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getSpielerForSelect'],
            'eval'             => ['chosen' => true, 'includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50 wizard'],
            'wizard'           => [
                    ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'editMemberWizard']
            ],
            //'foreignKey'       => 'tl_member.CONCAT(lastname, ", ", firstname)',
            'foreignKey'       => 'tl_member.CONCAT(firstname, " ", lastname)',
            'relation'         => ['type' => 'hasOne', 'table' => 'tl_member', 'load' => 'eager'],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'teamcaptain' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spieler']['teamcaptain'],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'co_teamcaptain' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_spieler']['co_teamcaptain'],
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'active' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_spieler']['active'],
            'save_callback' => [['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'spielerSaveCallback']],
            'inputType'  => 'checkbox',
            'exclude'    => true,
            'search'     => false,
            'filter'     => true,
            'sorting'    => false,
            //'eval'       => ['tl_style'=>'w50'],
            'sql'        => "char(1) NOT NULL default '1'",
        ],
        'ersatzspieler' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_spieler']['ersatzspieler'],
            'inputType'  => 'checkbox',
            'exclude'    => true,
            'search'     => false,
            'filter'     => true,
            'sorting'    => false,
            //'eval'       => ['tl_style'=>'w50'],
            'sql'        => "char(1) NOT NULL default ''",
        ],

        'avatar' => [
            'label'  => &$GLOBALS['TL_LANG']['tl_spieler']['avatar'],
            'input_field_callback' => function(\Contao\DataContainer $dc) {
                $member_id = $dc->activeRecord->row()['member_id'];
                $member = \Contao\MemberModel::findById($member_id);
                $avatar = $member ? \Contao\FilesModel::findById($member->avatar)->path : null;
                return '<div class="widget">'
                    .'<h3>'
                    .'<label>'
                    .'<span class="invisible">Nur zur Information </span>'
                    .$GLOBALS['TL_LANG']['tl_spieler']['avatar'][0]
                    .'</label>'
                    .'</h3>'
                    .'<div>'
                    .'  <img src="'.$avatar.'" '
                    .  'height="150" alt="" class="gimage" '
                    .  'title="'.$member->firstname.' '.$member->lastname.'">'
                    .'</div>'
                    .'<p class="tl_help tl_tip" title="">'.$GLOBALS['TL_LANG']['tl_spieler']['avatar'][1].'</p>'
                    .'</div>' // .widget
                    ;
            }
        ],
    ],

];


