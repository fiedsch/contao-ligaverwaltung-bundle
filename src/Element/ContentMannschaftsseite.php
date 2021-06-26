<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2021 Andreas Fieger
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
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Patchwork\Utf8;

class ContentMannschaftsseite extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftsseite';

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $headline = $this->headline;

            if (!$headline) {
                $mannschaftModel = MannschaftModel::findById($this->mannschaft);
                $headline = $mannschaftModel->getFullName();
            }

            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['mannschaftsseite'][0]).' ###';
            $objTemplate->id = $this->id;
            $objTemplate->link = $headline;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * @throws \Exception
     */
    public function compile(): void
    {
        $mannschaftModel = MannschaftModel::findById($this->mannschaft);

        $this->addDescriptionToTlHead('Alles zur Mannschaft '.$mannschaftModel->name);

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
        $contentModel->rankingtype = 2; // 'Spieler'
        $contentModel->headline = [
            'value' => 'Einzelspieler Ranking '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $contentElement = new ContentRanking($contentModel);
        $this->Template->ranking = $contentElement->generate();

        // Highlights
        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'highlightranking';
        $contentModel->liga = $mannschaftModel->liga;
        $contentModel->rankingtype = 2; // 'Spieler'
        $contentModel->rankingfield = 99; // alle zusammen
        $contentModel->mannschaft = $mannschaftModel->id;
        $contentModel->headline = [
            'value' => 'Highlights '.$mannschaftModel->name,
            'unit' => 'h2',
        ];
        $contentElement = new ContentHighlightRanking($contentModel);
        $this->Template->highlightranking = $contentElement->generate();

        $this->Template->mannschaft_name = $mannschaftModel->name;
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
        if ($GLOBALS['TL_HEAD']) {
            foreach ($GLOBALS['TL_HEAD'] as $i => $entry) {
                if (preg_match('/description/', $entry)) {
                    unset($GLOBALS['TL_HEAD'][$i]);
                }
            }
        }
        $GLOBALS['TL_HEAD'][] = sprintf('<meta name="description" content="%s">', $content);
    }
}
