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

use Fiedsch\LigaverwaltungBundle\IcalController;
use Fiedsch\LigaverwaltungBundle\JsonController; // for annotations!
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the bundle's frontend routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class LigaverwaltungFrontendController extends Controller
{
    /**
     * Spielplan als Ical.
     *
     * @param int $ligaid
     * @param int $mannschaftid
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @Route(
     *     "/ligaverwaltung/spielplan/ical/{ligaid}/{mannschaftid}",
     *     name="spielplan_ical",
     *     requirements={
     *       "ligaid":"\d+",
     *       "mannschaftid":"\d+"
     *     },
     *     defaults={
     *       "mannschaftid":"0"
     *     }
     * )
     */
    public function icalAction($ligaid, $mannschaftid = 0)
    {
        $controller = new IcalController($ligaid, $mannschaftid);

        return $controller->run();
    }

    /**
     * Spielplan als JSON.
     *
     * @param int $ligaid
     * @param int $mannschaftid
     *
     * @return JsonResponse
     *
     * @Route(
     *     "/ligaverwaltung/spielplan/json/{ligaid}/{mannschaftid}",
     *     name="spielplan_json",
     *     requirements={
     *       "ligaid":"\d+",
     *       "mannschaftid":"\d+"
     *     },
     *     defaults={
     *       "mannschaftid":"0"
     *     }
     * )
     */
    public function jsonAction($ligaid, $mannschaftid = 0)
    {
        $controller = new JsonController($ligaid, $mannschaftid);

        return $controller->run();
    }
}
