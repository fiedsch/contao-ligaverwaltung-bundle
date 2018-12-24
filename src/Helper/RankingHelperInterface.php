<?php

declare(strict_types=1);

namespace Fiedsch\LigaverwaltungBundle\Helper;

interface RankingHelperInterface
{

    /**
     * @param string $score
     * @param int $ranking_model
     * @return int
     */
    public function getPunkte(string $score, int $ranking_model = 1): int;

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    public function compareResults(array $a, array $b): int;

}
