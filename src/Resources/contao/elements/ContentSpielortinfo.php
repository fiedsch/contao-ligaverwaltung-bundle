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

/**
 * Content element "Informationen zu einem Spielort".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\ContentElement;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;

/**
 * @property integer $spielort
 */
class ContentSpielortinfo extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielortinfo';

    /**
     * Generate the content element.
     */
    public function compile()
    {
        $this->Template->spielort = SpielortModel::findById($this->spielort);
    }
}
