<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2025 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use function dirname;

/**
 * Configures the Ligaverwaltung bundle.
 *
 * @author Andreas Fieger
 */
class FiedschLigaverwaltungBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

}
