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

/* Ligenliste */
$GLOBALS['TL_DCA']['tl_content']['palettes']['ligenliste'] = '{type_legend},type,headline;{auswahl_legend},verband,saison;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['verband'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['verband'],
    'exclude' => true,
    'foreignKey' => 'tl_verband.name',
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'inputType'        => 'checkboxWizard',
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => ''],
    'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAlleVerbaendeForSelect'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    //'sql'              => "blob NULL",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['saison'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['saison'],
    'exclude' => true,
    'foreignKey' => 'tl_saison.name',
    'inputType' => 'checkboxWizard',
    'eval' => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'w50 clr'],
    //'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAlleVerbaendeForSelect'],
    'sql' => 'blob NULL',
];
/* Mannschaftsliste */
$GLOBALS['TL_DCA']['tl_content']['palettes']['mannschaftsliste'] = '{type_legend},type,headline;{liga_legend},liga;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['liga'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['liga'],
    'exclude' => true,
    'foreignKey' => 'tl_liga.name',
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    //'inputType'        => 'checkboxWizard',
    //'eval'             => ['mandatory' => true, 'multiple'=>true, 'tl_class' => ''],
    'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAlleLigenForSelect'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

/* Spielerliste */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielerliste'] = '{type_legend},type,headline;{mannschaft_legend},mannschaft,showdetails;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['mannschaft'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['mannschaft'],
    'exclude' => true,
    'foreignKey' => 'tl_mannschaft.name',
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAlleMannschaftenForSelect'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['showdetails'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['showdetails'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];
/* Spielplan */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielplan'] = '{type_legend},type,headline;{filter_legend},liga,mannschaft;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
// liga und mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert

/* Spielortinfo */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielortinfo'] = '{type_legend},type,headline,spielort;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['spielort'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['spielort'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'foreignKey' => 'tl_spielort.name',
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
/* Spielbericht */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielbericht'] = '{type_legend},type,headline,begegnung;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['fields']['begegnung'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['begegnung'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true],
    'foreignKey' => 'tl_begegnung.id',
    'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getAlleBegegnungen'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
/* Ranking/Tabelle */
$GLOBALS['TL_DCA']['tl_content']['palettes']['ranking'] = '{type_legend},type,headline,liga,rankingtype;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'rankingtype';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['rankingtype_2'] = 'mannschaft';
// liga und mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert

$GLOBALS['TL_DCA']['tl_content']['fields']['rankingtype'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['rankingtype'],
    'exclude' => true,
    'options' => [1 => 'Mannschaften', 2 => 'Spieler'],
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'submitOnChange' => true],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

/* Mannschaftsseite */
$GLOBALS['TL_DCA']['tl_content']['palettes']['mannschaftsseite'] = '{config_legend},type'/*.',headline'*/.',mannschaft';
// mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert

/* Ranking/Tabelle der Highlights */
$GLOBALS['TL_DCA']['tl_content']['palettes']['highlightranking'] = '{type_legend},type,headline,liga,rankingtype,rankingfield;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
//$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'rankingtype';
//$GLOBALS['TL_DCA']['tl_content']['subpalettes']['rankingtype_2'] = 'mannschaft';
// ^^^ bereits durch $GLOBALS['TL_DCA']['tl_content']['palettes']['ranking'] gesetzt
//     liga und mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert
$GLOBALS['TL_DCA']['tl_content']['fields']['rankingfield'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['rankingfield'],
    'exclude' => true,
    'options' => [
        \Contao\HighlightModel::TYPE_ALL => 'Alle zusammen',
        //\Contao\HighlightModel::TYPE_171 => '180+171',
        \Contao\HighlightModel::TYPE_180 => '180+171',
        \Contao\HighlightModel::TYPE_SHORTLEG => 'Shortleg',
        \Contao\HighlightModel::TYPE_HIGHFINISH => 'Highfinish',
    ],
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

/* Mannschaftenübersicht: Mannschaft und deren Teamcaptains */
$GLOBALS['TL_DCA']['tl_content']['palettes']['mannschaftenuebersicht'] = '{type_legend},type,headline;{saison_legend},saison;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['ligen'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['ligen'],
    'inputType' => 'checkboxWizard',
    'filter' => false,
    'sorting' => false,
    // 'flag'        => 1, // Sort by initial letter ascending
    'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
    'foreignKey' => 'tl_liga.name',
    'eval' => ['multiple' => true, 'tl_class' => 'w50 clr'],
    'options_callback' => ['\Fiedsch\LigaverwaltungBundle\DCAHelper', 'getLigaForSelect'],
    'sql' => 'blob NULL',
];
/* Spielortseite */
$GLOBALS['TL_DCA']['tl_content']['palettes']['spielortseite'] = '{config_legend},type'/*.',headline'*/.',spielort,ligen';
// mannschaft bereits bei Mannschaftsliste bzw. Spielerliste definiert
