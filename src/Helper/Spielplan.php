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

namespace Fiedsch\Ligaverwaltung\Helper;

use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Exception;

class Spielplan
{
    /**
     * @throws Exception
     */
    public static function getSpielplan(BegegnungModel $begegnungModel): array
    {
        $liga = $begegnungModel->getRelated('pid');

        return match ($liga->spielplan) {
            LigaModel::SPIELPLAN_16E => [
                ['home' => [0], 'away' => [0]],
                ['home' => [1], 'away' => [1]],
                ['home' => [2], 'away' => [2]],
                ['home' => [3], 'away' => [3]],
                ['home' => [0], 'away' => [1]],
                ['home' => [1], 'away' => [0]],
                ['home' => [2], 'away' => [3]],
                ['home' => [3], 'away' => [2]],
                ['home' => [2], 'away' => [0]],
                ['home' => [3], 'away' => [1]],
                ['home' => [1], 'away' => [2]],
                ['home' => [0], 'away' => [3]],
                ['home' => [3], 'away' => [0]],
                ['home' => [2], 'away' => [1]],
                ['home' => [0], 'away' => [2]],
                ['home' => [1], 'away' => [3]],
            ],
            LigaModel::SPIELPLAN_16E2D => [
                ['home' => [0], 'away' => [0]],
                ['home' => [1], 'away' => [1]],
                ['home' => [2], 'away' => [2]],
                ['home' => [3], 'away' => [3]],
                ['home' => [0], 'away' => [1]],
                ['home' => [1], 'away' => [0]],
                ['home' => [2], 'away' => [3]],
                ['home' => [3], 'away' => [2]],
                ['home' => [2], 'away' => [0]],
                ['home' => [3], 'away' => [1]],
                ['home' => [1], 'away' => [2]],
                ['home' => [0], 'away' => [3]],
                ['home' => [3], 'away' => [0]],
                ['home' => [2], 'away' => [1]],
                ['home' => [0], 'away' => [2]],
                ['home' => [1], 'away' => [3]],
                ['home' => [0, 2], 'away' => [1, 3]],
                ['home' => [1, 3], 'away' => [0, 2]],
            ],
            LigaModel::SPIELPLAN_16E4D => [
                ['home' => [0], 'away' => [0]],
                ['home' => [1], 'away' => [1]],
                ['home' => [2], 'away' => [2]],
                ['home' => [3], 'away' => [3]],
                ['home' => [0], 'away' => [1]],
                ['home' => [1], 'away' => [0]],
                ['home' => [2], 'away' => [3]],
                ['home' => [3], 'away' => [2]],
                ['home' => [2], 'away' => [0]],
                ['home' => [3], 'away' => [1]],
                ['home' => [1], 'away' => [2]],
                ['home' => [0], 'away' => [3]],
                ['home' => [3], 'away' => [0]],
                ['home' => [2], 'away' => [1]],
                ['home' => [0], 'away' => [2]],
                ['home' => [1], 'away' => [3]],
                ['home' => [0, 2], 'away' => [1, 3]],
                ['home' => [1, 3], 'away' => [0, 2]],
                ['home' => [0, 2], 'away' => [0, 2]],
                ['home' => [1, 3], 'away' => [1, 3]],
            ],
            LigaModel::SPIELPLAN_6E3D => [
                ['home' => [0], 'away' => [0]],
                ['home' => [1], 'away' => [1]],
                ['home' => [2], 'away' => [2]],
                ['home' => [3], 'away' => [3]],
                ['home' => [4], 'away' => [4]],
                ['home' => [5], 'away' => [5]],
                ['home' => [0, 1], 'away' => [0, 1]],
                ['home' => [2, 3], 'away' => [2, 3]],
                ['home' => [4, 5], 'away' => [4, 5]],
            ],
            LigaModel::SPIELPLAN_8E2D => [
                ['home' => [0], 'away' => [0]],
                ['home' => [1], 'away' => [1]],
                ['home' => [2], 'away' => [2]],
                ['home' => [3], 'away' => [3]],
                ['home' => [0], 'away' => [3]],
                ['home' => [1], 'away' => [2]],
                ['home' => [2], 'away' => [1]],
                ['home' => [3], 'away' => [0]],
                ['home' => [0, 2], 'away' => [0, 2]],
                ['home' => [1, 3], 'away' => [1, 3]],
            ],
            LigaModel::SPIELPLAN_4E1D => [
                ['home' => [0], 'away' => [0]],
                ['home' => [1], 'away' => [1]],
                ['home' => [0], 'away' => [1]],
                ['home' => [1], 'away' => [0]],
                ['home' => [0, 1], 'away' => [0, 1]],
            ],
            default => [
            ],
        };
    }

    public static function getSpielplanCss(int $spielplanCode): string
    {
        $allCss = 'input.spieler-score { width: 2rem; }';
        $baseTwoSkip = '.results-table tr.single:nth-child(2n+1 of .single) {border-top: 20px solid transparent !important;}';
        $baseFourSkip = '.results-table tr.single:nth-child(4n+1 of .single) {border-top: 20px solid transparent !important;}';
        $doublesSkip = '.results-table tr.double {border-top: 20px solid transparent !important;}';

        return match ($spielplanCode) {
            LigaModel::SPIELPLAN_16E => "/* SPIELPLAN_16E */ $baseFourSkip $allCss",
            LigaModel::SPIELPLAN_16E2D => "/* SPIELPLAN_16E2D */ $baseFourSkip $doublesSkip $allCss",
            LigaModel::SPIELPLAN_16E4D => "/* SPIELPLAN_16E4D */ $baseFourSkip $doublesSkip $allCss",
            LigaModel::SPIELPLAN_6E3D => "/* SPIELPLAN_6E3D */ $baseTwoSkip  $doublesSkip $allCss",
            LigaModel::SPIELPLAN_8E2D => "/* SPIELPLAN_8E2D */ $baseFourSkip $doublesSkip $allCss",
            default => "/* DEFAULT */ $baseFourSkip $allCss",
        };
    }
}
