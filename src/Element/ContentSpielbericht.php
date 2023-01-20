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

/**
 * Content element "Spielbericht".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\MemberModel;
use Fiedsch\LigaverwaltungBundle\Helper\DCAHelper;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\HighlightModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielModel;
use function Symfony\Component\String\u;

/**
 * @property int $begegnung
 */
class ContentSpielbericht extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielbericht';

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;
            $begegnunglabel = BegegnungModel::findById($this->begegnung) ? BegegnungModel::findById($this->begegnung)->getLabel('full') : 'Begegnung nicht gefunden!';
            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['spielbericht'][0])->upper()." $begegnunglabel ###";
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
    public function compile(): void
    {
        $begegnung = BegegnungModel::findById($this->begegnung);

        if (!$begegnung) {
            return;
        }

        $this->Template->begegnunglabel = $begegnung->getLabel('full');

        $this->Template->home = $begegnung->getRelated('home')->name;
        $this->Template->away = $begegnung->getRelated('away')->name;

        $this->Template->spielergebnisse = $this->compileSpielergebnsisse($begegnung);

        $this->Template->highlights = $this->compileHighlights($begegnung);
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    protected function compileSpielergebnsisse(BegegnungModel $begegnung)
    {
        if (!$begegnung->published) {
            return [];
        }
        $spiele = SpielModel::findByPid($begegnung->id, ['order' => 'slot ASC']);

        if (!$spiele) {
            return [];
        }
        $spielergebnisse = [];
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            // Einzel (und erster Spieler Doppel)
            /** @var SpielerModel $home */
            if ($home = $spiel->getRelated('home')) {
                /** @var MemberModel $member */
                $member = $home->getRelated('member_id');
                $homeplayer = DCAHelper::makeSpielerName($member);
            } else {
                $homeplayer = '-';
            }
            /** @var SpielerModel $away */
            if ($away = $spiel->getRelated('away')) {
                /** @var \MemberModel $member */
                $member = $away->getRelated('member_id');
                $awayplayer = DCAHelper::makeSpielerName($member);
            } else {
                $awayplayer = '-';
            }

            if (SpielModel::TYPE_DOPPEL === $spiel->spieltype) {
                // Doppel (zweiter Spieler)
                /** @var SpielerModel $home */
                if ($home = $spiel->getRelated('home2')) {
                    /** @var \MemberModel $member */
                    $member = $home->getRelated('member_id');
                    $homeplayer .= '/'.DCAHelper::makeSpielerName($member);
                } else {
                    $homeplayer .= '/-';
                }
                /** @var SpielerModel $away */
                if ($away = $spiel->getRelated('away2')) {
                    /** @var \MemberModel $member */
                    $member = $away->getRelated('member_id');
                    $awayplayer .= '/'.DCAHelper::makeSpielerName($member);
                } else {
                    $awayplayer .= '/-';
                }
            }

            $homeCssClass = 'draw';
            $awayCssClass = 'draw';
            $score = '-';

            if ($spiel->score_home > 0 || $spiel->score_away > 0) {
                $homeCssClass = $spiel->score_home > $spiel->score_away ? 'winner' : 'loser';
                $awayCssClass = $spiel->score_home > $spiel->score_away ? 'loser' : 'winner';
                $score = sprintf('%d:%d', $spiel->score_home, $spiel->score_away);
            }

            $spielergebnisse[] = [
                'home' => sprintf('<span class="%s">%s</span>', $homeCssClass, $homeplayer),
                'away' => sprintf('<span class="%s">%s</span>', $awayCssClass, $awayplayer),
                'type' => SpielModel::TYPE_EINZEL === $spiel->spieltype ? 'einzel' : 'doppel',
                'score' => $score,
            ];
        }

        return $spielergebnisse;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    protected function compileHighlights(BegegnungModel $begegnung)
    {
        if (!$begegnung->published) {
            return [];
        }
        $highlights = HighlightModel::findBy(['begegnung_id=?', 'spieler_id<>?'], [$begegnung->id, 0]);

        if (!$highlights) {
            return [];
        }
        $result = [];
        /** @var HighlightModel $highlight */
        foreach ($highlights as $highlight) {
            $result[$highlight->spieler_id]['highlights'][$highlight->type] = $highlight->value;

            if (!isset($result[$highlight->spieler_id]['name'])) {
                $spieler = SpielerModel::findById($highlight->spieler_id);
                // Zusatzcheck: verwaiste Highlight-Einträge
                if ($spieler) {
                    $result[$highlight->spieler_id]['name'] = $spieler->getName();
                    $result[$highlight->spieler_id]['team'] = $spieler->getRelated('pid')->name;
                }
            }
        }
        // make sure, all fields are set so we can access them in the template without checking
        foreach ($result as $spielerId => &$data) {
            foreach (HighlightModel::ALL_TYPES as $highlight) {
                $data['highlights'][$highlight] = $data['highlights'][$highlight] ?? '';
            }
        }

        uasort(
            $result,
            static function ($a, $b) {
                return $a['name'] <=> $b['name'];
            }
        );

        return $result;
    }
}
