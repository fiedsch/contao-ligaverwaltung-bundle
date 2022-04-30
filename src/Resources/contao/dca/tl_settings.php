<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2021 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

//$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{ligaverwaltung_legend},ligaverwaltung_exclusive_model,ligaverwaltung_ranking_model,ligaverwaltung_ranking_model_ties,teampage,spielberichtpage';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{ligaverwaltung_legend},ligaverwaltung_exclusive_model,ligaverwaltung_ranking_model,teampage,spielberichtpage,ligaverwaltung_dataentry_compatibility_mode';

$GLOBALS['TL_DCA']['tl_settings']['fields']['ligaverwaltung_exclusive_model'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_exclusive_model'],
    'inputType' => 'select',
    'options' => [1 => '(in einer Mannschaft) je Saison', 2 => '(in einer Mannschaft) je Liga'],
    'eval' => ['tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['teampage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['teampage'],
    'inputType' => 'pageTree',
    'exclude' => true,
    'search' => false,
    'filter' => false,
    'sorting' => false,
    'eval' => ['mandatory' => false, 'multiple' => false, 'fieldType' => 'radio', 'tl_class' => 'clr long'],
    //'sql'        => "blob NULL",
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['spielberichtpage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['spielberichtpage'],
    'inputType' => 'pageTree',
    'exclude' => true,
    'search' => false,
    'filter' => false,
    'sorting' => false,
    'eval' => ['mandatory' => false, 'multiple' => false, 'fieldType' => 'radio', 'tl_class' => 'clr long'],
    //'sql'        => "blob NULL",
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['ligaverwaltung_ranking_model'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_ranking_model'],
    'inputType' => 'select',
    'options' => [1 => 'nach gewonnenen Legs', 2 => 'nur gewonnenen/verloren'],
    'eval' => ['tl_class' => 'clr w50'],
];
/*
$GLOBALS['TL_DCA']['tl_settings']['fields']['ligaverwaltung_ranking_model_ties'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_ranking_model_ties'],
    'inputType' => 'select',
    'options' => [1 => 'nach Differenzen', 2 => 'nach absoluten Werten'],
    'eval' => ['tl_class' => 'w50'],
];
*/

$GLOBALS['TL_DCA']['tl_settings']['fields']['ligaverwaltung_dataentry_compatibility_mode'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_dataentry_compatibility_mode'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'long'],
];
