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

use Fiedsch\LigaverwaltungBundle\PlayerHistoryController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller; // for annotations!
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the bundle's backend routes.
 *
 * @Route(defaults={"_scope" = "backend", "_token_check" = true})
 */
class LigaverwaltungBackendController extends Controller
{
    /**
     * Spielerhistorie.
     *
     * @param int $memberid
     *
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
