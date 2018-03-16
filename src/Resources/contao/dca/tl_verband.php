<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']  ['tl_verband'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'ctable'           => ['tl_liga'],
        'enableVersioning' => true,
        'onload_callback' => [
            ['tl_verband','checkPermission'], // FIXME experimental only
        ],
        'sql'              => [
            'keys' => [
                'id'   => 'primary',
                'name' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'         => 2,
            'fields'       => ['name'],
            'panelLayout'  => 'sort,filter;search,limit',
            'headerFields' => ['home'],
        ],
        'label'             => [
            'fields'         => ['name'],
            'format'         => '%s',
            'label_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'verbandLabelCallback'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_verband']['edit'],
                'href'  => 'table=tl_liga',
                'icon'  => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_mannschaft']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.svg',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_verband']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_verband']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_verband']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
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
            'label'     => &$GLOBALS['TL_LANG']['tl_verband']['name'],
            'sorting'   => true,
            'flag'      => 1, // Sort by initial letter ascending
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
    ],

];


class tl_verband extends Backend
{
    public function __construct()
    {
        parent::__construct();
        // \Contao\BackendUser::getInstance() // TODO: verwenden
        $this->import('BackendUser', 'User'); // TODO: Contao 4.4-way
    }

    public function checkPermission()
  {
      // do nothing => User has all rights
      // return;

      // User has access top IDs 1 and 2 only
      //$GLOBALS['TL_DCA']['tl_verband']['list']['sorting']['root'] = [1,2];
      //return;

      // Table is closed, new records can not be created
      //$GLOBALS['TL_DCA']['tl_verband']['config']['closed'] = true;
      //return;

      // ...

  }
}