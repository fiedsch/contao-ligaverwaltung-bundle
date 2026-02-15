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

namespace Fiedsch\Ligaverwaltung\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\StringUtil;
use Contao\System;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielortModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use function Symfony\Component\String\u;

class ContentSpielortseite extends ContentElement
{
    use TlModeTrait;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielortseite';

    /**
     * @return string
     */
    public function generate(): string
    {
        if ($this->isBackend()) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $headline = $this->headline;

            if (!$headline) {
                $spielortModel = SpielortModel::findById($this->spielort);
                $headline = $spielortModel->name;
            }

            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['spielortseite'][0])->upper().' ###';
            $objTemplate->id = $this->id;
            $objTemplate->link = $headline;
            $objTemplate->href = 'TODO'; // or not when switching to CE contoller

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    public function compile(): void
    {
        $spielortModel = SpielortModel::findById($this->spielort);

        $this->addInfoToHead($spielortModel->name);

        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielortinfo';
        $contentModel->spielort = $spielortModel->id;
        $contentModel->headline = [
            'value' => 'Spielort',
            'unit'  => 'h1',
        ];
        $this->Template->spielortinfo = Controller::getContentElement($contentModel);

        $saisons = StringUtil::deserialize($this->saison);
        $saison_lookup = [];
        $result = [];

        foreach ($saisons as $saison) {
            $saisonModel = SaisonModel::findById($saison);
            $saison_lookup[$saisonModel->id] = $saisonModel->name;

            $mannschaften = MannschaftModel::findBy(
                ['spielort=?', 'saison=?', 'active=?'],
                [$spielortModel->id, $saison, '1'],
                ['order' => 'name ASC']
            );
            foreach ($mannschaften ?? [] as $mannschaft) {
                $liga = LigaModel::findById($mannschaft->liga);
                $ligen_lookup[$liga?->id ?? 0] = $liga;

                $result[$saison][] = [
                    'link' => $mannschaft->getLinkedName(),
                    'liga' => $ligen_lookup[$mannschaft->liga]?->name,
                    'saison' => $saison_lookup[$liga->saison] ?? '',
                ];
            }
        }

        $this->Template->mannschaften = $result;
    }


    protected function addInfoToHead(string $spielortName): void
    {
        $responseContext = System::getContainer()->get('contao.routing.response_context_accessor')->getResponseContext();
        $htmlHeadBag = $responseContext->get(HtmlHeadBag::class);
        $htmlHeadBag->setMetaDescription('Alles zum Spielort '.$spielortName);
        $htmlHeadBag->setTitle($spielortName);
    }
}
