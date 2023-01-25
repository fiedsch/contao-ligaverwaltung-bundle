<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2023 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property int    $slot
 * @property string $spieltype
 * @property int    $home
 * @property int    $home2
 * @property int    $away
 * @property int    $away2
 * @property int    $score_home
 * @property int    $score_away
 *
 * @method static SpielModel|null findById($id, array $opt=array())
 * @method static Collection|SpielModel|null findByPid($id, array $opt=array())
 */
class SpielModel extends Model
{
    const TYPE_EINZEL = '1'; // FIXME: should this be 1 (not '1') with PHP 8?
    const TYPE_DOPPEL = '2'; // see above

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_spiel';

    /**
     * Ausgang des Spiels 0:0, 1:0 oder 0:1.
     *
     * @return array
     */
    public function getScore()
    {
        if ($this->score_home === $this->score_away) {
            return [0, 0];
        }

        return [
            $this->score_home > $this->score_away ? 1 : 0,
            $this->score_home > $this->score_away ? 0 : 1,
        ];
    }

    /**
     * Ausgang des Spiels in Legs.
     *
     * @return array
     */
    public function getLegs()
    {
        return [
            $this->score_home,
            $this->score_away,
        ];
    }
}
