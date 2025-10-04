<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2025 Andreas Fieger
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
 * @property string $name
 * @property string $street
 * @property string $postal
 * @property string $city
 *
 * @method static SpielortModel|null findById($id, array $opt=array())
 */
class SpielortModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_spielort';
}
