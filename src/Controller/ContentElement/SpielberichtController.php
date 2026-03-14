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

namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\MemberModel;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Model\HighlightModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Model\SpielModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Exception;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'spielbericht',
    category: 'ligaverwaltung',
    template: 'content_element/spielbericht'
)]
class SpielberichtController extends AbstractContentElementController
{
    use TlModeTrait;

    /**
     * @throws Exception
     */
    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->setData($template, $model);

        return $template->getResponse();
    }

    /**
     * @throws Exception
     */
    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['spielbericht'][0])->upper().' ###';

        $begegnung = BegegnungModel::findById($model->begegnung);
        if (!$begegnung) {
            $template->subject = sprintf('Begegnung mit der ID %d existiert nicht mehr', $model->begegnung);
            return;
        } else {
            $template->subject = $begegnung->getLabel();
        }
        if ($this->isBackend()) {
            return;
        }

        $template->begegnunglabel = $begegnung->getLabel();
        $template->home = $begegnung->getRelated('home')->name;
        $template->away = $begegnung->getRelated('away')->name;

        $template->spielergebnisse = $this->compileSpielergebnsisse($begegnung);

        $template->highlights = $this->compileHighlights($begegnung);

    }

    /**
     * @throws Exception
     */
    protected function compileSpielergebnsisse(BegegnungModel $begegnung): array
    {
        if (!$begegnung->published) {
            return [];
        }

        $spiele = SpielModel::findByPid($begegnung->id, ['order' => 'slot ASC']);

        if (!$spiele) {
            return [];
        }
        // Für die "Metainformationen wie "Aufstellung" ("wer hat an welcher Position gespielt" + "Einwechslungen" etc.)
        $begegnung_data = Yaml::parse($begegnung->begegnung_data)['app_data'] ?? [];

        //dd($begegnung_data);
        $playerMap = [
            'home' => $this->mapAufstellungData($begegnung_data['home']['available'], $begegnung_data['home']['lineup'], 'H'),
            'away' => $this->mapAufstellungData($begegnung_data['away']['available'], $begegnung_data['away']['lineup'], 'G'),
        ];
        // dd($playerMap);

        $spielergebnisse = [];
        /** @var SpielModel $spiel */
        foreach ($spiele as $spiel) {
            // dd($playerMap['home'][$spiel->home]);
            // Einzel (und erster Spieler Doppel)
            /** @var SpielerModel $home */
            if ($home = $spiel->getRelated('home')) {
                /** @var MemberModel $member */
                $member = $home->getRelated('member_id');
                $homeplayer = sprintf('(%s) %s', $playerMap['home'][$spiel->home]['position'] ?? '-', DCAHelper::makeSpielerName($member));
            } else {
                $homeplayer = '-';
            }
            /** @var SpielerModel $away */
            if ($away = $spiel->getRelated('away')) {
                /** @var MemberModel $member */
                $member = $away->getRelated('member_id');
                $awayplayer = sprintf('(%s) %s', $playerMap['away'][$spiel->away]['position'] ?? '-', DCAHelper::makeSpielerName($member));
            } else {
                $awayplayer = '-';
            }

            if (SpielModel::TYPE_DOPPEL === (string)$spiel->spieltype) {
                // Doppel (zweiter Spieler)
                /** @var SpielerModel $home */
                if ($home = $spiel->getRelated('home2')) {
                    /** @var MemberModel $member */
                    $member = $home->getRelated('member_id');
                    $homeplayer .= '/'.sprintf('(%s) %s',  $playerMap['home'][$spiel->home2]['position'] ?? '-', DCAHelper::makeSpielerName($member));
                } else {
                    $homeplayer .= '/-';
                }
                /** @var SpielerModel $away */
                if ($away = $spiel->getRelated('away2')) {
                    /** @var MemberModel $member */
                    $member = $away->getRelated('member_id');
                    $awayplayer .= '/'.sprintf('(%s) %s', $playerMap['away'][$spiel->away2]['position'] ?? '-', DCAHelper::makeSpielerName($member));
                } else {
                    $awayplayer .= '/-';
                }
            }

            $homeCssClass = 'draw';
            $awayCssClass = 'draw';

            if ($spiel->score_home > 0 || $spiel->score_away > 0) {
                $homeCssClass = $spiel->score_home > $spiel->score_away ? 'winner' : 'loser';
                $awayCssClass = $spiel->score_home > $spiel->score_away ? 'loser' : 'winner';
            }

            $spielergebnisse[] = [
                'player_home' => $homeplayer,
                'css_home' => $homeCssClass,
                'player_away' => $awayplayer,
                'css_away' => $awayCssClass,
                'type' => SpielModel::TYPE_EINZEL === (string)$spiel->spieltype ? 'einzel' : 'doppel',
                'score_home' => $spiel->score_home,
                'score_away' => $spiel->score_away,
            ];
        }

        return ['ergebnisse' => $spielergebnisse,'raw_data' => $begegnung_data, 'player_map' => $playerMap];
    }

    /**
     * @throws Exception
     */
    protected function compileHighlights(BegegnungModel $begegnung): array
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
        // make sure, all fields are set, so we can access them in the template without checking
        foreach ($result as &$data) {
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

    private function mapAufstellungData(array $originalDataAvailable, array $originalDataLineup, string $prefix): array
    {
        //dd($originalDataAvailable, $originalDataLineup);
        $result = [];
        foreach ($originalDataLineup as $position => $spieler_id) {
            $spielerdata = array_first(array_filter($originalDataAvailable, fn ($el) => $el['id'] === $spieler_id));
            $result[$spieler_id] = [
                'name' => $spielerdata['name'],
                'pass' => $spielerdata['pass'],
                'id' => $spielerdata['id'],
                'position' => sprintf('%s%d', $prefix, $position+1),
            ];
        }

        return $result;
    }

}
