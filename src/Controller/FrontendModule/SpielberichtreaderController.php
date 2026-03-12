<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016- Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Ligaverwaltung\Controller\FrontendModule;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(category: 'ligaverwaltung', priority: 1)]
class SpielberichtreaderController extends AbstractFrontendModuleController
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
        $id = Input::get('id');
        // spielbericht.html?id=123 (id Parameter) vs.
        // spielbericht/id/123.html (auto_item)
        if (!$id) {
            $id = Input::get('auto_item');
        }

        if (empty($id)) {
            throw new PageNotFoundException('Erforderlicher Parameter id fehlt');
        }
        $begegnung = BegegnungModel::findById($id);

        if (!$begegnung) {
            throw new PageNotFoundException('Begegnung mit ID '.$id.' existiert nicht (mehr)');
        }
        $template->begegnung = $begegnung;

        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielbericht';
        $contentModel->begegnung = $begegnung->id;

        $template->spielbericht = Controller::getContentElement($contentModel);
    }
}
