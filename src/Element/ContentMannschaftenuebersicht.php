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
 * Content element "Liste aller Mannschaften einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Exception;
use Fiedsch\LigaverwaltungBundle\Trait\TlModeTrait;
use function Symfony\Component\String\u;

/**
 * @property int $saison
 */
class ContentMannschaftenuebersicht extends ContentElement
{
    use TlModeTrait;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftenuebersicht';

    public function generate(): string
    {
        if ($this->isBackend()) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftenuebersicht'][0])->upper().' ###';
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
     * @throws Exception
     */
    public function compile(): void
    {
        if (!$this->saison) {
            return;
        }
        $ligen = LigaModel::findBy(
            ['saison IN ('.implode(',', array_map('intval', StringUtil::deserialize($this->saison))).')'],
            [],
            ['order' => 'tl_liga.spielstaerke ASC, tl_liga.name ASC']
        );

        $arrLigen = [];
        $arrDetails = [];

        foreach ($ligen as $liga) {
            //$mannschaften = MannschaftModel::findByLiga($liga->id, ['order' => 'name ASC']);
            $mannschaften = MannschaftModel::findBy(['liga=?', 'active=?'], [$liga->id, '1'], ['order' => 'name ASC']);

            if (null === $mannschaften) {
                continue;
            }
            $saison = SaisonModel::findById(LigaModel::findById($liga->id)?->saison)?->name;
            $arrLigen[$liga->id] = $liga->name . ' ' . $saison;
            $arrDetails[$liga->id] = [];

            foreach ($mannschaften as $mannschaft) {
                $arrTc = [];
                $spieler = SpielerModel::findBy(
                    ['pid=?', '(teamcaptain=1 OR co_teamcaptain=1)'],
                    [$mannschaft->id],
                    ['order' => 'tl_spieler.teamcaptain DESC, tl_spieler.co_teamcaptain DESC']
                );

                if ($spieler) {
                    foreach ($spieler as $sp) {
                        $arrTc[] = $sp->getTcDetails();
                    }
                }
                $spielort = $mannschaft->getRelated('spielort');
                $arrDetails[$liga->id][] = [
                    'mannschaft' => $mannschaft->getLinkedName(),
                    'tc' => $arrTc,
                    'spielort' => [
                        'name' => $spielort->name,
                        'phone' => $spielort->phone,
                        'website' => $spielort->website,
                        'address' => [
                            'street' => $spielort->street,
                            'postal' => $spielort->postal,
                            'city' => $spielort->city,
                        ],
                    ],
                ];
            }
        }

        $this->Template->ligen = $arrLigen;
        $this->Template->details = $arrDetails;
    }
}
