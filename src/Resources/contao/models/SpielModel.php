<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Contao;

/**
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property integer $slot
 * @property integer $spieltype
 * @property integer $home
 * @property integer $home2
 * @property integer $away
 * @property integer $away2
 * @property integer $score_home
 * @property integer $score_away
 * @method static SpielModel|null findById($id, array $opt=array())
 * @method static Model\Collection|SpielModel[]|SpielModel|null findByPid($id, array $opt=array())
 */

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