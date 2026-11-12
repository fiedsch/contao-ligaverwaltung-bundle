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

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\ModuleModel;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\Input;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\System;
use Fiedsch\Ligaverwaltung\Callback\BegegnungDataEntryForm;
use Fiedsch\Ligaverwaltung\Helper\DataEntrySaver;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Fiedsch\Ligaverwaltung\Helper\FrontendHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

#[AsFrontendModule(
    category: 'ligaverwaltung',
    template: 'frontend_module/spielberichterfassung',
    priority: 1
)]
class SpielberichtErfassungController extends AbstractFrontendModuleController
{
    use TlModeTrait;

    public function __construct(protected FrontendHelper $frontendHelper)
    {
    }

    /**
     * @throws Exception
     */
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $this->handlePostData($request);

        $this->setData($template, $model);

        return $template->getResponse();
    }

    /**
     * @throws Exception
     */
    private function setData(FragmentTemplate $template, ModuleModel $model): void
    {
        if ($this->isBackend()) {
            return;
        }

        $id = Input::get('id');
        // spielbericht-erfassung.html?id=123 (id Parameter) vs.
        // spielbericht-erfassung/123.html (auto_item)
        if (!$id) {
            $id = Input::get('auto_item');
        }
        if (empty($id)) {
            throw new PageNotFoundException('Erforderlicher Parameter id fehlt');
        }
        $id = (int)$id;

        $begegnung = BegegnungModel::findById($id);
        if (null== $begegnung) {
            throw new PageNotFoundException('die angegebene Begegnung existiert nicht');
        }
        if (null === $begegnung) {
            $template->subject = sprintf('Begegnung mit der ID %d existiert nicht mehr', $id);
            $template->begegnunglabel = $template->subject;
            $template->published = false;
            $template->begegnung = null;
            return;
        }

        $template->begegnung = $begegnung;

        $form = System::getContainer()->get(BegegnungDataEntryForm::class);
        $app_data = $form->getData($begegnung);
        $template->app_data = $app_data;
    }

    protected function handlePostData(Request $request): void
    {
        $data = $request->request->all();
        if (!empty($data)) {
            // TODO: save $data after checking
            //  - permissions
            //  - data consistency
            //  - data integrity (e.g. $data['begegnungId'] must be equal to $id from the URL for which we testet the access rights above)
            //  - etc.
            $data = json_decode($data['vue_app'], true) ?? [];
            $data = DataEntrySaver::augment($data);
            $data = DataEntrySaver::fixInputEncoding($data);
            DataEntrySaver::handleDataEntryData((int)$data['begegnungId'], $data);
        }
    }

}
