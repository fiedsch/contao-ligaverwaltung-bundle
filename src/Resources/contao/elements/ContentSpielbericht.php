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

/**
 * Content element "Spielbericht".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\BackendTemplate;
use Contao\BegegnungModel;
use Contao\ContentElement;
use Contao\SpielModel;
use Patchwork\Utf8;

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
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['spielbericht'][0])." $begegnunglabel ###";
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
        $begegnung = BegegnungModel::findById($this->begegnung);

        if (null === $begegnung) {
            return;
        }

        $this->Template->begegnunglabel = $begegnung->getLabel('full');

        $this->Template->home = $begegnung->getRelated('home')->name;
        $this->Template->away = $begegnung->getRelated('away')->name;

        $this->Template->spielergebnisse = [];

        $spiele = SpielModel::findByPid($this->begegnung, ['order' => 'slot ASC']);
        if (null === $spiele) {
            return;
        }
        $spielergebnisse = [];
        foreach ($spiele as $spiel) {
            // Einzel (und erster Spieler Doppel)
            if ($home = $spiel->getRelated('home')) {
                /** @var \Contao\MemberModel $member */
                $member = $home->getRelated('member_id');
                $homeplayer = DCAHelper::makeSpielerName($member);
            } else {
                $homeplayer = '-';
            }
            if ($away = $spiel->getRelated('away')) {
                $member = $away->getRelated('member_id');
                $awayplayer = DCAHelper::makeSpielerName($member);
            } else {
                $awayplayer = '-';
            }
            if (SpielModel::TYPE_DOPPEL === $spiel->spieltype) {
                // Doppel (zweiter Spieler)
                if ($home = $spiel->getRelated('home2')) {
                    $member = $home->getRelated('member_id');
                    $homeplayer .= '/'.DCAHelper::makeSpielerName($member);
                } else {
                    $homeplayer .= '/-';
                }
                if ($away = $spiel->getRelated('away2')) {
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
        $this->Template->spielergebnisse = $spielergebnisse;
    }
}
