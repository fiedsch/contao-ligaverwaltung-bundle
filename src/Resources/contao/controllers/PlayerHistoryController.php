<?php

namespace Fiedsch\LigaverwaltungBundle;

use Symfony\Component\HttpFoundation\Response;
use Contao\SpielerModel;

class PlayerHistoryController
{
    /**
     * @var integer
     */
    protected $memberid;

    /**
     * PlayerHistoryController constructor.
     *
     * @param integer $memberid
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
        $template = new \BackendTemplate('be_spielerhistory');
        $template->history = $this->getHistory();
        return new Response($template->parse());
    }

    /**
     * @return array
     */
    protected  function getHistory()
    {
        $history = [];
        $spieler = SpielerModel::findBy(['member_id=?'], [$this->memberid], ['pid ASC']);
        if ($spieler) {
            foreach ($spieler as $sp) {
                $liga = $sp->getRelated('pid')->getRelated('liga');
                $history[] = [
                    'mannschaft' => $sp->getRelated('pid')->name,
                    'saison' => $liga->name . ' '. $liga->getRelated('saison')->name,
                ];
            }
        }
        return $history;
    }

}