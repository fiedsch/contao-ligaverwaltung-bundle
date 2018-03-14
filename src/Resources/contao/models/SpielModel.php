<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Contao;

class SpielModel extends Model
{

    const TYPE_EINZEL = 1;
    const TYPE_DOPPEL = 2;

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_spiel";

    /**
     * Ausgang des Spiels 0:0, 1:0 oder 0:1
     *
     * @return array
     */
    public function getScore()
    {
        if ($this->score_home == $this->score_away) {
            return [0, 0];
        }
        return [
            $this->score_home > $this->score_away ? 1 : 0,
            $this->score_home > $this->score_away ? 0 : 1
        ];
    }

    /**
     * Ausgang des Spiels in Legs
     *
     * @return array
     */
    public function getLegs()
    {
        return [
            $this->score_home,
            $this->score_away
        ];
    }
}