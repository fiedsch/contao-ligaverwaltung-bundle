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

namespace Fiedsch\LigaverwaltungBundle;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\LigaModel;
use Contao\MannschaftModel;
use Patchwork\Utf8;

/**
 * Content element "Liste aller Mannschaften einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ContentMannschaftsliste extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftsliste';

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;

            $liga = LigaModel::findById($this->liga);
            $subject = sprintf('%s %s %s',
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name
            );
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['mannschaftsliste'][0])." $subject ###";

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the content element.
     */
    public function compile()
    {
        if (!$this->liga) {
            return;
        }
        $mannschaften = MannschaftModel::findByLiga($this->liga, ['order' => 'name ASC']);
        if (!$mannschaften) {
            return;
        }

        $listitems = [];
        foreach ($mannschaften as $mannschaft) {
            if ('1' === $mannschaft->active) {
                $listitem = $mannschaft->getLinkedName();
                $listitems[] = $listitem;
            }
        }

        $this->Template->listitems = $listitems;
    }
}
