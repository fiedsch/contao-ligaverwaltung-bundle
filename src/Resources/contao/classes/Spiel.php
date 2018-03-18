<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

/**
 * Class Spiel
 * Spiel zweier Spieler gegeneinander (Teil einer Begegnung zweier Mannschaften)
 *
 * @package Fiedsch\Liga
 */
class Spiel
{

    /**
     * @var array
     */
    protected $data;

    /**
     * Spiel constructor.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Vgl. auch \SpielModel::getScore()
     *
     * @return int
     */
    public function getScoreHome()
    {
        if ($this->data['legs_home'] == $this->data['legs_away']) {
            return 0;
        }
        return $this->data['legs_home'] > $this->data['legs_away'] ? 1 : 0;
    }

    /**
     * Vgl. auch \SpielModel::getScore()
     *
     * @return int
     */
    public function getScoreAway()
    {
        if ($this->data['legs_home'] == $this->data['legs_away']) {
            return 0;
        }
        return $this->data['legs_home'] > $this->data['legs_away'] ? 0 : 1;
    }

    /**
     * @return int
     */
    public function getLegsHome()
    {
        return $this->data['legs_home'];
    }

    /**
     * @return int
     */
    public function getLegsAway()
    {
        return $this->data['legs_away'];
    }

    /**
     * @return int
     */
    public function getPunkteHome()
    {
        return $this->getPunkte(sprintf("%d:%d", $this->data['legs_home'], $this->data['legs_away']));
    }

    /**
     * Punkte für die Rangliste
     *
     * @return int
     */
    public function getPunkteAway()
    {
        return $this->getPunkte(sprintf("%d:%d", $this->data['legs_away'], $this->data['legs_home']));
    }

    /**
     * Das Punktesystem ist abhäng von der Liga, da nicht in allen Ligen die gleiche Anzahl
     * von Legs gespielt wird (best of X legs).
     * Hier: Universalmethode, da sich die Spielergebnisse der verschiedenen
     * Systeme gegenseitig ausschließen!
     * Bsp.: "3:1" => es wurde best of 5 gespielt, bei best of 3 kann es kein "3:1" geben!
     *
     * @param string $score
     * @return int
     */
    public function getPunkte($score)
    {
        switch ($score) {
            // mögliche Ergebnisse bei "best of 3"
            case '2:0':
                return 3;
                break;
            case '2:1':
                return 2;
                break;
            case '1:2':
                return 1;
                break;
            case '0:2':
                return 0;
                break;

            // mögliche Ergebnisse bei "best of 5"
            case '3:0':
                return 5;
                break;
            case '3:1':
                return 4;
                break;
            case '3:2':
                return 3;
                break;
            case '2:3':
                return 2;
                break;
            case '1:3':
                return 1;
                break;
            case '0:3':
                return 0;
                break;

            default:
                //\System::log("nicht vorgesehenes Spielergebnis ".$score, __METHOD__, TL_ERROR);
                return 0;
        }
    }


    /**
     * Compare results $a and $b for sorting, i.e. return -1, 0 or +1
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public static function compareSpielerResults($a, $b)
    {
        // $a und $b haben bei Speielern und Mannschafren die gleichen Felder und
        // es soll die gleiche Sortierlogik angewandt werden.
        return Begegnung::compareMannschaftResults($a, $b);
    }
}