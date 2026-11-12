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
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\MemberModel;
use Contao\StringUtil;
use Fiedsch\Ligaverwaltung\Helper\LigaVerwaltungHelper;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Helper\FrontendHelper;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'begegnungsliste',
    category: 'ligaverwaltung',
    template: 'content_element/begegnungsliste'
)]
class BegegnungslisteController extends AbstractContentElementController
{
    use TlModeTrait;

    public function __construct(
        private readonly FrontendHelper $frontendHelper,
        private readonly LigaVerwaltungHelper $ligaverwaltungHelper
    ) {
    }

    /**
     * @throws Exception
     */
    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {

        $this->setData($template, $model);

        return $template->getResponse();
    }

    /**
     * Daten für die Ausgabe ermitteln und im $template setzen
     *
     * @throws Exception
     */
    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['begegnungsliste'][0])->upper().' ###';

        if ($this->isBackend()) {
            return;
        }

        $frontendMember = $this->frontendHelper->getCurrentLoggedInMember();

        $saisons = StringUtil::deserialize($model->saison);

        $mannschaften = $this->ligaverwaltungHelper->getMannschaften($frontendMember, $saisons);
        $mannschaftenReduced = [];
        foreach ($mannschaften as $mannschaft) {
            $mannschaftenReduced[] = [
                'mannschaft' => $mannschaft->name,
                'mannschaft-id' => $mannschaft->id,
                'liga' => $mannschaft->getRelated('liga')->name,
                'liga-id' => $mannschaft->getRelated('liga')->id,
                'saison' => SaisonModel::findById($mannschaft->getRelated('liga')->saison)->name, // name oder alias?
                'saison-id' => SaisonModel::findById($mannschaft->getRelated('liga')->saison)->id
            ];
        }
        $template->mannschaften = $mannschaftenReduced;
        $mannschaftsIds = array_map(fn($el) => $el['mannschaft-id'], $mannschaftenReduced);

        // Lookup für Labels zu den IDs von Ligen
        $ligenmap = [];
        foreach ($mannschaften as $mannschaft) {
            $ligenmap[$mannschaft->getRelated('liga')->id] =
                sprintf('%s %s',
                    $mannschaft->getRelated('liga')->name,
                SaisonModel::findById($mannschaft->getRelated('liga')->saison)->name, // name oder alias?
                );
        }
        $template->ligenmap = $ligenmap;

        // Im Template auszugebende Items (Begegnungen)
        $begegnungen = BegegnungModel::findAll([
            'having' => sprintf('home IN (%s) OR away IN (%s)', implode(',', $mannschaftsIds), implode(',', $mannschaftsIds)),
            'orderBy' => 'spiel_am ASC',
         ]);
        $listitems = [];
        foreach ($begegnungen as $begegnung) {
            $listitems[$begegnung->pid][] =
                [
                    'id' => $begegnung->id,
                    'spiel_am' => $begegnung->spiel_am,
                    'label' => $begegnung->getLabel('short'), // oder 'medium', d.h. mit Liga/Saison
                    // 'erfasst' => $begegnung->erfasst, // TODO: verwenden wir dieses Feldüberhaupt? Siehe DCA, wo es nicht in der Palette ist
                    'published' => $begegnung->published,
                    'score' => $begegnung->getScore(),
                    'linkedscore' => $begegnung->getLinkedScore(),
                ];
        }
        $template->listitems = $listitems;

    }

}
