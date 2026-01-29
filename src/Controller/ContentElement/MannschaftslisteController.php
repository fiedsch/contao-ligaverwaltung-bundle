<?php

declare(strict_types=1);

namespace Fiedsch\LigaverwaltungBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Template;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'mannschaftsliste',
    category: 'ligaverwaltung',
    template: 'content_element/mannschaftsliste'
)]

class MannschaftslisteController extends AbstractContentElementController
{

public function getResponse(Template $template, ContentModel $model, Request $request): Response
    {

        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(Template $template, ContentModel $model): void
    {

        $liga = LigaModel::findById($model->liga);

        if ($liga) {
            $subject = sprintf('%s %s %s',
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name
            );
        } else {
            $subject = sprintf('Liga mit der ID=%d existiert nicht mehr', $model->liga);
        }
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftsliste'][0])->upper()." $subject ###";

        if (!$liga) {
            return;
        }

        $mannschaften = MannschaftModel::findByLiga($model->liga, ['order' => 'name ASC']);

        if (!$mannschaften) {
            return;
        }

        $listitems = [];

        foreach ($mannschaften as $mannschaft) {
            if ('1' === $mannschaft->active) {
                $listitem = $mannschaft->getLinkedName();
                $listitems[] = $listitem;
            }
        }

        $template->listitems = $listitems;
    }
}
