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

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\System;
use Exception;
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Helper\Spielplan;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_array;

/**
 * Handles the bundle's backend routes.
 *
 * @Route(defaults={"_scope" = "backend", "_token_check" = true})
 */
class LigaverwaltungBackendController
{
    /**
     * Spielerhistorie.
     *
     * @param int $memberid
     *
     * @throws Exception
     *
     * @return Response
     *
     * @Route(
     *     "/ligaverwaltung/player/history/{memberid}",
     *     name="player_history",
     *     requirements={
     *       "memberid": "[0-9]+"
     *     }
     * )
     */
    public function playerhistoryAction(int $memberid): Response
    {
        $controller = new PlayerHistoryController($memberid);

        return $controller->run();
    }

    /**
     * Einbagemaske Begegnungserfassung.
     *
     * @param int $begegnung
     *
     * @throws Exception
     *
     * @return Response
     *
     * @Route(
     *     "/ligaverwaltung/begegnung/{begegnung}",
     *     name="begegnung_dataentry_form",
     *     requirements={
     *       "begegnung": "[0-9]+"
     *     },
     *     defaults = {
     *         "_backend_module" = "liga.begegnung",
     *     },
     *     methods={"GET"}
     * )
     */
    public function begegnungDataEntryAction(int $begegnung): Response
    {
        $begegnungModel = BegegnungModel::findById($begegnung);

        if (!$begegnungModel) {
            throw new RedirectResponseException('/contao?do=liga.begegnung');
            // ODER: 'contao/main.php?act=error' ?
        }

        $appData = $begegnungModel->{DataEntrySaver::KEY_APP_DATA};

        if (!is_array($appData)) {
            $appData = [];
        }
        $appData['webserviceUrl'] = '/ligaverwaltung/begegnung';
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
        $template = '@FiedschLigaverwaltung/begegnung_dataentry_be.html.twig';

        return new Response($twig->render($template, $data));
    }

    /**
     * Datenverarbeitung Begegnungserfassung.
     *
     * @Route(
     *     "/ligaverwaltung/begegnung/{begegnung}",
     *     name="begegnung_dataentry_save",
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
