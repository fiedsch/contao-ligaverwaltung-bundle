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

namespace Fiedsch\Ligaverwaltung\Controller\Backend;

use Exception;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
class PlayerHistoryController
{
    public function __construct(private Environment $twig)
    {
    }
    // The following however works: (with no other changes)
    // private Environment $twig;
    // public function __construct()
    // {
    //     $this->twig = \Contao\System::getContainer()->get('twig');
    // }

    /**
     * @throws Exception
     *
     * @return Response
     */
    #[Route('%contao.backend.route_prefix%/ligaverwaltung/player/history/{memberid}', name: 'player_history', requirements: [ "memberid" => "[0-9]+"], defaults: ['_scope' => 'backend','token_check' => true])]
    public function __invoke(int $memberid): Response
    {

        $history = $this->getHistory($memberid);

        return new Response($this->twig->render('@Contao_FiedschLigaverwaltungBundle/backend/spielerhistory.html.twig', ['history' => $history]));
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
