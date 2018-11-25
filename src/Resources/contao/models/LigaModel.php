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
 * @property string $name
 * @property string $saison
 * @property string $spielplan
 * @method static LigaModel|null findById($id, array $opt=array())
 */

class LigaModel extends Model
{

    const SPIELPLAN_16E2D = 2;
    const SPIELPLAN_16E4D = 4;
    const SPIELPLAN_6E3D  = 8;
    const SPIELPLAN_16E   = 16;

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_liga";

}
