<?php

declare(strict_types=1);

namespace Fiedsch\Ligaverwaltung\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

#[AsCallback(table: 'tl_begegnung', target: 'edit.buttons')]

class EditButtonsCallbackListener
{
    public function __invoke(array $buttons, DataContainer $dc): array
    {
        // Remove buttons that don't make sense here
        // unset($buttons['saveNclose']);
        unset($buttons['saveNcreate']);
        unset($buttons['saveNduplicate']);

        return $buttons;
    }
}
