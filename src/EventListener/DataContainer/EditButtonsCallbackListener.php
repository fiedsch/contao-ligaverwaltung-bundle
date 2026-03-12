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
