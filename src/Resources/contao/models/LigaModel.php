<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Contao;

class LigaModel extends Model
{

    const SPIELPLAN_16E2D = 2;
    const SPIELPLAN_16E4D = 4;

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_liga";
}