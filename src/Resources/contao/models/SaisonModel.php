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
 * @method static SaisonModel|null findById($id, array $opt=array())
 */

class SaisonModel extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_saison";

}
