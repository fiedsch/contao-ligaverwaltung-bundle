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
 * Content element "Liste aller Spieler einer Mannaschft".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\ContentElement;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;

class ContentSpielerliste extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielerliste';

    /**
     * Generate the content element.
     *
     * @throws \Exception
     */
    public function compile()
    {
        $allespieler = SpielerModel::findAll([
                'column' => ['pid=?', 'tl_spieler.active=?'],
                'value' => [$this->mannschaft, '1'],
                //'order'  => 'teamcaptain DESC, co_teamcaptain DESC, lastname ASC, firstname ASC',
                'order' => 'teamcaptain DESC, co_teamcaptain DESC, firstname ASC, lastname ASC',
            ]);

        if (!$allespieler) {
            $allespieler = [];
        }

        $listitems = [];
        foreach ($allespieler as $spieler) {
            $member = $spieler->getRelated('member_id');
            $listitems[] = ['member' => $member, 'spieler' => $spieler];
        }

        $this->Template->mannschaft = $this->mannschaft;
        $this->Template->listitems = $listitems;
        $this->Template->showdetails = $this->showdetails;
    }
}
