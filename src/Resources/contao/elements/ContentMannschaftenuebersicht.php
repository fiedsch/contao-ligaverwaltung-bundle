<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Liste aller Mannschaften einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\ContentElement;
use Contao\BackendTemplate;
use Contao\LigaModel;
use Contao\MannschaftModel;
use Contao\SpielerModel;
use Patchwork\Utf8;

class ContentMannschaftenuebersicht extends ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftenuebersicht';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = "### " . Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['mannschaftenuebersicht'][0]) . " ###";
            // $objTemplate->id = $this->id;
            // $objTemplate->link = 'the text that will be linked with href';
            // $objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the content element
     */
    public function compile()
    {
        if (!$this->saison) {
            return;
        }
        $ligen = LigaModel::findBy(
            ['saison IN (' . join(",", array_map('intval', deserialize($this->saison))) . ')'],
            [],
            ['order' => 'tl_liga.spielstaerke ASC, tl_liga.name ASC']);

        $arrLigen = [];
        $arrDetails = [];

        foreach ($ligen as $liga) {
            //$mannschaften = MannschaftModel::findByLiga($liga->id, ['order' => 'name ASC']);
            $mannschaften = MannschaftModel::findBy(['liga=?','active=?'],[$liga->id,'1'], ['order' => 'name ASC']);
            if ($mannschaften === null) {
                continue;
            }
            $arrLigen[$liga->id] = $liga->name;
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
                    'tc'         => $arrTc,
                    'spielort'   => [
                        'name'    => $spielort->name,
                        'phone'   => $spielort->phone,
                        'website' => $spielort->website,
                    ],
                ];
            }
        }

        $this->Template->ligen = $arrLigen;
        $this->Template->details = $arrDetails;
    }

}