<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

/**
 * Class Begegnung
 *
 * Begegnungen zweier Mannschaften bestehen aus Spielen einzelner Spieler gegeneinander.
 *
 * @package Fiedsch\Liga
 */
class Begegnung
{

    const PUNKTE_GEWONNEN      = 3;
    const PUNKTE_UNENTSCHIEDEN = 1;
    const PUNKTE_VERLOREN      = 0;

    /**
     * @var array
     */
    protected $spiele;

    /**
     * Begegnung constructor.
     */
    public function __construct()
    {
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
     * 3 Punkte bei Sieg, 1 bei unentschieden, 0 bei verloren
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
        if ($punkte_home == $punkte_away) {
            return self::PUNKTE_UNENTSCHIEDEN;
        }
        return $punkte_home > $punkte_away ? self::PUNKTE_GEWONNEN : self::PUNKTE_VERLOREN;

    }

    /**
     * 3 Punkte bei Sieg, 1 bei unentschieden, 0 bei verloren
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
        if ($punkte_home == $punkte_away) {
            return self::PUNKTE_UNENTSCHIEDEN;
        }
        return $punkte_home > $punkte_away ? self::PUNKTE_VERLOREN : self::PUNKTE_GEWONNEN;
    }

    /**
     * @return int
     */
    public function getNumSpiele()
    {
        return count($this->spiele);
    }


    /**
     * @return bool
     */
    public function isGewonnenHome()
    {
        return $this->getPunkteHome() == self::PUNKTE_GEWONNEN;
    }

    /**
     * @return bool
     */
    public function isGewonnenAway()
    {
        return $this->getPunkteAway() == self::PUNKTE_GEWONNEN;
    }

    /**
     * @return bool
     */
    public function isUnentschieden()
    {
        return $this->getPunkteHome() == self::PUNKTE_UNENTSCHIEDEN;
    }

    /**
     * @return bool
     */
    public function isVerlorenHome()
    {
        return $this->getPunkteHome() == self::PUNKTE_VERLOREN;
    }

    /**
     * @return bool
     */
    public function isVerlorenAway()
    {
        return $this->getPunkteAway() == self::PUNKTE_VERLOREN;
    }

    /**
     * Compare results $a and $b for sorting, i.e. return -1, 0 or +1
     *
     * @param array $a
     * @param array $b
     */
    public static function compareMannschaftResults($a, $b)
    {
        // Bei Punktegleichstand ...
        if ($a['punkte_self'] == $b['punkte_self']) {
            // ... nach eigenen gewonnenen Spielen. Sind diese auch gleich, ...
            if ($a['spiele_self'] == $b['spiele_self']) {
                // ... dann nach Legs
                if ($a['legs_self'] == $b['legs_self']) {
                    // Immer noch gleich, dann nach Legdifferenz
                    return ($b['legs_self']-$b['legs_other'] <=> $a['legs_self']-$a['legs_other']);
                }
                return $a['legs_self'] < $b['legs_self'] ? +1 : -1;
            }
            return $a['spiele_self'] < $b['spiele_self'] ? +1 : -1;
        }
        return $a['punkte_self'] < $b['punkte_self'] ? +1 : -1;
    }
}