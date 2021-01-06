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
 * Content element "Liste aller Mannschaften einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\ContentElement;
use Contao\Controller;
use Contao\PageModel;
use Contao\StringUtil;
use Exception;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;

/**
 * @property int verband
 */
class ContentLigenliste extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_ligenliste';

    /**
     * Generate the content element.
     *
     * @throws Exception
     */
    public function compile(): void
    {
        if ('' === $this->verband) {
            return;
        }
        $saisonIds = StringUtil::deserialize($this->saison);

        $saisonFilter = sprintf('saison IN (%s)', implode(',', $saisonIds));
        $ligen = LigaModel::findAll([
            'column' => ['pid=?', 'aktiv=?', $saisonFilter],
            'value' => [$this->verband, '1'],
            'order' => 'spielstaerke ASC',
        ]);

        if (null === $ligen) {
            return;
        }

        $listitems = [];

        foreach ($ligen as $liga) {
            $listitems[] = sprintf('%s %s',
                $liga->name,
                $liga->getRelated('saison')->name
            );
            $mannschaften = MannschaftModel::findByLiga($liga->id, ['order' => 'name ASC']);
            $temp = [];

            foreach ($mannschaften as $mannschaft) {
                if ($mannschaft->teampage) {
                    $teampage = PageModel::findById($mannschaft->teampage);
                    $temp[] = sprintf("<a href='%s'>%s</a>",
                        Controller::generateFrontendUrl($teampage->row()),
                        $mannschaft->name
                    );
                } else {
                    $temp[] = $mannschaft->name;
                }
            }
            $listitems[] = $temp;
        }

        $this->Template->listitems = $listitems;
    }
}
