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
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'ligaverwaltung', priority: 1)]
class MannschaftsseitenreaderController extends AbstractFrontendModuleController
{
    public function __construct() {}

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
        // mannschaft.html?id=123 (id Parameter) vs.
        // mannschaft/123.html (auto_item)
        // $id = $request->request->get('id'); // using Symfony's Request class
        $id = Input::get('id'); // using Contao's class
        if (!$id) {
            $id = Input::get('auto_item');
        }

        if (empty($id)) {
            throw new PageNotFoundException('Erforderlicher Parameter id fehlt');
        }
        $mannschaft = MannschaftModel::findById($id);

        if (!$mannschaft || !$mannschaft->active) {
            throw new PageNotFoundException('Mannschaft mit ID '.$id.' existiert nicht, oder ist nicht mehr aktiv');
        }

        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'mannschaftsseite';
        $contentModel->mannschaft = $mannschaft->id;
        $template->mannschaftsseite = Controller::getContentElement($contentModel);
    }

}
