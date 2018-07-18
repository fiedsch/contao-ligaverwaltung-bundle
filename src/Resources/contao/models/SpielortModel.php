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
 * @property string $street
 * @property string $postal
 * @property string $city
 * @method static SpielortModel|null findById($id, array $opt=array())
 */

class SpielortModel extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_spielort";

}