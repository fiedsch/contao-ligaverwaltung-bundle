<?php /** @noinspection ALL */

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

namespace Fiedsch\LigaverwaltungBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\Database;
use Contao\MemberModel;
use Contao\Template;
use Contao\Config;
use Exception;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Fiedsch\LigaverwaltungBundle\Entity\Begegnung;
use Fiedsch\LigaverwaltungBundle\Entity\Spiel;
use Fiedsch\LigaverwaltungBundle\Helper\RankingHelperInterface;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Fiedsch\LigaverwaltungBundle\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Content element "Liste aller Spieler einer Mannschaft".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 *
 * @property int $rankingtype
 * @property int $liga
 * @property int $mannschaft
 */
#[AsContentElement(
    type: 'ranking',
    category: 'ligaverwaltung',
    template: 'content_element/ranking'
)]
class RankingController extends AbstractContentElementController
{
    /** @noinspection PhpUnused */
    const int RANKING_TYPE_MANNSCHAFTEN = 1;
    /** @noinspection PhpUnused */
    const string RANKING_TYPE_S_MANNSCHAFTEN = 'mannschaften';

    const int RANKING_TYPE_SPIELER = 2;

    /** @noinspection PhpUnused */
    const string RANKING_TYPE_S_SPIELER = 'spieler';

    public function __construct(private readonly RankingHelperInterface $rankingHelper)
    {
    }

    use TlModeTrait;

    /**
     * changed scope from protected (as in AbstractContentElementController) to public
     * because we need to call this method in ContentMannschaftsseite.
     *
     * @throws Exception
     */
    public function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        $this->setData($template, $model);

        return $template->getResponse();
    }

    /**
     * @throws Exception
     */
    private function setData(Template $template, ContentModel $model): void
    {
        $liga = LigaModel::findById($model->liga);

        // Daten für die Backend-Ansicht
        if (!$liga) {
            $template->suffix = '';
            $template->subject = sprintf('Liga mit der ID=%d (ex. nicht mehr', $model->liga);
            return;
        }
        $template->rankingtype = $model->rankingtype;
        switch ($model->rankingtype) {
            case 1:
                $template->suffix = 'Mannschaften';
                $template->subject = sprintf('%s %s %s',
                    $liga->getRelated('pid')->name,
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
                $this->compileMannschaftenranking($template, $model);
                break;
            case 2:
                $template->suffix = 'Spieler';
                $mannschaft = MannschaftModel::findById($model->mannschaft);
                $template->subject = sprintf('%s %s %s',
                    '(Mannschaft: ' . ($mannschaft?->name ?: 'alle') . ')',
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
                $this->compileSpielerranking($template, $model);
                break;
            default:
                $template->suffix = '';
                $template->subject = '';
        }

        // TODO (jeweils Variablen für Twig Template vs PHP Template) und $this->... wird $model->...
        // $appendCssClass = 'rankingtype_'.(1 === $this->rankingtype ? 'mannschaft' : 'spieler');
        // $this->cssID = [$this->cssID[0] ?? '', ($this->cssID[1] ?? '') .' '.$appendCssClass];

        $template->ranking_model = Config::get('ligaverwaltung_ranking_model');

    }

    /**
     * Ranking aller Mannschaften einer Liga.
     *
     * Achtung: Spiele vom spieltype "Doppel" gehen wie "Einzel" mit in die Berechnung
     * ein. (d.h. hier ohne Fallunterscheidung).
     *
     * @throws Exception
     */
    protected function compileMannschaftenranking(Template $template, ContentModel $model): void
    {
        $liga = LigaModel::findById($model->liga);

        $template->subject = sprintf('Ranking aller Mannschaften der %s %s %s',
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
                          AND b.published='1'
                          ")
            ->execute($model->liga)
        ;

        $begegnungen = [];

        while ($spiele->next()) {
            $key = sprintf('%d:%d:%d', $spiele->spieltag, $spiele->team_home, $spiele->team_away);

            if (!isset($begegnungen[$key])) {
                $begegnungen[$key] = new Begegnung();
            }
            $begegnungen[$key]->addSpiel(new Spiel($spiele->row()));
        }

        $results = [];

        /** @var Begegnung $begegnung */
        foreach ($begegnungen as $key => $begegnung) {
            [$spieltag, $home, $away] = explode(':', $key);
            unset($spieltag); // wird nicht benötigt

            // Begegnungen: Mannschaft gegen Mannschaft

            // Initialisierung
            foreach ([$home, $away] as $i) {
                foreach (['begegnungen', 'legs_self', 'legs_other', 'spiele_self', 'spiele_other', 'punkte_self', 'punkte_other', 'gewonnen','unentschieden','verloren'] as $j) {
                    $results[$i][$j] = $results[$i][$j] ?? 0;
                }
            }

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

        $helper = $this->rankingHelper;
        uasort(
            $results,
            static function ($a, $b) use ($helper) {
                return $helper->compareResults($a, $b);
            }
        );

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

        foreach (array_keys($results) as $id) {
            $mannschaft = MannschaftModel::findById($id);

            if (!$mannschaft?->active) {
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

        $template->rankingtype = 'mannschaften';
        $template->listitems = $results;
    }

    /**
     * Ranking aller Spieler einer Mannschaft (in einer liga).
     *
     * Achtung: Spiele vom spieltype "Doppel" gehen *nicht* mit in die Berechnung
     * ein -- gezählt werden nur die "Einzel".
     *
     * ohne ausgewählte Mannschaft => Ranking aller Spieler der Liga
     *
     * @throws Exception
     */
    protected function compileSpielerranking(Template $template, ContentModel $model): void
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
                          AND b.published='1'
                          ";

        if ($model->mannschaft > 0) {
            // eine bestimmte Mannschaft
            $mannschaft = MannschaftModel::findById($model->mannschaft);
            $template->subject = 'Ranking aller Spieler der Mannschaft '.$mannschaft->name;
            $sql .= ' AND (b.home=? OR b.away=?)';
            $spiele = Database::getInstance()
                ->prepare($sql)->execute($model->liga, $model->mannschaft, $model->mannschaft);
        } else {
            // alle Mannschaften
            $template->subject = 'Ranking aller Spieler';
            $spiele = Database::getInstance()
                ->prepare($sql)->execute($model->liga);
        }

        $results = [];

        while ($spiele->next()) {
            $spiel = new Spiel($spiele->row());

            $results[$spiele->player_home]['mannschaft_id'] = $spiele->team_home;
            $results[$spiele->player_away]['mannschaft_id'] = $spiele->team_away;

            //++$results[$spiele->player_home]['spiele'];
            $results[$spiele->player_home]['spiele'] = ($results[$spiele->player_home]['spiele'] ?? 0)+1;
            $results[$spiele->player_home]['spiele_self'] = ($results[$spiele->player_home]['spiele_self'] ?? 0) + $spiel->getScoreHome();
            $results[$spiele->player_home]['spiele_other'] = ($results[$spiele->player_home]['spiele_other'] ?? 0)+ $spiel->getScoreAway();
            $results[$spiele->player_home]['legs_self'] = ($results[$spiele->player_home]['legs_self'] ?? 0) + $spiel->getLegsHome();
            $results[$spiele->player_home]['legs_other'] = ($results[$spiele->player_home]['legs_other'] ?? 0) + $spiel->getLegsAway();
            $results[$spiele->player_home]['punkte_self'] = ($results[$spiele->player_home]['punkte_self'] ?? 0) + $spiel->getPunkteHome();
            $results[$spiele->player_home]['punkte_other'] = ($results[$spiele->player_home]['punkte_other'] ?? 0) + $spiel->getPunkteAway();

            $results[$spiele->player_away]['spiele'] = ($results[$spiele->player_away]['spiele'] ?? 0)+1;
            $results[$spiele->player_away]['spiele_self'] = ($results[$spiele->player_away]['spiele_self'] ?? 0)+ $spiel->getScoreAway();
            $results[$spiele->player_away]['spiele_other'] = ($results[$spiele->player_away]['spiele_other'] ?? 0) + $spiel->getScoreHome();
            $results[$spiele->player_away]['legs_self'] = ($results[$spiele->player_away]['legs_self'] ?? 0) + $spiel->getLegsAway();
            $results[$spiele->player_away]['legs_other'] = ($results[$spiele->player_away]['legs_other'] ?? 0) + $spiel->getLegsHome();
            $results[$spiele->player_away]['punkte_self'] = ($results[$spiele->player_away]['punkte_self'] ?? 0) + $spiel->getPunkteAway();
            $results[$spiele->player_away]['punkte_other'] = ($results[$spiele->player_away]['punkte_other'] ?? 0)+ $spiel->getPunkteHome();
        }

        // ID 0 ist der Platzhalter für "kein Spieler" (z.B. bei "nicht angetreten"),
        // was uns im Ranking nicht interessiert
        unset($results[0]);

        // Bei mannschaftsinternen Rankings alle Spieler löschen, die nicht
        // zur betrachteten Mannschaft gehören.
        if ($model->mannschaft > 0) {
            foreach ($results as $id => $data) {
                if ($data['mannschaft_id'] !== $model->mannschaft) {
                    unset($results[$id]);
                }
            }
        }

        $helper = $this->rankingHelper;
        uasort(
            $results,
            static function ($a, $b) use ($helper) {
                return $helper->compareResults($a, $b);
            }
        );

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

        foreach (array_keys($results) as $id) {
            $spieler = SpielerModel::findById($id);
            $mannschaft = MannschaftModel::findById($results[$id]['mannschaft_id']);

            if (!$spieler?->active || !$mannschaft?->active) {
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

                    if ($spieler->jugendlich) {
                        $cssClasses[] = 'youth';
                    }
                }
                $results[$id]['CSS'] = implode(' ', $cssClasses);
            }

            if (self::isTie($results[$id], $lastrow)) {
                // gleicher Rang und beim nächsten einen Rang mehr auslassen
                ++$rang_skip;
            } else {
                // Ein Rang weiter und keinen folgenden auslassen,
                // (aber die ggf. vorherige Auslassung berücksichtigen)
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

        $template->rankingtype = 'spieler';

        if ($model->mannschaft > 0) {
            $template->rankingsubtype = 'mannschaft';
        } else {
            $template->rankingsubtype = 'alle';
        }

        $template->listitems = $results;
    }

    /**
     * TODO (?): in Helper\RankingHelper auslagern.
     *
     * @param array $result     die Daten einer Zeile des sortierten Rankimgs
     * @param array $lastresult die Daten der vorhergehenden Zeile des Rankings
     *
     * @return bool
     */
    protected function isTie(array $result, array $lastresult): bool
    {
        return $result['punkte_self'] === $lastresult['punkte_self']
                && $result['spiele_self'] - $result['spiele_other'] === $lastresult['spiele_self'] - $lastresult['spiele_other']
                && $result['legs_self'] - $result['legs_other'] === $lastresult['legs_self'] - $lastresult['legs_other']
                && $result['legs_self'] === $lastresult['legs_self']
                ;
    }
}
