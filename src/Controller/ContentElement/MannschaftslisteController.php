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

namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'mannschaftsliste',
    category: 'ligaverwaltung',
    template: 'content_element/mannschaftsliste'
)]

class MannschaftslisteController extends AbstractContentElementController
{
    use TlModeTrait;

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftsliste'][0])->upper().' ###';

        $liga = LigaModel::findById($model->liga);
        if ($liga) {
            $template->subject = sprintf('%s %s %s',
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name
            );
        } else {
            $template->subject = sprintf('Liga mit der ID=%d %s', $model->liga, DCAHelper::DOES_NOT_EXIST);
        }

        if ($this->isBackend()) {
            return;
        }

        $listitems = [];

        $mannschaften = MannschaftModel::findByLiga($model->liga, ['order' => 'name ASC']);
        if (!$mannschaften) {
            $template->listitems = $listitems;
            return;
        }


        foreach ($mannschaften as $mannschaft) {
            if ($mannschaft->active) {
                $listitem = $mannschaft->getLinkedName();
                $listitems[] = $listitem;
            }
        }

        $template->listitems = $listitems;
    }
}
