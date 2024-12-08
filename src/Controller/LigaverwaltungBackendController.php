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

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\System;
use Exception;
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Helper\Spielplan;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function is_array;

#[Route('/ligaverwaltung/begegnung', defaults: ['_scope' => 'backend', 'token_check' => true ])]
class LigaverwaltungBackendController //extends AbstractBackendController
{
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Einbagemaske Begegnungserfassung.
     *
     * TODO: sollte entfernen werden, sobald die Eingabemaske über ein Widget fertig ist!
     *
     * @param int $begegnung
     *
     * @throws Exception
     *
     * @return Response
     */
    #[Route('/{begegnung}', name: 'begegnung_dataentry_form', requirements: ['begegnung' => '\d+'], methods: [Request::METHOD_GET])]
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
        //$requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        $requestToken = $this->container->get('contao.csrf.token_manager')->getDefaultTokenValue();
        $appData['webserviceUrl'] = '/ligaverwaltung/begegnung';
        $appData['requestToken'] = $requestToken;
        $appData['begegnungId'] = $begegnung;
        $appData['numSlots'] = 8;
        $appData['spielplanCss'] = Spielplan::getSpielplanCss($begegnungModel->getRelated('pid')->spielplan);
        $appData = DataEntrySaver::augment($appData);

        $data = [
            'headline' => $begegnungModel->getLabel(),
            'app_data' => $appData,
        ];
        /** @var \Twig\Environment $twig */
        //$twig = System::getContainer()->get('twig');
        $twig = $this->container->get('twig');
        $template = '@FiedschLigaverwaltung/begegnung_dataentry_be.html.twig';

        //return new Response($twig->render($template, $data));
        return $this->render($template, $data);
    }

    /**
     * Datenverarbeitung Begegnungserfassung.
     */
    #[Route('/{begegnung}', name: 'begegnung_dataentry_save', requirements: ['begegnung' => '\d+'], methods: [Request::METHOD_POST])]
    public function begegnungDataSaveAction(Request $request, int $begegnung): Response
    {
        $requestData = json_decode($request->request->get('json_data'), true);

        return new Response(DataEntrySaver::handleDataEntryData($begegnung, $requestData));
    }
}
