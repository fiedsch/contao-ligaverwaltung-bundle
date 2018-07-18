<?php
/** @noinspection PhpUnusedAliasInspection */

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // for annotations!
use Symfony\Component\HttpFoundation\Response;
use Fiedsch\LigaverwaltungBundle\PlayerHistoryController;


/**
 * Handles the bundle's backend routes.
 *
 * @Route(defaults={"_scope" = "backend", "_token_check" = true})
 */
class LigaverwaltungBackendController extends Controller
{
    /**
     * Spielerhistorie
     *
     * @param integer $memberid
     * @return Response
     *
     * @Route("/ligaverwaltung/player/history/{memberid}", name="player_history")
     */
    public function playerhistoryAction($memberid)
    {
        $controller = new PlayerHistoryController($memberid);
        return $controller->run();
    }

}