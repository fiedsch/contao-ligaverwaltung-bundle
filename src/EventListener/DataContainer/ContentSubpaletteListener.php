<?php

namespace Fiedsch\Ligaverwaltung\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\ContentModel;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: 'tl_content', target: 'config.onload')]
class ContentSubpaletteListener
{
    public function __construct(private RequestStack $requestStack)
    {
    }
    public function __invoke(DataContainer|null $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== $this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }
        $element = ContentModel::findById($dc->id);

        if (null === $element || 'spielerliste' !== $element->type) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_content']['fields']['liga']['eval']['submitOnChange'] = true;
    }
}
