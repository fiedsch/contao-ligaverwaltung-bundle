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

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Contao\BackendTemplate;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Symfony\Component\HttpFoundation\Response;

class PlayerHistoryController
{
    /**
     * @var int
     */
    protected $memberid;

    /**
     * PlayerHistoryController constructor.
     *
     * @param int $memberid
     */
    public function __construct($memberid)
    {
        $this->memberid = $memberid;
    }

    /**
     * @return Response
     */
    public function run()
    {
        $template = new BackendTemplate('be_spielerhistory');
        $template->history = $this->getHistory();

        return new Response($template->parse());
    }

    /**
     * @return array
     */
    protected function getHistory()
    {
        $history = [];
        $spieler = SpielerModel::findBy(['member_id=?'], [$this->memberid], ['pid ASC']);
        if ($spieler) {
            foreach ($spieler as $sp) {
                $liga = $sp->getRelated('pid')->getRelated('liga');
                $history[] = [
                    'mannschaft' => $sp->getRelated('pid')->name,
                    'saison' => $liga->name.' '.$liga->getRelated('saison')->name,
                ];
            }
        }

        return $history;
    }
}
