<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2023 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Model;

use Contao\Config;
use Contao\Controller;
use Contao\Date;
use Contao\Model;
use Contao\PageModel;
use Exception;
use Fiedsch\JsonWidgetBundle\Traits\YamlGetterSetterTrait;
use function count;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $home
 * @property int    $away
 * @property string $name
 * @property string $spiel_am
 * @property int    $tstamp
 * @property int    $spiel_tag
 * @property bool   $published
 * @property bool   $postponed
 *
 * @method static BegegnungModel|null findById($id, array $opt=array())
 */
class BegegnungModel extends Model
{
    use YamlGetterSetterTrait;

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_begegnung';

    /**
     * YAML-data column (@see YamlGetterSetterTrait).
     */
    protected static string $strYamlColumn = 'begegnung_data';

    /**
     * @return string Ergebnis der Begegnung
     */
    public function getScore(): string
    {
        if (!$this->published) {
            return '';
        }
        $spiele = SpielModel::findByPid($this->id);

        if (!$spiele) {
            return '';
        }
        //$eingesetzte_spieler = ['home'=>[], 'away'=>[]];
        $result = [0, 0];
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            [$home, $away] = $spiel->getScore();
            $result[0] += $home;
            $result[1] += $away;
            //$eingesetzte_spieler['home'][$spiel->home]++;
            //$eingesetzte_spieler['away'][$spiel->away]++;
        }

        // nicht angetreten?
        //$is_noshow_home = count(array_keys($eingesetzte_spieler['home'])) === 1 && array_keys($eingesetzte_spieler['home'])[0] === 0;
        //$is_noshow_away = count(array_keys($eingesetzte_spieler['away'])) === 1 && array_keys($eingesetzte_spieler['away'])[0] === 0;
        //if ($is_noshow_home) { return "Heim nicht angetreten"; } // siehe auch ce_spielplan.html5!
        //if ($is_noshow_away) { return "Gast nicht angetreten"; } //
        return sprintf('%d:%d', $result[0], $result[1]);
    }

    /**
     * @return string Ergebnis der Begegnung in Legs
     */
    public function getLegs(): string
    {
        if (!$this->published) {
            return '';
        }
        $spiele = SpielModel::findByPid($this->id);

        if (!$spiele) {
            return '';
        }
        $result = [0, 0];
        $eingesetzte_spieler = ['home' => [], 'away' => []];
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            [$home, $away] = $spiel->getLegs();
            $result[0] += $home;
            $result[1] += $away;
            // Initialisierung
            $eingesetzte_spieler['home'][$spiel->home] = $eingesetzte_spieler['home'][$spiel->home] ?? 0;
            $eingesetzte_spieler['away'][$spiel->away] = $eingesetzte_spieler['away'][$spiel->away] ?? 0;

            ++$eingesetzte_spieler['home'][$spiel->home];
            ++$eingesetzte_spieler['away'][$spiel->away];
        }
        // nicht angetreten?
        $is_noshow_home = 1 === count(array_keys($eingesetzte_spieler['home'])) && 0 === array_keys($eingesetzte_spieler['home'])[0];
        $is_noshow_away = 1 === count(array_keys($eingesetzte_spieler['away'])) && 0 === array_keys($eingesetzte_spieler['away'])[0];

        if ($is_noshow_home && $is_noshow_away) {
            return 'Nicht angetreten';
        }

        if ($is_noshow_home) {
            return 'Heim nicht angetreten';
        } // siehe auch ce_spielplan.html5!

        if ($is_noshow_away) {
            return 'Gast nicht angetreten';
        }

        return sprintf('%d:%d', $result[0], $result[1]);
    }

    /**
     * @param string $mode Art (Ausführlichkeit) des Labels ['full'|'medium'|'short']
     *
     * @throws Exception
     *
     * @return string
     */
    public function getLabel(string $mode = 'full'): string
    {
        switch ($mode) {
            case 'full':
                return sprintf('%s:%s (%s %s%s%s)',
                    $this->getRelated('home')?->name ?? MannschaftModel::MANNSCHAFT_DOES_NOT_EXIST,
                    $this->getRelated('away')?->name,
                    $this->getRelated('pid')->name,
                    $this->getRelated('pid')->getRelated('saison')->name,
                    $this->spiel_am ? ', ' : '',
                        $this->spiel_am ? Date::parse(Config::get('dateFormat'), $this->spiel_am) : ''
                );
                //break;

            case 'medium':
                return sprintf('%s:%s (%s %s)',
                    $this->getRelated('home')?->name ?? MannschaftModel::MANNSCHAFT_DOES_NOT_EXIST,
                    $this->getRelated('away')?->name,
                    $this->getRelated('pid')->name,
                    $this->getRelated('pid')->getRelated('saison')->name
                );
                //break;

            case 'short':
            default:
                return sprintf('%s:%s',
                    $this->getRelated('home')?->name ?? MannschaftModel::MANNSCHAFT_DOES_NOT_EXIST,
                    $this->getRelated('away')?->name
                );
            //break;
        }
    }

    /**
     * Zur "Mansnchaftsseite" verlinkter Name der Mannschaft.
     *
     * @return string
     */
    public function getLinkedScore(): string
    {
        if (!$this->published) {
            return '';
        }
        $score = $this->getScore();

        if ('' === $score) {
            return '';
        }
        $spielberichtpageId = Config::get('spielberichtpage');

        if ($spielberichtpageId) {
            $spielberichtpage = PageModel::findById($spielberichtpageId);

            if (Config::get('folderUrl')) {
                $url = Controller::generateFrontendUrl($spielberichtpage->row(), '/id/'.$this->id);
            } else {
                $url = Controller::generateFrontendUrl($spielberichtpage->row()).'?id='.$this->id;
            }

            return sprintf("<a href='%s'>%s</a>",
                $url,
                $score
            );
        }

        return $score;
    }
}
