<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2021 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\System;
use Exception;
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Helper\Spielplan;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles the bundle's frontend routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class LigaverwaltungFrontendController extends AbstractController
{
    /**
     * Spielplan als Ical.
     *
     * @param int $ligaid
     * @param int $mannschaftid
     *
     * @throws Exception
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
    public function icalAction($ligaid, $mannschaftid = 0): Response
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
    public function jsonAction($ligaid, $mannschaftid = 0): Response
    {
        $controller = new JsonController($ligaid, $mannschaftid);

        return $controller->run();
    }

    /**
     * Einbagemaske Begegnungserfassung.
     *
     * @param int $begegnung
     *
     * @throws Exception
     *
     * @Route(
     *     "/ligaverwaltung/begegnung_fe/{begegnung}",
     *     name="begegnung_dataentry_form_fe",
     *     requirements={
     *       "begegnung": "[0-9]+"
     *     },
     *     methods={"GET"}
     * )
     * @Template("@FiedschLigaverwaltung/begegnung_dataentry.html.twig")
     */
    public function begegnungDataEntryAction($begegnung): Response
    {
        $begegnungModel = BegegnungModel::findById($begegnung);

        if (!$begegnungModel) {
            throw new PageNotFoundException('Begegnung '.$begegnung.' nicht gefunden');
        }

        if ($begegnungModel->published) {
            throw new AccessDeniedException('Begegnung '.$begegnung.' ist bereits erfasst');
        }

        $appData = $begegnungModel->{DataEntrySaver::KEY_APP_DATA};

        if (!\is_array($appData)) {
            $appData = [];
        }
        $appData['webserviceUrl'] = '/ligaverwaltung/begegnung_fe';
        $appData['requestToken'] = REQUEST_TOKEN;
        $appData['begegnungId'] = $begegnung;
        $appData['numSlots'] = 8;
        $appData['spielplanCss'] = Spielplan::getSpielplanCss($begegnungModel->getRelated('pid')->spielplan);
        $appData = DataEntrySaver::augment($appData);

        $data = [
            'headline' => $begegnungModel->getLabel(),
            'app_data' => $appData,
        ];
        $twig = System::getContainer()->get('twig');

        // $template = '@FiedschLigaverwaltung/begegnung_dataentry_vue.html.twig';
        // $feTemplate = new FrontendTemplate('fe_page');
        // $feTemplate->main = $twig->render($template, $data);
        // return $feTemplate->getResponse();

        $template = '@FiedschLigaverwaltung/begegnung_dataentry_fe.html.twig';

        return new Response($twig->render($template, $data));
    }

    /**
     * Datenverarbeitung Begegnungserfassung.
     *
     * @param int $begegnung
     *
     * @Route(
     *     "/ligaverwaltung/begegnung_fe/{begegnung}",
     *     name="begegnung_dataentry_save_fe",
     *     requirements={
     *       "begegnung": "[0-9]+"
     *     },
     *     methods={"POST"}
     *     )
     */
    public function begegnungDataSaveAction(Request $request, $begegnung): Response
    {
        $requestData = json_decode($request->request->get('json_data'), true);

        return new Response(DataEntrySaver::handleDataEntryData((int) $begegnung, $requestData));
    }
}
