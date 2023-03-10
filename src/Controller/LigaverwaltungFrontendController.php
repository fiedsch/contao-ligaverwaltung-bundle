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

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\System;
// use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Exception;
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Helper\Spielplan;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_array;

/**
 * Handles the bundle's frontend routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class LigaverwaltungFrontendController
{
    /**
     * Spielplan als Ical.
     *
     * @param int $ligaid
     * @param int $mannschaftid
     *
     * @throws Exception
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
    public function icalAction(int $ligaid, int $mannschaftid = 0): Response
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
    public function jsonAction(int $ligaid, int $mannschaftid = 0): Response
    {
        $controller = new JsonController($ligaid, $mannschaftid);

        return $controller->run();
    }

    /**
     * Einbagemaske Begegnungserfassung.
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
     */
    public function begegnungDataEntryAction(int $begegnung): Response
    {
        $begegnungModel = BegegnungModel::findById($begegnung);

        if (!$begegnungModel) {
            throw new PageNotFoundException('Begegnung '.$begegnung.' nicht gefunden');
        }

        if ($begegnungModel->published) {
            throw new AccessDeniedException('Begegnung '.$begegnung.' ist bereits erfasst');
        }

        $appData = $begegnungModel->{DataEntrySaver::KEY_APP_DATA};

        if (!is_array($appData)) {
            $appData = [];
        }

        $requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        $appData['webserviceUrl'] = '/ligaverwaltung/begegnung_fe';
        $appData['requestToken'] = $requestToken;
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
     * @Route(
     *     "/ligaverwaltung/begegnung_fe/{begegnung}",
     *     name="begegnung_dataentry_save_fe",
     *     requirements={
     *       "begegnung": "[0-9]+"
     *     },
     *     methods={"POST"}
     *     )
     */
    public function begegnungDataSaveAction(Request $request, int $begegnung): Response
    {
        $requestData = json_decode($request->request->get('json_data'), true);

        return new Response(DataEntrySaver::handleDataEntryData($begegnung, $requestData));
    }
}
