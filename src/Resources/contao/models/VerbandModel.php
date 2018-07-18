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
 * @method static VerbandModel|null findById($id, array $opt=array())
 */
class VerbandModel extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_verband";
}