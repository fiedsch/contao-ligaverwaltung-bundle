<?php

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

/**
 * Content Element "Mannschaftsseite".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\System;
use Exception;
//use Fiedsch\LigaverwaltungBundle\Controller\ContentElement\ContentRanking;
use Fiedsch\LigaverwaltungBundle\Controller\ContentElement\RankingController;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Fiedsch\LigaverwaltungBundle\Trait\TlModeTrait;
use function Symfony\Component\String\u;

class ContentMannschaftsseite extends ContentElement
{
    use TlModeTrait;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftsseite';

    /**
     * @throws Exception
     *
     * @return string
     */
    public function generate(): string
    {
        if ($this->isBackend()) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $headline = $this->headline;

            if (!$headline) {
                $mannschaftModel = MannschaftModel::findById($this->mannschaft);
                $headline = $mannschaftModel->getFullName();
            }

            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftsseite'][0])->upper().' ###';
            $objTemplate->id = $this->id;
            $objTemplate->link = $headline;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * @throws Exception
     */
    public function compile(): void
    {
        $mannschaftModel = MannschaftModel::findById($this->mannschaft);

        $this->addDescriptionToTlHead('Alles zur Mannschaft '.$mannschaModel->name);

        // Spielortinfo
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielortinfo';
        $contentModel->spielort = $mannschaftModel->spielort;
        $contentModel->headline = [
            'value' => 'Spielort '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $contentElement = new ContentSpielortinfo($contentModel);
        $this->Template->spielortinfo = $contentElement->generate();

        // Spielerliste
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielerliste';
        $contentModel->mannschaft = $this->mannschaft;
        $contentModel->showdetails = '1';
        $contentModel->headline = [
            'value' => 'Spielerliste '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $contentElement = new ContentSpielerliste($contentModel);
        $this->Template->spielerliste = $contentElement->generate();

        // Spielplan
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielplan';
        $contentModel->liga = $mannschaftModel->liga;
        $contentModel->mannschaft = $mannschaftModel->id;
        $contentModel->headline = [
            'value' => 'Spielplan '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $contentElement = new ContentSpielplan($contentModel);
        $this->Template->spielplan = $contentElement->generate();

        // Einzelspielerrangliste
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'ranking';
        $contentModel->liga = $mannschaftModel->liga;
        $contentModel->mannschaft = $mannschaftModel->id;
        $contentModel->rankingtype = RankingController::RANKING_TYPE_SPIELER;
        $contentModel->headline = [
            'value' => 'Einzelspieler Ranking '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $contentElement = new RankingController(System::getContainer()->get('fiedsch_ligaverwaltung.rankinghelper'));

        $this->Template->ranking = $contentElement->getResponse($this->Template, $contentModel, $this->requestStack->getCurrentRequest())->getContent();

        // Highlights
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'highlightranking';
        $contentModel->liga = $mannschaftModel->liga;
        $contentModel->rankingtype = RankingController::RANKING_TYPE_SPIELER;
        $contentModel->rankingfield = 99; // alle zusammen
        $contentModel->mannschaft = $mannschaftModel->id;
        $contentModel->headline = [
            'value' => 'Highlights '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $contentElement = new ContentHighlightRanking($contentModel);
        $this->Template->highlightranking = $contentElement->generate();

        $this->Template->mannschaft_name = $mannschaftModel->name;
        $liga = LigaModel::findById($mannschaftModel->liga);
        $this->Template->liga = $liga?->name;
        $this->Template->saison = SaisonModel::findById($liga?->saison)?->name;

    }

    /**
     * Add the following to fe_page.html5 or (if using Bootsrap for Contao) to fe_bootstrap_xx.html5:
     * ```
     * <?php if (!strpos($head, "description") === false): ?>
     * <meta name="description" content="<?php echo $this->description; ?>">
     * <?php endif; ?>
     * ```.
     */
    protected function addDescriptionToTlHead(string $content): void
    {
        if ($GLOBALS['TL_HEAD'] ?? false) {
            foreach ($GLOBALS['TL_HEAD'] as $i => $entry) {
                if (str_contains($entry, 'description')) {
                    unset($GLOBALS['TL_HEAD'][$i]);
                }
            }
        }
        $GLOBALS['TL_HEAD'][] = sprintf('<meta name="description" content="%s">', $content);
    }
}
