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

namespace Fiedsch\LigaverwaltungBundle\Entity;

use Contao\System;
use Fiedsch\LigaverwaltungBundle\Helper\RankingHelperInterface;
use function count;

/**
 * Class Begegnung.
 *
 * Begegnungen zweier Mannschaften bestehen aus Spielen einzelner Spieler gegeneinander.
 */
class Begegnung
{
    protected RankingHelperInterface $rankingHelper;

    protected array $spiele;

    /**
     * Begegnung constructor.
     */
    public function __construct()
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->rankingHelper = System::getContainer()->get('fiedsch_ligaverwaltung.rankinghelper');
    }

    public function addSpiel(Spiel $spiel): void
    {
        $this->spiele[] = $spiel;
    }

    /**
     * @return int
     */
    public function getLegsHome(): int
    {
        $result = 0;
        /** @var Spiel $spiel */
        foreach ($this->spiele as $spiel) {
            $result += $spiel->getLegsHome();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getLegsAway(): int
    {
        $result = 0;
        /** @var Spiel $spiel */
        foreach ($this->spiele as $spiel) {
            $result += $spiel->getLegsAway();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getSpieleHome(): int
    {
        $result = 0;
        /** @var Spiel $spiel */
        foreach ($this->spiele as $spiel) {
            $result += $spiel->getScoreHome();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getSpieleAway(): int
    {
        $result = 0;
        /** @var Spiel $spiel */
        foreach ($this->spiele as $spiel) {
            $result += $spiel->getScoreAway();
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getNumSpiele(): int
    {
        return count($this->spiele);
    }

    /**
     * @return bool
     */
    public function isGewonnenHome(): bool
    {
        return $this->rankingHelper::PUNKTE_GEWONNEN === $this->getPunkteHome();
    }

    /**
     * 3 Punkte bei Sieg, 1 bei unentschieden, 0 bei verloren.
     *
     * @return int
     */
    public function getPunkteHome(): int
    {
        $punkte_home = 0;
        $punkte_away = 0;

        foreach ($this->spiele as $spiel) {
            $punkte_home += $spiel->getLegsHome() > $spiel->getLegsAway() ? 1 : 0;
            $punkte_away += $spiel->getLegsHome() > $spiel->getLegsAway() ? 0 : 1;
        }

        if ($punkte_home === $punkte_away) {
            return $this->rankingHelper::PUNKTE_UNENTSCHIEDEN;
        }

        return $punkte_home > $punkte_away ? $this->rankingHelper::PUNKTE_GEWONNEN : $this->rankingHelper::PUNKTE_VERLOREN;
    }

    /**
     * @return bool
     */
    public function isGewonnenAway(): bool
    {
        return $this->rankingHelper::PUNKTE_GEWONNEN === $this->getPunkteAway();
    }

    /**
     * 3 Punkte bei Sieg, 1 bei unentschieden, 0 bei verloren.
     *
     * @return int
     */
    public function getPunkteAway(): int
    {
        $punkte_home = 0;
        $punkte_away = 0;

        foreach ($this->spiele as $spiel) {
            $punkte_home += $spiel->getLegsHome() > $spiel->getLegsAway() ? 1 : 0;
            $punkte_away += $spiel->getLegsHome() > $spiel->getLegsAway() ? 0 : 1;
        }

        if ($punkte_home === $punkte_away) {
            return $this->rankingHelper::PUNKTE_UNENTSCHIEDEN;
        }

        return $punkte_home > $punkte_away ? $this->rankingHelper::PUNKTE_VERLOREN : $this->rankingHelper::PUNKTE_GEWONNEN;
    }

    /**
     * @return bool
     */
    public function isUnentschieden(): bool
    {
        return $this->rankingHelper::PUNKTE_UNENTSCHIEDEN === $this->getPunkteHome();
    }

    /**
     * @return bool
     */
    public function isVerlorenHome(): bool
    {
        return $this->rankingHelper::PUNKTE_VERLOREN === $this->getPunkteHome();
    }

    /**
     * @return bool
     */
    public function isVerlorenAway(): bool
    {
        return $this->rankingHelper::PUNKTE_VERLOREN === $this->getPunkteAway();
    }
}
