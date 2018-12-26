<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2018 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Helper;

interface RankingHelperInterface
{
    /**
     * @param string $score
     * @param int    $ranking_model
     *
     * @return int
     */
    public function getPunkte(string $score, int $ranking_model = 1): int;

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public function compareResults(array $a, array $b): int;
}
