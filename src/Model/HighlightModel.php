<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2021 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $begegnung_id
 * @property int    $spieler_id
 * @property int    $type
 * @property string $value
 *
 * @method static HighlightModel|null findById($id, array $opt=array())
 */
class HighlightModel extends Model
{
    const TYPE_180 = '1';
    const TYPE_SHORTLEG = '2';
    const TYPE_HIGHFINISH = '3';
    const TYPE_171 = '4';
    const TYPE_ALL = '99';

    const ALL_TYPES = [self::TYPE_180, self::TYPE_SHORTLEG, self::TYPE_HIGHFINISH, self::TYPE_171];

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_highlight';

    /**
     * @return array
     */
    public static function getOptionsArray()
    {
        return [
            self::TYPE_180 => '180',
            self::TYPE_171 => '171',
            self::TYPE_SHORTLEG => 'Short Leg',
            self::TYPE_HIGHFINISH => 'High Finish',
        ];
    }
}
