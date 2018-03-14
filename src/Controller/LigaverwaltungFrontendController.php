<?php // -*- coding: utf-8 -*-

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // for annotations!
use Symfony\Component\HttpFoundation\Response;
use Fiedsch\LigaverwaltungBundle\IcalController;


/**
 * Handles the bundle's frontend routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class LigaverwaltungFrontendController extends Controller
{
    /**
     * Spielplan als Ical
     *
     * @param integer $ligaid
     * @param integer $mannschaftid
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

}