<?php

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2018 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\System;
use Fiedsch\LigaverwaltungBundle\Helper\RankingHelperInterface;

/**
 * Class Begegnung.
 *
 * Begegnungen zweier Mannschaften bestehen aus Spielen einzelner Spieler gegeneinander.
 */
class Begegnung
{
    /**
     * @var RankingHelperInterface
     */
    protected $rankingHelper;

    /**
     * @var array
     */
    protected $spiele;

    /**
     * Begegnung constructor.
     */
    public function __construct()
    {
        $this->rankingHelper = System::getContainer()->get('fiedsch_ligaverwaltung.rankinghelper');
    }

    /**
     * @param Spiel $spiel
     */
    public function addSpiel(Spiel $spiel)
    {
        $this->spiele[] = $spiel;
    }

    /**
     * @return int
     */
    public function getLegsHome()
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
    public function getLegsAway()
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
    public function getSpieleHome()
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
    public function getSpieleAway()
    {
        $result = 0;
        /** @var Spiel $spiel */
        foreach ($this->spiele as $spiel) {
            $result += $spiel->getScoreAway();
        }

        return $result;
    }

    /**
     * 3 Punkte bei Sieg, 1 bei unentschieden, 0 bei verloren.
     *
     * @return int
     */
    public function getPunkteHome()
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
     * 3 Punkte bei Sieg, 1 bei unentschieden, 0 bei verloren.
     *
     * @return int
     */
    public function getPunkteAway()
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
     * @return int
     */
    public function getNumSpiele()
    {
        return \count($this->spiele);
    }

    /**
     * @return bool
     */
    public function isGewonnenHome()
    {
        return $this->rankingHelper::PUNKTE_GEWONNEN === $this->getPunkteHome();
    }

    /**
     * @return bool
     */
    public function isGewonnenAway()
    {
        return $this->rankingHelper::PUNKTE_GEWONNEN === $this->getPunkteAway();
    }

    /**
     * @return bool
     */
    public function isUnentschieden()
    {
        return $this->rankingHelper::PUNKTE_UNENTSCHIEDEN === $this->getPunkteHome();
    }

    /**
     * @return bool
     */
    public function isVerlorenHome()
    {
        return $this->rankingHelper::PUNKTE_VERLOREN === $this->getPunkteHome();
    }

    /**
     * @return bool
     */
    public function isVerlorenAway()
    {
        return $this->rankingHelper::PUNKTE_VERLOREN === $this->getPunkteAway();
    }

}
