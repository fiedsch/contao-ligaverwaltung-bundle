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
 * @method static AufstellerModel|null findById($id, array $opt=array())
 */

class AufstellerModel extends Model
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_aufsteller";
}