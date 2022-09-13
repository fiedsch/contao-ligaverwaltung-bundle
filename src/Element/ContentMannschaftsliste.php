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

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use function Symfony\Component\String\u;

/**
 * Content element "Liste aller Mannschaften einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 *
 * @property int $liga
 */
class ContentMannschaftsliste extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_mannschaftsliste';

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

            $liga = LigaModel::findById($this->liga);
            if ($liga) {
                $subject = sprintf('%s %s %s',
                    $liga->getRelated('pid')->name,
                    $liga->name,
                    $liga->getRelated('saison')->name
                );
            } else {
                $subject = sprintf('Liga mit der ID=%d (ex. nicht mehr', $this->liga);
            }
            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftsliste'][0])->upper()." $subject ###";

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the content element.
     */
    public function compile(): void
    {
        if (!$this->liga) {
            return;
        }
        $mannschaften = MannschaftModel::findByLiga($this->liga, ['order' => 'name ASC']);

        if (!$mannschaften) {
            return;
        }

        $listitems = [];

        foreach ($mannschaften as $mannschaft) {
            if ('1' === $mannschaft->active) {
                $listitem = $mannschaft->getLinkedName();
                $listitems[] = $listitem;
            }
        }

        $this->Template->listitems = $listitems;
    }
}
