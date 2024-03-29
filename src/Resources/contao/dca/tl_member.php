<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2023 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\MemberModel;

$GLOBALS['TL_DCA']['tl_member']['list']['operations']['history'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['history'],
    'button_callback' => static function ($arrRow, $href, $label, $title, $icon, $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext) {
        $member = MemberModel::findById($arrRow['id']);

        return sprintf(
            '<a href="ligaverwaltung/player/history/%s?popup=1&amp;rt=%s"'
            .' title="" style="padding-left:3px"'
            .' onclick="Backend.openModalIframe({\'width\':768,\'title\':\'Spielerhistorie von %s\',\'url\':this.href});return false"'
            .'>'
            .'%s</a>',
            $arrRow['id'],
            REQUEST_TOKEN,
            $member->firstname.' '.$member->lastname,
            // getHtml(a, foo, c) setzt mit foo das alt-Attribut, wir benötigen aber das title-Attribut
            // das wir im dritten Parameter "manuell" setzen.
            Image::getHtml('diff.svg', $GLOBALS['TL_LANG']['tl_member']['spielerhistorie'][0], 'style="vertical-align:top" title="'.$GLOBALS['TL_LANG']['tl_member']['spielerhistorie'][0].'"')
        );
    },
];

// Nicht jeder Spieler hat eine E-Mail-Adresse: den Contao-Standard "ist Pflichtfeld" ändern

$GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['mandatory'] = false;

$GLOBALS['TL_DCA']['tl_member']['palettes']['default']
    = preg_replace('/;{address_legend/', ';{liga_legend},passnummer,avatar,anonymize;{address_legend', $GLOBALS['TL_DCA']['tl_member']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_member']['fields']['passnummer'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['passnummer'],
    'inputType' => 'text',
    'search' => true,
    'sorting' => true,
    'eval' => ['rgxp' => 'alnum', 'tl_class' => 'w50', 'maxlength' => 32, 'unique' => true],
    'sql' => "varchar(32) NOT NULL default ''",
    'load_callback' => [static function ($value, DataContainer $dc) {
        // "auto increment" passnummer for new records and consider special cases.
        // NOTE: if the member with the highest passnumber gets deleted their passnumber
        // will be assigned to the next new entry in tl_member -- this is something
        // which we consider an edge case and do not try to catch here.
        if ($value) {
            return $value;
        }
        // An existing record with empty passnummer? Then leave as is!
        if ($dc->activeRecord->tstamp > 0) {
            return $value;
        }
        // Do we have non numerical passummmer values? Then MAX() + 1 will not be appropriate!
        $res = Database::getInstance()->prepare("SELECT SUM(passnummer NOT REGEXP '^[0-9]+$') as n FROM tl_member WHERE passnummer <>''")->execute();

        if ($res->n > 0) {
            return $value;
        }
        $res = Database::getInstance()->prepare('SELECT MAX(passnummer * 1) as maxpass FROM tl_member')->execute();

        return $res->maxpass + 1;
    }],
];

$GLOBALS['TL_DCA']['tl_member']['fields']['anonymize'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['anonymize'],
    'inputType' => 'checkbox',
    'filter' => true,
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_member']['fields']['avatar'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['avatar'],
    'exclude' => true,
    'search' => false,
    'filter' => false,
    'inputType' => 'fileTree',
    'eval' => ['tl_class' => 'clr w50', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'jpg,png', 'icon' => 'pickfile.svg'],
    'sql' => 'blob NULL',
];

// remove fields we don't need/want

foreach (['company', 'country', 'state', 'fax', 'website', 'lang'] as $field) {
    $GLOBALS['TL_DCA']['tl_member']['palettes']['default']
      = preg_replace("/$field,*;*/", '', $GLOBALS['TL_DCA']['tl_member']['palettes']['default']);
}

// change tl_style so fields align nicely again
$GLOBALS['TL_DCA']['tl_member']['fields']['postal']['eval']['tl_class'] .= ' clr';

// make username case insensitive
// original definition
// $GLOBALS['TL_DCA']['tl_member']['fields']['username']['sql'] = 'varchar(64) COLLATE utf8_bin NULL';
// redefinition
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['sql'] = 'varchar(64) COLLATE utf8mb4_general_ci NULL';

// do not use 'filter' for these
foreach (['country', 'language', 'disable', 'login', 'city'] as $field) {
    $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['filter'] = false;
}

// do not use 'search' for these
foreach (['company', 'website', 'street'] as $field) {
    $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['search'] = false;
}

// do not use 'sorting' for these
foreach (['company', 'country', 'state'] as $field) {
    $GLOBALS['TL_DCA']['tl_member']['fields'][$field]['sorting'] = false;
}

// Make gender a filter field
$GLOBALS['TL_DCA']['tl_member']['fields']['gender']['filter'] = true;
