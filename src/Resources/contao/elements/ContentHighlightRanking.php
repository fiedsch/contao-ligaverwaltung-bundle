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
use Contao\Config;
use Contao\ContentElement;
use Contao\Database;
use Contao\Date;
use Fiedsch\LigaverwaltungBundle\Model\HighlightModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Contao\System;
use Patchwork\Utf8;

/**
 * Content element "Liste aller Spieler einer Mannaschft".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class ContentHighlightRanking extends ContentElement
{
    /**
     * alles bis inkl. 20 Darts ist ein Shortleg.
     */
    const MAX_SHORTLEG_DARTS = 20;
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_highlightranking';

    /**
     * @return string
     * @throws \Exception
     *
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            /** @var BackendTemplate $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');
            $liga = LigaModel::findById($this->liga);
            if (1 === $this->rankingtype) {
                $suffix = 'Mannschaften';
                $subject = sprintf('%s %s %s',
                    $liga->getRelated('pid')->name,
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            } else {
                $suffix = 'Spieler';
                $mannschaft = MannschaftModel::findById($this->mannschaft);
                $subject = sprintf('%s %s %s %s',
                    'Mannschaft ' . ($mannschaft->name ?: 'alle'),
                    $liga->getRelated('pid')->name,
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            }
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['highlightranking'][0]) . ", $suffix, $subject ###";
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
                $this->Template->subject = 'Undefined ' . $this->rankingtype;
        }
        $this->Template->rankingfield = $this->rankingfield;
        $this->Template->auf_mannschaftsseite = $this->mannschaft > 0;
    }

    /**
     * Highlight-"Ranking" aller Mannschaften einer Liga.
     *
     * @throws \Exception
     */
    protected function compileMannschaftenranking()
    {
        $liga = LigaModel::findById($this->liga);

        $this->Template->subject = sprintf('Highlight-Ranking aller Mannschaften der %s %s %s',
            $liga->getRelated('pid')->name,
            $liga->name,
            $liga->getRelated('saison')->name
        );

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
                          AND b.published='1'
                          AND ma.active='1'
                          AND s.active='1'
                          AND me.id IS NOT NULL"; // keine gelöschten Spieler

        $sql .= ' AND ' . $this->getRankingTypeFilter('h');

        $highlights = Database::getInstance()
            ->prepare($sql)
            ->execute($this->liga);

        $results = [];

        while ($highlights->next()) {
            //print "<pre>".print_r($highlights->row(), true)."</pre>";
            $results[] = [
                'datum'         => Date::parse(Config::get('dateFormat'), $highlights->spiel_am),
                'mannschaft'    => $highlights->mannschaft,
                'hl_171'        => HighlightModel::TYPE_171 == $highlights->type ? $highlights->value : '',
                'hl_180'        => HighlightModel::TYPE_180 == $highlights->type ? $highlights->value : '',
                'hl_highfinish' => HighlightModel::TYPE_HIGHFINISH == $highlights->type ? $highlights->value : '',
                'hl_shortleg'   => HighlightModel::TYPE_SHORTLEG == $highlights->type ? $highlights->value : '',
                'hl_punkte'     => [],
                'hl_rang'       => 0,
            ];
        }

        // TODO analog compileSpielerranking() aufbereiten
        System::log('Methode noch nicht vollständig implementiert!', __METHOD__, TL_ERROR);

        $this->Template->rankingtype = 'mannschaften';
        $this->Template->listitems = $results;
    }

    /**
     * @param string $tablealias
     *
     * @return string
     */
    protected function getRankingTypeFilter($tablealias)
    {
        switch ($this->rankingfield) {
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
     * Highlight-"Ranking" aller Spieler einer Mannschaft (in einer liga).
     *
     * ohne ausgewählte Mannschaft => Ranking aller Spieler der Liga
     */
    protected function compileSpielerranking()
    {
        $sql = "SELECT
                          h.*,
                          s.id as spieler_id, s.pid, s.active as sactive, s.jugendlich as sjugendlich,
                          me.firstname as member_firstname, me.lastname as member_lastname, me.anonymize as member_anonymize, me.id as member_id, me.gender as member_gender,
                          b.spiel_am,
                          ma.name as mannschaft, ma.active as mactive
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
                          AND b.published='1'
                          -- AND s.active='1'   -- keine Filter, damit 'meine' Leistungen nicht verloren gehen
                          -- AND ma.active='1'  -- auch, wenn 'ich' sie in einer anderen Mannschaft erbracht habe
                          AND me.id IS NOT NULL"; // keine gelöschten Spieler

        if ($this->mannschaft > 0) {
            // eine bestimmte Mannschaft
            $mannschaft = MannschaftModel::findById($this->mannschaft);
            $this->Template->subject = 'Highlight-Ranking aller Spieler der Mannschaft ' . $mannschaft->name;
            $sql .= ' AND s.pid=?';
            $sql .= ' AND ' . $this->getRankingTypeFilter('h');
            $sql .= " AND s.active='1'"; // nur aktive Spieler dieser Mannschaft
            $sql .= ' ORDER BY spiel_am DESC';
            $highlights = Database::getInstance()
                ->prepare($sql)->execute($this->liga, $this->mannschaft);
        } else {
            // alle Mannschaften
            $sql .= ' AND ' . $this->getRankingTypeFilter('h');
            $sql .= ' ORDER BY spiel_am DESC';
            $this->Template->subject = 'Highlight-Ranking aller Spieler';
            $highlights = Database::getInstance()
                ->prepare($sql)->execute($this->liga);
        }

        $results = [];

        while ($highlights->next()) {
            // Bei Ranking "nur für eine Mannschaft" auf der Mannschaftsseite
            // also falls $this->mannschaft > 0 unter der $highlights->spieler_id
            // ablegen (dem "Mannschaftsspieler") , ansonsten unter der
            // $highlights->memberid (der dahinter stehenden Person, dem "Member")
            if ($this->mannschaft > 0) {
                $credit_to = $highlights->spieler_id;
            } else {
                $credit_to = $highlights->member_id;
            }
            // Initialisieren
            if (!isset($results[$credit_to])) {
                $results[$credit_to] = [
                    'name'          => DCAHelper::makeSpielerNameFromParts($highlights->member_firstname, $highlights->member_lastname, $highlights->member_anonymize),
                    'mannschaft'    => [$highlights->mannschaft => 0], // bei Wechsel der Mannschaft eines Spielers innerhalb der Saison können es mehrere Mannschaften sein!
                    'hl_171'        => 0,  // Anzahl
                    'hl_180'        => 0,  // dito
                    'hl_highfinish' => [], // Liste der Highfinishes
                    'hl_shortleg'   => [], // dito
                    'hl_punkte'     => [], // List der einzelnen Punkte
                    'hl_rang'       => 0,
                    'member_id'     => $highlights->member_id,
                    'spieler_id'    => $highlights->spieler_id,
                    'active'        => false,
                    'CSS'           => $highlights->member_anonymize ? '' : trim($highlights->member_gender . ' ' . ($highlights->sjugendlich ? 'youth' : '')),
                ];
            }
            // Spieler hat in versch. Mannschaften gespielt?
            ++$results[$credit_to]['mannschaft'][$highlights->mannschaft];
            // Spieler ist (noch) aktiv?
            $results[$credit_to]['active'] |= $highlights->sactive && $highlights->mactive; // aktiver Spieler in einer aktiven Mannschaft

            // Aggregieren
            switch ($highlights->type) {
                case HighlightModel::TYPE_171:
                    $results[$credit_to]['hl_171'] += $highlights->value;
                    $results[$credit_to]['hl_punkte'][] = $highlights->value;
                    break;
                case HighlightModel::TYPE_180:
                    $results[$credit_to]['hl_180'] += $highlights->value;
                    $results[$credit_to]['hl_punkte'][] = $highlights->value;
                    break;
                case HighlightModel::TYPE_HIGHFINISH:
                    $results[$credit_to]['hl_highfinish'][] = $highlights->value;
                    $results[$credit_to]['hl_punkte'][] = explode(',', $highlights->value);
                    break;
                case HighlightModel::TYPE_SHORTLEG:
                    $results[$credit_to]['hl_shortleg'][] = $highlights->value;
                    $results[$credit_to]['hl_punkte'][] = explode(',', $highlights->value);
                    break;
            }
        }

        // Daten "normieren" und Punkte berechnen

        foreach ($results as $id => $data) {
            // Spieler nicht mehr aktiv?
            if (!$results[$id]['active']) {
                unset($results[$id]);
                continue;
            }
            switch ($this->rankingfield) {
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
                    $results[$id]['hl_punkte'] = array_map(function($val) {
                        $val = (int)$val;
                        // Wert > self::MAX_SHORTLEG_DARTS via 0 Punkte nicht berücksichtigen
                        if (self::MAX_SHORTLEG_DARTS < $val) {
                            return 0;
                        }
                        // mapping: kürzeres Leg == besser
                        $result = self::MAX_SHORTLEG_DARTS - $val + 1;
                        // "12" (9 Darter) bis "01" (20 Darts) für String-Sortierung
                        return $result < 10 ? "0$result" : "$result";
                    }, $results[$id]['hl_punkte']);

                    // kürzester Shortleg zuerst (nach Mapping => höchster Wert zuerst!)
                    rsort($results[$id]['hl_punkte']);
                    break;
                case HighlightModel::TYPE_ALL:
                    $results[$id]['hl_punkte'] = []; // wir sortieren hier nach Namen, brauchen also die Punkte nicht
                    $results[$id]['hl_shortleg'] = static::prettyPrintSorted($results[$id]['hl_shortleg'], 'ASC');
                    $results[$id]['hl_highfinish'] = static::prettyPrintSorted($results[$id]['hl_highfinish'], 'DESC');
            }
            // Spieler hat in versch. Mannschaften gespielt? Auflösen:
            $mannschaftenlabel = implode(', ', array_keys($results[$id]['mannschaft']));
            // $results[$id]['test'] = $mannschaftenlabel;
            $results[$id]['mannschaft'] = $mannschaftenlabel;
        }

        // Sortieren

        // print '<pre>'.print_r(['rankingtype'=>$this->rankingtype, $results], true) .'</pre>';

        if (HighlightModel::TYPE_ALL == $this->rankingfield) {
            uasort($results, function($a, $b) {
                // ohne spezielle Punkteregel: nach Namen sortieren
                return $a['name'] <=> $b['name'];
            });
        } else {
            uasort($results, function($a, $b) {
                // Bei Shortleg und Highfinish können wir die sortierten Werte (== ['hl_punkte']-Eintrag)
                // aneinander hängen und als String sortieren, weil:
                // * bei Shortleg oben bereits "gemap" wurde (kurze Legs -> hohe Werte) und
                //   die (gemapten) Werte zwischen "12" (9 Darter) und "01" (20 Darts) liegen
                // * bei Highfinish die Nebenbedingung gilt, daß die Werte > 100 und <180 sind!
                //   Es kann also nicht das "Zahlen als Strings sortiert"-Problem auftauchen --
                //   falsch: "1", "11", "2" vs. korrekt: 1, 2, 11.
                if (HighlightModel::TYPE_SHORTLEG == $this->rankingfield || HighlightModel::TYPE_HIGHFINISH == $this->rankingfield) {
                    return strcmp(implode('', $b['hl_punkte']), implode('', $a['hl_punkte']));
                }
                // Bei allen anderen Rankings hat ['hl_punkte'] nur einene numerischen Eintrag, nach dem
                // wir sortieren können:
                return $b['hl_punkte'][0] <=> $a['hl_punkte'][0];
            });
        }

        $lastpunkte = PHP_INT_MAX;
        $rang = 0;
        $rang_skip = 1;

        foreach ($results as $i => $data) {
            // die Konkatenierten Punktwerte als "Prüfstring" für die Feststellung,
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

        $this->Template->rankingtype = 'spieler';
        if ($this->mannschaft > 0) {
            $this->Template->rankingsubtype = 'mannschaft';
        } else {
            $this->Template->rankingsubtype = 'alle';
        }

        $this->Template->listitems = $results;
    }

    /**
     * flatten an array. E.g. [1,2,[3,4],5] becomes [1,2,3,4,5].
     * Additionally the array elements will be casted to integers.
     *
     * @param array $a
     *
     * @return array (of integers)
     */
    protected static function flattenToIntArray(array $a)
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($a));
        $result = [];
        foreach ($it as $v) {
            $result[] = (int)$v;
        }

        return $result;
    }

    /**
     * TODO (?): "13,13,13,14,14" als "13 (3x), 14 (2x)" ausgeben.
     *
     * @param string|array $value
     * @param string $order
     *
     * @return string
     */
    protected static function prettyPrintSorted($value, $order)
    {
        if (\is_array($value)) {
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

        // return implode(',', $data);
        return static::compressResultsArray($data);
    }

    /**
     * Compress an array of results
     * i.e. display "15,15,15,16,..." as "3x15,16,...".
     *
     * @param array $data
     *
     * @return string
     */
    protected static function compressResultsArray($data)
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
