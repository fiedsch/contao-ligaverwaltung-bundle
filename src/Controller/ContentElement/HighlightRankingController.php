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

namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\Database;
use Contao\Date;
use Contao\MemberModel;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Config;
use Contao\System;
use Doctrine\DBAL\Connection;
use Exception;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Fiedsch\Ligaverwaltung\Entity\Begegnung;
use Fiedsch\Ligaverwaltung\Entity\Spiel;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Helper\RankingHelperInterface;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Model\HighlightModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;

/**
 * Content element "Ranking der Highlights (einer Mannschaft oder Liga)".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 *
 * @property int $rankingtype
 * @property int $liga
 * @property int $mannschaft
 */
#[AsContentElement(
    type: 'highlightranking',
    category: 'ligaverwaltung',
    template: 'content_element/highlightranking'
)]
class HighlightRankingController extends AbstractContentElementController
{
    const MAX_SHORTLEG_DARTS = 20;

    use TlModeTrait;

    public function __construct(private readonly Connection $connection)
    {
    }
    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### ' . u($GLOBALS['TL_LANG']['CTE']['highlightranking'][0])->upper() . ' ###';

        $template->rankingfield = $model->rankingfield;
        $template->auf_mannschaftsseite = $model->mannschaft > 0; // TODO bessere Variablenbezeichnung

        switch ($model->rankingtype) {
            case 1:
                $this->setDataMannschaftenranking($template, $model);
                break;

            case 2:
                $this->setDataSpielerranking($template, $model);
                break;

            default:
                $template->subject = 'Undefined ' . $model->rankingtype;
        }

    }

    /**
     * @throws \LogicException (Methode ist noch nicht vollständig implementiert!))
     * @throws \Doctrine\DBAL\Exception
     */
    private function setDataMannschaftenranking(FragmentTemplate $template, ContentModel $model): void
    {
        $liga = LigaModel::findById($model->liga);
        if ($this->isBackend()) {
            $template->subject = sprintf('Highlight-Ranking aller Mannschaften der %s %s %s',
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name
            );
            return;
        }

        $sql = "SELECT
                          h.*, b.spiel_am, ma.name as mannschaft
                          FROM tl_highlight h
                          LEFT JOIN tl_begegnung b
                          ON (h.begegnung_id = b.id)
                          LEFT JOIN tl_spieler s
                          ON (h.spieler_id=s.id)
                          LEFT JOIN tl_member me
                          ON (s.member_id=me.id)
                          LEFT JOIN tl_mannschaft ma
                          ON (s.pid=ma.id)
                          WHERE b.pid=?
                          AND b.published=1
                          AND ma.active=1
                          AND s.active=1
                          AND me.id IS NOT NULL"; // keine gelöschten Spieler

        $sql .= ' AND '.$this->getRankingTypeFilter($model,'h');

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(1, $liga->id);
        $highlights = $statement->executeQuery();

        $results = [];

        while ($highlights->fetchAssociative()) {
            $results[] = [
                'datum' => Date::parse(Config::get('dateFormat'), $highlights->spiel_am),
                'mannschaft' => $highlights->mannschaft,
                'hl_171' => HighlightModel::TYPE_171 == $highlights->type ? $highlights->value : '', // FIXME === vs == (vs. Konstanten ändern)
                'hl_180' => HighlightModel::TYPE_180 == $highlights->type ? $highlights->value : '',
                'hl_highfinish' => HighlightModel::TYPE_HIGHFINISH == $highlights->type ? $highlights->value : '',
                'hl_shortleg' => HighlightModel::TYPE_SHORTLEG == $highlights->type ? $highlights->value : '',
                'hl_punkte' => [],
                'hl_rang' => 0,
            ];
        }

        // TODO analog compileSpielerranking() aufbereiten
        throw new \LogicException(sprintf('Methode %s:%s noch nicht vollständig implementiert!', __CLASS__, __METHOD__));

        $template->rankingtype = 'mannschaften';
        $template->listitems = $results;
    }
    private function setDataSpielerranking(FragmentTemplate $template, ContentModel $model): void
    {
        $liga = LigaModel::findById($model->liga);
        $mannschaftsuffix = '';

        if ($model->rankingfield !== MannschaftModel::ALLE_MANNSCHAFTEN && $model->mannschaft > 0) {
            $mannschaft = MannschaftModel::findById($model->mannschaft);
            if ($mannschaft) {
                $mannschaftsuffix = sprintf('Mannschaft %s', $mannschaft->name);
            } else {
                $mannschaftsuffix = sprintf('Mannschaft mit der ID %d %s', $model->mannschaft, DCAHelper::DOES_NOT_EXIST);
            }
        } else {
            $mannschaftsuffix = 'alle Mannschaften';
        }
        if ($this->isBackend()) {
            $template->subject = sprintf('Highlight-Ranking Spieler %s %s %s (%s)',
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name,
                $mannschaftsuffix
            );
            return;
        }

        $sql = "SELECT
                          h.*,
                          s.id as spieler_id, s.pid, s.active as sactive, s.jugendlich as sjugendlich,
                          me.firstname as member_firstname, me.lastname as member_lastname, me.anonymize as member_anonymize, me.id as member_id, me.gender as member_gender,
                          b.spiel_am,
                          ma.name as mannschaft, ma.active as mactive, ma.id as maid
                          FROM tl_highlight h
                          LEFT JOIN tl_begegnung b
                          ON (h.begegnung_id = b.id)
                          LEFT JOIN tl_spieler s
                          ON (h.spieler_id=s.id)
                          LEFT JOIN tl_member me
                          ON (s.member_id=me.id)
                          LEFT JOIN tl_mannschaft ma
                          ON (s.pid=ma.id)
                          WHERE b.pid=?
                          AND b.published=1
                          -- AND s.active=1   -- keine Filter, damit 'meine' Leistungen nicht verloren gehen
                          -- AND ma.active=1  -- auch, wenn 'ich' sie in einer anderen Mannschaft erbracht habe
                          AND me.id IS NOT NULL"; // keine gelöschten Spieler

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(1, $liga->id);
        $highlights = $statement->executeQuery();

        if ($model->rankingfield !== MannschaftModel::ALLE_MANNSCHAFTEN && $model->mannschaft > 0) {
            // eine bestimmte Mannschaft
            $mannschaft = MannschaftModel::findById($model->mannschaft);

            $sql .= ' AND s.pid=?';
            $sql .= ' AND '.$this->getRankingTypeFilter($model, 'h');
            $sql .= " AND s.active=1"; // nur aktive Spieler dieser Mannschaft
            $sql .= ' ORDER BY spiel_am DESC';
            $statement = $this->connection->prepare($sql);
            $statement->bindValue(1, $model->liga);
            $statement->bindValue(2, $model->mannschaft);
            $highlights = $statement->executeQuery();
        } else {
            // alle Mannschaften
            $sql .= ' AND '.$this->getRankingTypeFilter($model, 'h');
            $sql .= ' ORDER BY spiel_am DESC';
            $statement = $this->connection->prepare($sql);
            $statement->bindValue(1, $model->liga);
            $highlights = $statement->executeQuery();
        }

        $results = [];

        while ($highlight = $highlights->fetchAssociative()) {
            // Bei Ranking "nur für eine Mannschaft" auf der Mannschaftsseite
            // also falls $model->mannschaft > 0 unter der $highlight->spieler_id
            // ablegen (dem "Mannschaftsspieler") , ansonsten unter der
            // $highlights->memberid (der dahinter stehenden Person, dem "Member")

            if ($model->mannschaft > 0) {
                $credit_to = $highlight['spieler_id'];
            } else {
                $credit_to = $highlight['member_id'];
            }
            // Initialisieren
            if (!isset($results[$credit_to])) {
                $results[$credit_to] = [
                    'name' => DCAHelper::makeSpielerNameFromParts($highlight['member_firstname'], $highlight['member_lastname'], (bool) $highlight['member_anonymize']),
                    'mannschaft' => [$highlight['mannschaft'] => 0], // bei Wechsel der Mannschaft eines Spielers innerhalb der Saison können es mehrere Mannschaften sein!
                    'mannschaftsid' => [$highlight['maid'] => 0],
                    'hl_171' => 0, // Anzahl
                    'hl_180' => 0, // dito
                    'hl_highfinish' => [], // Liste der Highfinishes
                    'hl_shortleg' => [], // dito
                    'hl_punkte' => [], // List der einzelnen Punkte
                    'hl_rang' => 0,
                    'member_id' => $highlight['member_id'],
                    'spieler_id' => $highlight['spieler_id'],
                    'active' => false,
                    'CSS' => $highlight['member_anonymize'] ? '' : trim($highlight['member_gender'].' '.($highlight['sjugendlich'] ? 'youth' : '')),
                ];
            }
            // Spieler hat in versch. Mannschaften gespielt?
            // Bei Wechsel der Mannschaft eines Spielers innerhalb der Saison können es mehrere Mannschaften sein!
            if (!isset($results[$credit_to]['mannschaft'][$highlight['mannschaft']])) {
                $results[$credit_to]['mannschaft'][$highlight['mannschaft']] = 0;
            }
            if (!isset($results[$credit_to]['mannschaftsid'][$highlight['maid']])) {
                $results[$credit_to]['mannschaftsid'][$highlight['maid']] = 0;
            }
            ++$results[$credit_to]['mannschaft'][$highlight['mannschaft']]; // Label
            ++$results[$credit_to]['mannschaftsid'][$highlight['maid']]; // ID
            // Spieler ist (noch) aktiv?
            $results[$credit_to]['active'] |= $highlight['sactive'] && $highlight['mactive']; // aktiver Spieler in einer aktiven Mannschaft

            // Aggregieren
            switch ($highlight['type']) {
                case HighlightModel::TYPE_171:
                    $results[$credit_to]['hl_171'] += (int)$highlight['value'];
                    $results[$credit_to]['hl_punkte'][] = $highlight['value'];
                    break;

                case HighlightModel::TYPE_180:
                    $results[$credit_to]['hl_180'] += (int)$highlight['value'];
                    $results[$credit_to]['hl_punkte'][] = $highlight['value'];
                    break;

                case HighlightModel::TYPE_HIGHFINISH:
                    $results[$credit_to]['hl_highfinish'][] = rtrim($highlight['value'], ',');
                    $results[$credit_to]['hl_punkte'][] = explode(',', rtrim($highlight['value'], ','));
                    break;

                case HighlightModel::TYPE_SHORTLEG:
                    $results[$credit_to]['hl_shortleg'][] = rtrim($highlight['value'], ',');
                    $results[$credit_to]['hl_punkte'][] = explode(',', rtrim($highlight['value'], ','));
                    break;
            }
        }

        // Daten "normieren" und Punkte berechnen

        foreach (array_keys($results) as $id) {
            // Spieler nicht mehr aktiv?
            if (!$results[$id]['active']) {
                unset($results[$id]);
                continue;
            }

            switch ($model->rankingfield) { // note: switch is not type sensitive (i.e. we are implicitly using == here)
                case HighlightModel::TYPE_171:
                case HighlightModel::TYPE_180:
                    $results[$id]['hl_punkte'] = [array_sum($results[$id]['hl_punkte'])];
                    break;

                case HighlightModel::TYPE_HIGHFINISH:
                    $results[$id]['hl_punkte'] = static::flattenToIntArray($results[$id]['hl_punkte']);
                    $results[$id]['hl_highfinish'] = static::prettyPrintSorted($results[$id]['hl_highfinish'], 'DESC');
                    // höchstes Finish zuerst
                    rsort($results[$id]['hl_punkte']);
                    break;

                case HighlightModel::TYPE_SHORTLEG:
                    $results[$id]['hl_punkte'] = static::flattenToIntArray($results[$id]['hl_punkte']);
                    $results[$id]['hl_shortleg'] = static::prettyPrintSorted($results[$id]['hl_shortleg'], 'ASC');
                    // Mapping
                    $results[$id]['hl_punkte'] = array_map(
                        static function ($val) {
                            $val = (int) $val;
                            // Wert > self::MAX_SHORTLEG_DARTS via 0 Punkte nicht berücksichtigen
                            if (self::MAX_SHORTLEG_DARTS < $val) {
                                return 0;
                            }
                            // mapping: kürzeres Leg == besser
                            $result = self::MAX_SHORTLEG_DARTS - $val + 1;
                            // "12" (9 Darter) bis "01" (20 Darts) für String-Sortierung
                            return $result < 10 ? "0$result" : "$result";
                        },
                        $results[$id]['hl_punkte']
                    );

                    // kürzester Shortleg zuerst (nach Mapping => höchster Wert zuerst!)
                    rsort($results[$id]['hl_punkte']);
                    break;

                case HighlightModel::TYPE_ALL:
                    $results[$id]['hl_punkte'] = []; // wir sortieren hier nach Namen, brauchen also die Punkte nicht
                    $results[$id]['hl_shortleg'] = static::prettyPrintSorted($results[$id]['hl_shortleg'], 'ASC');
                    $results[$id]['hl_highfinish'] = static::prettyPrintSorted($results[$id]['hl_highfinish'], 'DESC');
            }

            if (count($results[$id]['mannschaftsid']) === 1) {
                // der Normalfall: Spieler hat in genau einer Mannschaft gespielt
                $m = MannschaftModel::findById(array_keys($results[$id]['mannschaftsid'])[0]);
                $mannschaftenlabel = $m->getLinkedName();
            } else {
                // Spieler hat in versch. Mannschaften gespielt? (unverlinkte) Mannschaftsnamen aneinanderhängen:
                $mannschaftenlabel = implode(', ', array_keys($results[$id]['mannschaft']));
                // TODO (?) für alle IDs in $results[$id]['mannschaftsid'] den verlinkten Mannschaftsnamen bestimmen und diese aneinanderhängen
            }
            $results[$id]['mannschaft'] = $mannschaftenlabel;
        }

        // Sortieren

        if (HighlightModel::TYPE_ALL == $model->rankingfield) {
            uasort(
                $results,
                static function ($a, $b) {
                    // ohne spezielle Punkteregel: nach Namen sortieren
                    return $a['name'] <=> $b['name'];
                }
            );
        } else {
            uasort(
                $results,
                function ($a, $b) use ($model) {
                    // Bei Shortleg und Highfinish können wir die sortierten Werte (== ['hl_punkte']-Eintrag)
                    // aneinander hängen und als String sortieren, weil:
                    // * bei Shortleg oben bereits "gemap" wurde (kurze Legs -> hohe Werte) und
                    //   die (gemapten) Werte zwischen "12" (9 Darter) und "01" (20 Darts) liegen
                    // * bei Highfinish die Nebenbedingung gilt, daß die Werte > 100 und <180 sind!
                    //   Es kann also nicht das "Zahlen als Strings sortiert"-Problem auftauchen --
                    //   falsch: "1", "11", "2" vs. korrekt: 1, 2, 11.
                    if (HighlightModel::TYPE_SHORTLEG == $model->rankingfield || HighlightModel::TYPE_HIGHFINISH == $model->rankingfield) {
                        return strcmp(implode('', $b['hl_punkte']), implode('', $a['hl_punkte']));
                    }
                    // Bei allen anderen Rankings hat ['hl_punkte'] nur einen numerischen Eintrag, nach dem
                    // wir sortieren können:
                    return ($b['hl_punkte'][0] ?? 0) <=> ($a['hl_punkte'][0] ?? 0);
                }
            );
        }

        $lastpunkte = PHP_INT_MAX;
        $rang = 0;
        $rang_skip = 1;

        foreach (array_keys($results) as $i) {
            // die konkatenierten Punktwerte als "Prüfstring" für die Feststellung,
            // ob ein Tie vorliegt. Denn: nur, wenn zwei aufeinanderfolgende Prüfstrings
            // identisch sind haben wir bei der Rangvergabe einen "Tie"!
            $punkte = implode('', $results[$i]['hl_punkte']);

            if ($punkte === $lastpunkte) {
                // we have a "tie"
                ++$rang_skip;
            } else {
                $rang += $rang_skip;
                $rang_skip = 1;
            }
            $results[$i]['hl_rang'] = $rang;
            $lastpunkte = $punkte;
        }

        $template->rankingtype = 'spieler';

        if ($model->mannschaft > 0) {
            $template->rankingsubtype = 'mannschaft';
        } else {
            $template->rankingsubtype = 'alle';
        }

        $template->listitems = $results;
    }

        protected function getRankingTypeFilter(ContentModel $model, string $tablealias): string
    {
        switch ($model->rankingfield) { // note: switch is not type sensitive (i.e. we are implicitly using == here)
            case HighlightModel::TYPE_171:
            case HighlightModel::TYPE_180:
                $result = sprintf('%s.type IN (%d,%d)',
                    $tablealias,
                    HighlightModel::TYPE_171, HighlightModel::TYPE_180
                );
                break;

            case HighlightModel::TYPE_HIGHFINISH:
                $result = sprintf('%s.type=%d',
                    $tablealias,
                    HighlightModel::TYPE_HIGHFINISH
                );
                break;

            case HighlightModel::TYPE_SHORTLEG:
                $result = sprintf('%s.type=%d',
                    $tablealias,
                    HighlightModel::TYPE_SHORTLEG
                );
                break;

            default:
                $result = '1=1'; // alle Records, aber zusammen mit AND ... sinnvolles SQL
        }

        return $result;
    }

    /**
     * flatten an array. E.g. [1,2,[3,4],5] becomes [1,2,3,4,5].
     * Additionally the array elements will be cast to integers.
     *
     * @return array (of integers)
     */
    protected static function flattenToIntArray(array $a): array
    {
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($a));
        $result = [];

        foreach ($it as $v) {
            $result[] = (int) $v;
        }

        return $result;
    }

    protected static function prettyPrintSorted(string|array $value, string $order): string
    {
        if (is_array($value)) {
            $data = $value;
        } else {
            $data = explode(',', $value);
        }
        // prepare ['1','2','3,4',5'] for sort,
        // i.e. make it ['1','2','3','4',5']
        // i.e. split '3,4' into '3','4'
        $data = explode(',', implode(',', $data));

        if ('ASC' === $order) {
            asort($data);
        } else {
            arsort($data);
        }

        return static::compressResultsArray($data);
    }

    /**
     * Compress an array of results
     * i.e. display "15,15,15,16,..." as "3x15,16,...".
     */
    protected static function compressResultsArray(array $data): string
    {
        $aggregated = [];
        $current = null;

        foreach ($data as $entry) {
            if ($current !== $entry) {
                $current = $entry;
                $aggregated[$current] = 0;
            }
            ++$aggregated[$current];
        }
        $result = [];

        foreach ($aggregated as $k => $v) {
            if (1 === $v) {
                $result[] = $k;
            } else {
                $result[] = sprintf('<small>%d&times;</small>%s', $v, $k);
            }
        }

        return implode(', ', $result);
    }

}
