<?php

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2018 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\Config;
use Contao\System;
use Fiedsch\LigaverwaltungBundle\Helper\RankingHelperInterface;

/**
 * Spiel zweier Spieler gegeneinander (Teil einer Begegnung zweier Mannschaften).
 */
class Spiel
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Spiel constructor.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Vgl. auch SpielModel::getScore().
     *
     * @return int
     */
    public function getScoreHome()
    {
        if ($this->data['legs_home'] === $this->data['legs_away']) {
            return 0;
        }

        return $this->data['legs_home'] > $this->data['legs_away'] ? 1 : 0;
    }

    /**
     * Vgl. auch SpielModel::getScore().
     *
     * @return int
     */
    public function getScoreAway()
    {
        if ($this->data['legs_home'] === $this->data['legs_away']) {
            return 0;
        }

        return $this->data['legs_home'] > $this->data['legs_away'] ? 0 : 1;
    }

    /**
     * @return int
     */
    public function getLegsHome()
    {
        return $this->data['legs_home'];
    }

    /**
     * @return int
     */
    public function getLegsAway()
    {
        return $this->data['legs_away'];
    }

    /**
     * @return int
     */
    public function getPunkteHome()
    {
        return $this->getPunkte(sprintf('%d:%d', $this->data['legs_home'], $this->data['legs_away']));
    }

    /**
     * Punkte für die Rangliste.
     *
     * @return int
     */
    public function getPunkteAway()
    {
        return $this->getPunkte(sprintf('%d:%d', $this->data['legs_away'], $this->data['legs_home']));
    }

    /**
     * Das Punktesystem ist abhäng von der Liga, da nicht in allen Ligen die gleiche Anzahl
     * von Legs gespielt wird (best of X legs).
     * Hier: Universalmethode, da sich die Spielergebnisse der verschiedenen
     * Systeme gegenseitig ausschließen!
     * Bsp.: "3:1" => es wurde best of 5 gespielt, bei best of 3 kann es kein "3:1" geben!
     *
     * @param string $score
     *
     * @return int
     */
    public function getPunkte($score)
    {
        // Wie soll das Ranking ermittelt werden
        $ranking_model = Config::get('ligaverwaltung_ranking_model');
        // 'options'   => [ 1 => 'nach Punkten', 2 => 'nach gewonnenen Spielen' ],

        /** @var RankingHelperInterface */
        $helper = System::getContainer()->get('fiedsch_ligaverwaltung.rankinghelper');

        return $helper->getPunkte($score, $ranking_model);
    }
}
