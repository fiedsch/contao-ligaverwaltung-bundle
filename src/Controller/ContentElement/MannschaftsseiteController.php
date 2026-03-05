<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2026 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * TODO:
 * - mannschafts**seite** is not a proper name for a content element. It should rather be something like mannschaftsinfo
 * - Adding the name of the team to the head (see addInfoToHead()) should be done in the "mannschaftsseitenreader" module
 * - Same for the other CE that (typically) are displayed in a reader module
 */


namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\System;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'mannschaftsseite',
    category: 'ligaverwaltung',
    template: 'content_element/mannschaftsseite'
)]
class MannschaftsseiteController extends AbstractContentElementController
{
    use TlModeTrait;

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {

        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftsseite'][0])->upper().' ###';

        $mannschaftModel = MannschaftModel::findById($model->mannschaft);

        $template->subject = $mannschaftModel ? $mannschaftModel?->getFullName() : 'Mannschaft '.DCAHelper::DOES_NOT_EXIST;

        if ($this->isBackend()) {
            return;
        }

        if (!$mannschaftModel) {
            return;
        }

        $this->addInfoToHead($mannschaftModel->getFullName());

        // Spielortinfo
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielortinfo';
        $contentModel->spielort = $mannschaftModel->spielort;
        $contentModel->headline = null; // keine zusaätzliche Überschrift
        $template->spielortinfo = Controller::getContentElement($contentModel);

        // Spielerliste
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielerliste';
        $contentModel->mannschaft = $model->mannschaft;
        $contentModel->showdetails = 1;
        $contentModel->headline = [
            'value' => 'Spielerliste '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $template->spielerliste = Controller::getContentElement($contentModel);

        // Spielplan
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielplan';
        $contentModel->liga = $mannschaftModel->liga;
        $contentModel->mannschaft = $mannschaftModel->id;
        $contentModel->headline = [
            'value' => 'Spielplan '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $template->spielplan= Controller::getContentElement($contentModel);

        // Einzelspielerrangliste
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'ranking';
        $contentModel->liga = $mannschaftModel->liga;
        $contentModel->mannschaft = $mannschaftModel->id;
        $contentModel->rankingtype = RankingController::RANKING_TYPE_SPIELER;
        $contentModel->headline = [
            'value' => 'Einzelspieler Ranking '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $template->ranking = Controller::getContentElement($contentModel);

        // Highlights
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'highlightranking';
        $contentModel->liga = $mannschaftModel->liga;
        $contentModel->rankingtype = RankingController::RANKING_TYPE_SPIELER;
        $contentModel->rankingfield = 99; // alle zusammen
        $contentModel->mannschaft = $mannschaftModel->id;
        $contentModel->headline = [
            'value' => 'Highlights '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $template->highlightranking = Controller::getContentElement($contentModel);

        $template->mannschaft_name = $mannschaftModel->name;
        $liga = LigaModel::findById($mannschaftModel->liga);
        $template->liga = $liga?->name;
        $template->saison = SaisonModel::findById($liga?->saison)?->name;

    }

    protected function addInfoToHead(string $mannschaftName): void
    {
        $responseContext = System::getContainer()->get('contao.routing.response_context_accessor')->getResponseContext();
        $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
        $htmlHeadBag->setMetaDescription('Alles zur Mannschaft '.$mannschaftName);
        $htmlHeadBag->setTitle($mannschaftName);
    }
}
