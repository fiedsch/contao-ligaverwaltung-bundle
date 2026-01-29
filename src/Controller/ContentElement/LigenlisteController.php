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


namespace Fiedsch\LigaverwaltungBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(
    type: 'ligenliste',
    category: 'ligaverwaltung',
    template: 'content_element/ligenliste'
)]
class LigenlisteController extends AbstractContentElementController
{
    // public function __construct()
    // {
    // }

    // use TlModeTrait;


    public function getResponse(Template $template, ContentModel $model, Request $request): Response
    {

        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(Template $template, ContentModel $model): void
    {
        if (!$model->verband) {
            return;
        }
        $saisonIds = StringUtil::deserialize($model->saison);

        $saisonFilter = sprintf('saison IN (%s)', implode(',', $saisonIds));
        $ligen = LigaModel::findAll([
            'column' => ['pid=?', 'aktiv=?', $saisonFilter],
            'value' => [$model->verband, '1'],
            'order' => 'spielstaerke ASC',
        ]);

        if (null === $ligen) {
            return;
        }

        $listdata = [];
        foreach ($ligen as $liga) {
            $listitems['liga'] = sprintf('%s %s',
                $liga->name,
                $liga->getRelated('saison')->name
            );

            $mannschaften = MannschaftModel::findByLiga($liga->id, ['order' => 'name ASC']);

            $temp = [];
            /** @var Collection $mannschaften */
            foreach ($mannschaften ?? [] as $mannschaft) {
                if ($mannschaft->teampage ?? false) {
                    $teampage = PageModel::findById($mannschaft->teampage);
                    $temp[] = sprintf("<a href='%s'>%s</a>",
                        Controller::generateFrontendUrl($teampage->row()),
                        $mannschaft->name
                    );
                } else {
                    $temp[] = $mannschaft->name;
                }
            }
            $listitems['mannschaften'] = $temp;
            $listdata[] = $listitems;
        }

        $template->listitems = $listdata;
    }
}
