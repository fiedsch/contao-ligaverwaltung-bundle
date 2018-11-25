<?php

namespace Contao;

/**
 * @property integer $id
 * @property integer $pid
 * @property integer $begegnung_id
 * @property integer $spieler_id
 * @property integer $type
 * @property \String $value
 * @method static HighlightModel|null findById($id, array $opt=array())
 */

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle-bundle/
 * @license https://opensource.org/licenses/MIT
 */
class HighlightModel extends Model
{

    const TYPE_180 = 1;
    const TYPE_SHORTLEG = 2;
    const TYPE_HIGHFINISH = 3;
    const TYPE_171 = 4;
    const TYPE_ALL = 99;

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_highlight";

    /**
     * @return array
     */
    public static function getOptionsArray()
    {
        return [
            self::TYPE_180        => '180',
            self::TYPE_171        => '171',
            self::TYPE_SHORTLEG   => 'Short Leg',
            self::TYPE_HIGHFINISH => 'High Finish',
        ];
    }

}
