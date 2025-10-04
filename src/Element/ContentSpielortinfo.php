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

/**
 * Content element "Informationen zu einem Spielort".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\ContentElement;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;

/**
 * @property int $spielort
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
    public function compile(): void
    {
        $this->Template->spielort = SpielortModel::findById($this->spielort);
    }
}
