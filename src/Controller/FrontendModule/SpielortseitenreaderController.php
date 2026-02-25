<?php

namespace Fiedsch\Ligaverwaltung\Controller\FrontendModule;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielortModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'ligaverwaltung', priority: 1)]
class SpielortseitenreaderController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $this->setData($template, $model, $request);

        return $template->getResponse();
    }

    /**
     * @throws PageNotFoundException
     */
    private function setData(FragmentTemplate $template, ModuleModel $model, Request $request): void
    {
        $spielortId = Input::get('o');
        $saisonId = Input::get('s');

        if (empty($spielortId||empty($saisonId))) {
            throw new PageNotFoundException('Erforderlicher Parameter fehlt');
        }
        $spielort = SpielortModel::findById($spielortId);
        $saison = SaisonModel::findById($saisonId);
        if (!$spielort) {
            throw new PageNotFoundException('Spielort mit ID '.$spielortId.' existiert nicht (mehr)');
        }
        if (!$saison) {
            throw new PageNotFoundException('Saison mit ID '.$saisonId.' existiert nicht (mehr)');
        }

        $template->spielort = $spielort;
        $template->saison = $saison;

        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielortseite';
        $contentModel->spielort = $spielort->id;
        $contentModel->saison = \serialize([$saison->id]);
        $template->spielortseite = Controller::getContentElement($contentModel);
    }

}
