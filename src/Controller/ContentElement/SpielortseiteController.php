<?php

declare(strict_types=1);

namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\StringUtil;
use Contao\System;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielortModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'spielortseite',
    category: 'ligaverwaltung',
    template: 'content_element/spielortseite'
)]
class SpielortseiteController extends AbstractContentElementController
{
    use TlModeTrait;

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['spielortseite'][0])->upper().' ###';

        $spielortModel = SpielortModel::findById($model->spielort);

        $template->subject = $spielortModel->name;
        if ($this->isBackend()) {
            return;
        }

        $this->addInfoToHead($spielortModel->name);

        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielortinfo';
        $contentModel->spielort = $spielortModel->id;
        $contentModel->headline = [
            'value' => 'Spielort',
            'unit'  => 'h1',
        ];
        $template->spielortinfo = Controller::getContentElement($contentModel);

        $saisons = StringUtil::deserialize($model->saison);
        $saison_lookup = [];
        $result = [];

        foreach ($saisons as $saison) {
            $saisonModel = SaisonModel::findById($saison);
            $saison_lookup[$saisonModel->id] = $saisonModel->name;

            $mannschaften = MannschaftModel::findBy(
                ['spielort=?', 'saison=?', 'active=?'],
                [$spielortModel->id, $saison, '1'],
                ['order' => 'name ASC']
            );
            foreach ($mannschaften ?? [] as $mannschaft) {
                $liga = LigaModel::findById($mannschaft->liga);
                $ligen_lookup[$liga?->id ?? 0] = $liga;

                $result[$saison][] = [
                    'link' => $mannschaft->getLinkedName(),
                    'liga' => $ligen_lookup[$mannschaft->liga]?->name,
                    'saison' => $saison_lookup[$liga->saison] ?? '',
                ];
            }
        }

        $template->mannschaften = $result;

    }

    protected function addInfoToHead(string $spielortName): void
    {
        $responseContext = System::getContainer()->get('contao.routing.response_context_accessor')->getResponseContext();
        $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
        $htmlHeadBag->setMetaDescription('Spielort '.$spielortName);
        $htmlHeadBag->setTitle($spielortName);
    }

}
