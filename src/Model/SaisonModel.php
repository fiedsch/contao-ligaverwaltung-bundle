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
 * @property string $name
 *
 * @method static SaisonModel|null findById($id, array $opt=array())
 */
class SaisonModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_saison';
}
