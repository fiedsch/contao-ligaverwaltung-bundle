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

use Contao\Config;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Date;
use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Fiedsch\JsonWidgetBundle\Traits\YamlGetterSetterTrait;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Helper\UrlHelper;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use RuntimeException;
use Exception;
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
 * @property bool   $erfasst
 * @property bool   $postponed
 * @property string $kommentar
 * @property string $begegnung_data
 *
 * @method LigaModel getRelated($strKey, array $arrOptions = array())
 * @method static BegegnungModel|null findById($id, array $opt=array())
 */
class BegegnungModel extends Model
{
    use YamlGetterSetterTrait;

    /**
     * @throws Exception
     */
    public function __construct($objResult = null)
    {
        parent::__construct($objResult);
    }

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
        $result = [0, 0];
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            [$home, $away] = $spiel->getScore();
            $result[0] += $home;
            $result[1] += $away;
        }

        return sprintf('%d:%d', $result[0], $result[1]);
    }

    public function getScoreHome(): string
    {
        if (!$this->published) {
            return '';
        }
        $spiele = SpielModel::findByPid($this->id);

        if (!$spiele) {
            return '';
        }
        $result = 0;
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            [$home, $away] = $spiel->getScore();
            $result += $home;
        }

        return (string)$result;
    }

    public function getScoreAway(): string
    {
        if (!$this->published) {
            return '';
        }
        $spiele = SpielModel::findByPid($this->id);

        if (!$spiele) {
            return '';
        }
        $result = 0;
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            [$home, $away] = $spiel->getScore();
            $result += $away;
        }

        return (string)$result;
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
            return 'Heim und Gast nicht angetreten';
        }

        if ($is_noshow_home) {
            return 'Heim nicht angetreten';
        }

        if ($is_noshow_away) {
            return 'Gast nicht angetreten';
        }

        return sprintf('%d:%d', $result[0], $result[1]);
    }


    public function getLegsHome(): string
    {
        if (!$this->published) {
            return '';
        }
        $spiele = SpielModel::findByPid($this->id);

        if (!$spiele) {
            return '';
        }
        $result = 0;
        $eingesetzte_spieler =[];
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            [$home, $away] = $spiel->getLegs();
            $result += $home;
            // Initialisierung
            $eingesetzte_spieler[$spiel->home] = $eingesetzte_spieler[$spiel->home] ?? 0;

            ++$eingesetzte_spieler[$spiel->home];
        }
        // nicht angetreten?
        $is_noshow = 1 === count(array_keys($eingesetzte_spieler)) && 0 === array_keys($eingesetzte_spieler)[0];

        if ($is_noshow) {
            return ''; // 'Heim nicht angetreten';
        }

        return (string)$result;
    }

    public function getLegsAway(): string
    {
        if (!$this->published) {
            return '';
        }
        $spiele = SpielModel::findByPid($this->id);

        if (!$spiele) {
            return '';
        }
        $result = 0;
        $eingesetzte_spieler =[];
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            [$home, $away] = $spiel->getLegs();
            $result += $away;
            // Initialisierung
            $eingesetzte_spieler[$spiel->away] = $eingesetzte_spieler[$spiel->away] ?? 0;

            ++$eingesetzte_spieler[$spiel->away];
        }
        // nicht angetreten?
        $is_noshow = 1 === count(array_keys($eingesetzte_spieler)) && 0 === array_keys($eingesetzte_spieler)[0];

        if ($is_noshow) {
            return ''; // 'Gast nicht angetreten';
        }

        return (string)$result;
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
                    $this->getRelated('home')?->name ?? DCAHelper::DOES_NOT_EXIST,
                    $this->getRelated('away')?->name,
                    $this->getRelated('pid')->name,
                    $this->getRelated('pid')->getRelated('saison')->name,
                    $this->spiel_am ? ', ' : '',
                        $this->spiel_am ? Date::parse(Config::get('dateFormat'), $this->spiel_am) : ''
                );
                //break;

            case 'medium':
                return sprintf('%s:%s (%s %s)',
                    $this->getRelated('home')?->name ?? DCAHelper::DOES_NOT_EXIST,
                    $this->getRelated('away')?->name,
                    $this->getRelated('pid')->name,
                    $this->getRelated('pid')->getRelated('saison')->name
                );
                //break;

            case 'short':
            default:
                return sprintf('%s:%s',
                    $this->getRelated('home')?->name ?? DCAHelper::DOES_NOT_EXIST,
                    $this->getRelated('away')?->name
                );
            //break;
        }
    }

    /**
     * @deprecated do not generate HTML which forces us to use |raw in templates. Use self::getScoreLinkTarget()
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

            $urlGenerator = System::getContainer()->get('contao.routing.content_url_generator');
            // $urlGenerator = System::getContainer()->get('contao.routing.page_url_generator');

            $url = $urlGenerator->generate($spielberichtpage, ['id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL);

            return sprintf("<a href='%s'>%s</a>",
                $url,
                $score
            );
        }

        return $score;
    }

    public function getScoreLinkTarget(): string
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

            /** @var ContentUrlGenerator $contentUrlGenerator */
            $contentUrlGenerator = System::getContainer()->get('contao.routing.content_url_generator');

            // if (Config::get('folderUrl')) { // TODO (?) root-Page::useFolderUrl Einstellung verwenden
            //     return $spielberichtpage->getFrontendUrl('/id/' . $this->id);
            //     //return $this->urlGenerator->generate($spielberichtpage, ['id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL);
            // } else {
            //     return $spielberichtpage->getFrontendUrl('?id=' . $this->id);
            //     // return $this->urlGenerator->generate($spielberichtpage, ['id' => $this->id], UrlGeneratorInterface::ABSOLUTE_URL);
            // }
            $url = $contentUrlGenerator->generate($spielberichtpage, ['id' => $this->id], UrlGeneratorInterface::ABSOLUTE_PATH);
            $url = UrlHelper::asFolderUrl($url, 'id', $spielberichtpage->urlSuffix);

            return $url;
        }

        return $score;
    }

    public function isSpielfrei(): bool
    {
        return !$this->away || !$this->home;
    }

    public function isAlreadyPlayed(): bool
    {
        return !empty($this->begegnung_data) && $this->published;
    }

    /**
     * Note: kann erst ermittelt werden, nachdem die Begegnung erfallst wurde.
     */
    public function isNoShowHome(): bool
    {
        return $this->isNoShow('home');
    }

    public function isNoShowAway(): bool
    {
        return $this->isNoShow('away');
    }

    /**
     * @throws RuntimeException
     */
    protected function isNoShow(string $team): bool
    {
        if ($this->isSpielfrei()) {
            return false;
        }
        if (!$this->published) {
            return false;
        }
        if (!in_array($team, ['home', 'away'])) {
            throw new RuntimeException(sprintf("Invalid parameter '%s'. Expected 'home' or 'away'", $team));
        }

        if ($this->begegnung_data) {
            try {
                $app_data = Yaml::parse($this->begegnung_data)['app_data'] ?? [];
            } catch (ParseException $e) {
                throw new RuntimeException($e->getMessage());
            }
        } else {
            $app_data = [];
        }
        if (empty ($app_data)) {
            return false;
        }

        // In der Aufstellung sind beim angegebenen Team keine Spieler hinterlegt == Team ist nicht angetreten
        return empty(array_filter($app_data[$team]['lineup'], fn($e) => $e>0));
    }

}
