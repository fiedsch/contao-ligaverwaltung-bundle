<?php

namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Fiedsch\Ligaverwaltung\Model\SpielortModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'spielortinfo',
    category: 'ligaverwaltung',
    template: 'content_element/spielortinfo'
)]
class SpielortinfoController extends AbstractContentElementController
{

    use TlModeTrait;
    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {

        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### ' . u($GLOBALS['TL_LANG']['CTE']['spielortinfo'][0])->upper() . ' ###';

        $spielort = SpielortModel::findById($model->spielort);

        if ($this->isBackend()) {
            $template->details = sprintf('%s, %s', $spielort->name, $spielort->city);
        } else {
            $template->spielort = $spielort;
        }

    }
}
