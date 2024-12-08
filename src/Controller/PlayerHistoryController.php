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

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Contao\BackendTemplate;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ligaverwaltung/player/history/{memberid}', name: 'player_history', requirements: [ "memberid" => "[0-9]+"], defaults: ['_scope' => 'backend','token_check' => true])]
#[AsController]
class PlayerHistoryController
{
    public function __construct()
    {
    }

    /**
     * @throws Exception
     *
     * @return Response
     */
    public function __invoke(int $memberid): Response
    {
        $template = new BackendTemplate('be_spielerhistory');
        $template->history = $this->getHistory($memberid);

        return new Response($template->parse());
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    private function getHistory(int $memberid): array
    {
        $history = [];
        $spieler = SpielerModel::findBy(['member_id=?'], [$memberid], ['pid ASC']);

        if ($spieler) {
            foreach ($spieler as $sp) {
                $mannschaft = $sp->getRelated('pid');
                if (null == $mannschaft) { continue; } // skip if parent data has already been deleted
                // dd(['sp'=>$sp,'mannschaft'=>$mannschaft, 'liga'=>$sp->getRelated('pid')->getRelated('liga')]);
                $liga = $mannschaft->getRelated('liga');
                if (null == $liga) { continue; } // skip if parent data has already been deleted
                $saison = $liga->getRelated('saison');
                if (null == $saison) { continue; } // skip if parent data has already been deleted
                $history[] = [
                    'mannschaft' => $mannschaft->name,
                    'saison' => $liga->name.' '.$saison->name,
                ];
            }
        }

        return $history;
    }
}
