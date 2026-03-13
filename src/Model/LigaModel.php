<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016- Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Ligaverwaltung\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $pid
 * @property string $name
 * @property string $saison
 * @property string $spielplan
 * @property string $spielstaerke
 * @property string $rechnungsbetrag_spielort
 * @property string $rechnungsbetrag_aufsteller
 *
 * @method static LigaModel|null findById($id, array $opt=array())
 */
class LigaModel extends Model
{
    const int SPIELPLAN_16E2D = 2;
    const int SPIELPLAN_16E4D = 4;
    const int SPIELPLAN_8E2D = 6;
    const int SPIELPLAN_6E3D = 8;
    const int SPIELPLAN_16E = 16;
    const int SPIELPLAN_4E1D = 32;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_liga';
}
