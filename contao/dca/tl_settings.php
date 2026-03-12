<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016- Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{ligaverwaltung_legend},ligaverwaltung_exclusive_model,ligaverwaltung_ranking_model,teampage,spielberichtpage,show_verband_in_select';

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
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['spielberichtpage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['spielberichtpage'],
    'inputType' => 'pageTree',
    'exclude' => true,
    'search' => false,
    'filter' => false,
    'sorting' => false,
    'eval' => ['mandatory' => false, 'multiple' => false, 'fieldType' => 'radio', 'tl_class' => 'clr long'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['ligaverwaltung_ranking_model'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['ligaverwaltung_ranking_model'],
    'inputType' => 'select',
    'options' => [1 => 'nach gewonnenen Legs', 2 => 'nur gewonnenen/verloren'],
    'eval' => ['tl_class' => 'clr w50'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['show_verband_in_select'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['show_verband_in_select'],
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'clr w50'],
];

