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

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\Database;
use Contao\LigaModel;
use Contao\MannschaftModel;
use Contao\SpielerModel;
use Contao\System;
use Fiedsch\LigaverwaltungBundle\Helper\RankingHelperInterface;
use Patchwork\Utf8;

/**
 * Content element "Liste aller Spieler einer Mannaschft".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 *
 * @property integer $rankingtype
 * @property integer $liga
 * @property integer $mannschaft
 *
 */
class ContentRanking extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_ranking';

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            /** @var BackendTemplate $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');
            $liga = LigaModel::findById($this->liga);
            if ('1' === $this->rankingtype) {
                $suffix = 'Mannschaften';
                $subject = sprintf('%s %s %s',
                    $liga->getRelated('pid')->name,
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            } else {
                $suffix = 'Spieler';
                $mannschaft = MannschaftModel::findById($this->mannschaft);
                $subject = sprintf('%s %s %s',
                    'Mannschaft '.($mannschaft->name ?: 'alle'),
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            }
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['ranking'][0])." $suffix $subject ###";
            // $objTemplate->id = $this->id;
            // $objTemplate->link = 'the text that will be linked with href';
            // $objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the content element.
     *
     * @throws \Exception
     */
    public function compile()
    {
        switch ($this->rankingtype) {
            case 1:
                $this->compileMannschaftenranking();
                break;
            case 2:
                $this->compileSpielerranking();
                break;
            default:
                $this->Template->subject = 'Undefined '.$this->rankingtype;
        }
    }

    /**
     * Ranking aller Mannschaften einer Liga.
     *
     * Achtung: Spiele vom spieltype "Doppel" gehen wie "Einzel" mit in die Berechnung
     * ein. (d.h. hier ohne Fallunterscheidung).
     *
     * @throws \Exception
     */
    protected function compileMannschaftenranking()
    {
        $liga = LigaModel::findById($this->liga);

        $this->Template->subject = sprintf('Ranking aller Mannschaften der %s %s %s',
            $liga->getRelated('pid')->name,
            $liga->name,
            $liga->getRelated('saison')->name
        );

        $spiele = Database::getInstance()
            ->prepare("SELECT 
                          s.score_home AS legs_home,
                          s.score_away AS legs_away,
                          b.home AS team_home,
                          b.away AS team_away,
                          b.spiel_tag AS spieltag
                          FROM tl_spiel s
                          LEFT JOIN tl_begegnung b
                          ON (s.pid=b.id)
                          LEFT JOIN tl_liga l
                          ON (b.pid=l.id)
                          LEFT JOIN tl_mannschaft m1
                          ON (b.home=m1.id)
                          LEFT JOIN tl_mannschaft m2
                          ON (b.away=m2.id)
                          WHERE l.id=?
                          AND m1.active='1'
                          AND m2.active='1'
                          ")
            ->execute($this->liga);

        $begegnungen = [];

        while ($spiele->next()) {
            $key = sprintf('%d:%d:%d', $spiele->spieltag, $spiele->team_home, $spiele->team_away);
            if (!isset($begegnungen[$key])) {
                $begegnungen[$key] = new Begegnung();
            }
            $begegnungen[$key]->addSpiel(new Spiel($spiele->row()));
        }

        $results = [];

        /** @var \Fiedsch\LigaverwaltungBundle\Begegnung $begegnung */
        foreach ($begegnungen as $key => $begegnung) {
            /* @noinspection PhpUnusedLocalVariableInspection */
            list($spieltag, $home, $away) = explode(':', $key);

            // Begegnungen: Mannschaft gegen Mannschaft

            ++$results[$home]['begegnungen'];
            ++$results[$away]['begegnungen'];

            // Legs (Ergebnis von Spieler gegen Spieler)

            $results[$home]['legs_self'] += $begegnung->getLegsHome();
            $results[$away]['legs_self'] += $begegnung->getLegsAway();
            $results[$home]['legs_other'] += $begegnung->getLegsAway();
            $results[$away]['legs_other'] += $begegnung->getLegsHome();

            // Spiele (Ergebnis von Spieler gegen Spieler; entweder 1:0 oder 0:1)

            $results[$home]['spiele_self'] += $begegnung->getSpieleHome();
            $results[$away]['spiele_self'] += $begegnung->getSpieleAway();
            $results[$home]['spiele_other'] += $begegnung->getSpieleAway();
            $results[$away]['spiele_other'] += $begegnung->getSpieleHome();

            // Punkte für die Begegnung

            $results[$home]['punkte_self'] += $begegnung->getPunkteHome();
            $results[$away]['punkte_self'] += $begegnung->getPunkteAway();
            $results[$home]['punkte_other'] += $begegnung->getPunkteAway();
            $results[$away]['punkte_other'] += $begegnung->getPunkteHome();

            $results[$home]['gewonnen'] += $begegnung->isGewonnenHome() ? 1 : 0;
            $results[$home]['unentschieden'] += $begegnung->isUnentschieden() ? 1 : 0;
            $results[$home]['verloren'] += $begegnung->isVerlorenHome() ? 1 : 0;

            $results[$away]['gewonnen'] += $begegnung->isGewonnenAway() ? 1 : 0;
            $results[$away]['unentschieden'] += $begegnung->isUnentschieden() ? 1 : 0;
            $results[$away]['verloren'] += $begegnung->isVerlorenAway() ? 1 : 0;
        }

        /** @var RankingHelperInterface */
        $helper = System::getContainer()->get('fiedsch_ligaverwaltung.rankinghelper');
        uasort($results, function ($a, $b) use ($helper) {
            return $helper->compareResults($a, $b);
        });

        // Berechnung Rang (Tabellenplatz) und Label

        $lastresult = [
            'punkte_self' => PHP_INT_MAX,
            'punkte_other' => 0,
            'spiele_self' => PHP_INT_MAX,
            'spiele_other' => 0,
            'legs_self' => PHP_INT_MAX,
            'legs_other' => 0,
        ];

        $rang = 0;
        $rang_skip = 1;
        foreach ($results as $id => $data) {
            $mannschaft = MannschaftModel::findById($id);

            if (!$mannschaft || '1' !== $mannschaft->active) {
                unset($results[$id]);
                continue;
            }

            $results[$id]['name'] = $mannschaft->getLinkedName();

            if (self::isTie($results[$id], $lastresult)) {
                ++$rang_skip;
            } else {
                $rang += $rang_skip;
                $rang_skip = 1;
            }
            $results[$id]['rang'] = $rang;
            $lastresult = $results[$id];
        }

        $this->Template->rankingtype = 'mannschaften';
        $this->Template->listitems = $results;
    }

    /**
     * Ranking aller Spieler einer Mannschaft (in einer liga).
     *
     * Achtung: Spiele vom spieltype "Doppel" gehen *nicht* mit in die Berechnung
     * ein -- gezählt werden nur die "Einzel".
     *
     * ohne ausgewählte Mannschaft => Ranking aller Spieler der Liga
     *
     * @throws \Exception
     */
    protected function compileSpielerranking()
    {
        $sql = "SELECT 
                          s.score_home AS legs_home,
                          s.score_away AS legs_away,
                          s.home AS player_home,
                          s.away AS player_away,
                          b.home AS team_home,
                          b.away AS team_away,
                          b.id AS begegnung_id
                          FROM tl_spiel s
                          LEFT JOIN tl_begegnung b
                          ON (s.pid=b.id)
                          LEFT JOIN tl_liga l
                          ON (b.pid=l.id)
                          LEFT JOIN tl_mannschaft m1
                          ON (b.home=m1.id)
                          LEFT JOIN tl_mannschaft m2
                          ON (b.away=m2.id)
                          WHERE s.spieltype=1
                          AND l.id=?
                          AND m1.active='1'
                          AND m2.active='1'
                          ";

        if ($this->mannschaft > 0) {
            // eine bestimmte Mannschaft
            $mannschaft = MannschaftModel::findById($this->mannschaft);
            $this->Template->subject = 'Ranking aller Spieler der Mannschaft '.$mannschaft->name;
            $sql .= ' AND (b.home=? OR b.away=?)';
            $spiele = Database::getInstance()
                ->prepare($sql)->execute($this->liga, $this->mannschaft, $this->mannschaft);
        } else {
            // alle Mannschaften
            $this->Template->subject = 'Ranking aller Spieler';
            $spiele = Database::getInstance()
                ->prepare($sql)->execute($this->liga);
        }

        $results = [];

        while ($spiele->next()) {
            $spiel = new Spiel($spiele->row());

            $results[$spiele->player_home]['mannschaft_id'] = $spiele->team_home;
            $results[$spiele->player_away]['mannschaft_id'] = $spiele->team_away;

            ++$results[$spiele->player_home]['spiele'];
            $results[$spiele->player_home]['spiele_self'] += $spiel->getScoreHome();
            $results[$spiele->player_home]['spiele_other'] += $spiel->getScoreAway();
            $results[$spiele->player_home]['legs_self'] += $spiel->getLegsHome();
            $results[$spiele->player_home]['legs_other'] += $spiel->getLegsAway();
            $results[$spiele->player_home]['punkte_self'] += $spiel->getPunkteHome();
            $results[$spiele->player_home]['punkte_other'] += $spiel->getPunkteAway();

            ++$results[$spiele->player_away]['spiele'];
            $results[$spiele->player_away]['spiele_self'] += $spiel->getScoreAway();
            $results[$spiele->player_away]['spiele_other'] += $spiel->getScoreHome();
            $results[$spiele->player_away]['legs_self'] += $spiel->getLegsAway();
            $results[$spiele->player_away]['legs_other'] += $spiel->getLegsHome();
            $results[$spiele->player_away]['punkte_self'] += $spiel->getPunkteAway();
            $results[$spiele->player_away]['punkte_other'] += $spiel->getPunkteHome();
        }

        // ID 0 ist der Platzhalter für "kein Spieler" (z.B. bei "nicht angetreten"),
        // was uns im Ranking nicht interessiert
        unset($results[0]);

        // Bei mannschaftsinternen Rankings alle Spieler löschen, die nicht
        // zur betrachteten Mannschaft gehören.
        if ($this->mannschaft > 0) {
            foreach ($results as $id => $data) {
                if ($data['mannschaft_id'] !== $this->mannschaft) {
                    unset($results[$id]);
                }
            }
        }

        /** @var RankingHelperInterface */
        $helper = System::getContainer()->get('fiedsch_ligaverwaltung.rankinghelper');
        uasort($results, function ($a, $b) use ($helper) {
            return $helper->compareResults($a, $b);
        });

        // Berechnung Rang (Tabellenplatz) und Label

        // Initialisierung der virtuellen Zeile 0 mit Maximalwerten
        $lastrow = [
            'punkte_self' => PHP_INT_MAX,
            'punkte_other' => 0,
            'spiele_self' => PHP_INT_MAX,
            'spiele_other' => 0,
            'legs_self' => PHP_INT_MAX,
            'legs_other' => 0,
        ];

        $rang = 0;
        $rang_skip = 1;

        foreach ($results as $id => $data) {
            $spieler = SpielerModel::findById($id);
            $mannschaft = MannschaftModel::findById($results[$id]['mannschaft_id']);

            if (!$spieler || '1' !== $spieler->active || !$mannschaft || '1' !== $mannschaft->active) {
                unset($results[$id]);
                continue;
            }
            $results[$id]['name'] = $spieler->getName();

            $results[$id]['mannschaft'] = $mannschaft->getLinkedName();

            // Informationen zum Spieler über CSS-Klassen hinzufügen
            /** @var MemberModel $member */
            $member = $spieler->getRelated('member_id');
            if ($member) {
                $cssClasses = [];
                if (!$member->anonymize) {
                    $cssClasses[] = $member->gender;
                    if ($spieler->jugendlich)
                        $cssClasses[] = 'youth';
                }
                $results[$id]['CSS'] = implode(' ', $cssClasses);
            }

            if (self::isTie($results[$id], $lastrow)) {
                // gleicher Rang und beim nächsten einen Rang mehr auslassen
                ++$rang_skip;
            } else {
                // ein Rang weiter und keinen folgenden auslassen,
                // aber die ggf. vorherige Auslassung berücksichtigen)
                $rang += $rang_skip;
                $rang_skip = 1;
            }
            $results[$id]['rang'] = $rang;

            $lastrow = [
                'punkte_self' => $results[$id]['punkte_self'],
                'punkte_other' => $results[$id]['punkte_other'],
                'spiele_self' => $results[$id]['spiele_self'],
                'spiele_other' => $results[$id]['spiele_other'],
                'legs_self' => $results[$id]['legs_self'],
                'legs_other' => $results[$id]['legs_other'],
            ];
        }

        $this->Template->rankingtype = 'spieler';
        if ($this->mannschaft > 0) {
            $this->Template->rankingsubtype = 'mannschaft';
        } else {
            $this->Template->rankingsubtype = 'alle';
        }

        $this->Template->listitems = $results;
    }

    /**
     * TODO (?): in Helper\RankingHelper auslagern
     *
     * @param array $result     die Daten einer Zeile des sortierten Rankimgs
     * @param array $lastresult die Daten der vorhergehenden Zeile des Rankings
     *
     * @return bool
     */
    protected function isTie($result, $lastresult)
    {
            return $result['punkte_self'] === $lastresult['punkte_self']
                && $result['spiele_self'] - $result['spiele_other'] === $lastresult['spiele_self'] - $lastresult['spiele_other']
                && $result['legs_self'] - $result['legs_other'] === $lastresult['legs_self'] - $lastresult['legs_other']
                && $result['legs_self'] === $lastresult['legs_self']
                ;
    }
}
