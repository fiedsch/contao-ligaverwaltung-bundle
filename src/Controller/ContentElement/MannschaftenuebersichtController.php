<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2026 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */


namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\MemberModel;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\StringUtil;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'mannschaftenuebersicht',
    category: 'ligaverwaltung',
    template: 'content_element/mannschaftenuebersicht'
)]
class MannschaftenuebersichtController extends AbstractContentElementController
{
    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {

        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftenuebersicht'][0])->upper().' ###';

        $saisonIds = StringUtil::deserialize($model->saison);

        $template->details = join(', ', $saisonIds);

        $saisonFilter = sprintf('saison IN (%s)', implode(',', $saisonIds));
        $ligen = LigaModel::findAll([
            'column' => [$saisonFilter, 'aktiv=?', $saisonFilter],
            'value' => ['1'],
            'order' => 'spielstaerke ASC',
        ]);

        if (!$ligen) {
            return;
        }

        $ligenInfo = [];
        $ligenDetails = [];

        foreach ($ligen as $liga) {
            $mannschaften = MannschaftModel::findBy(['liga=?', 'active=?'], [$liga->id, '1'], ['order' => 'name ASC']);
            if (null === $mannschaften) {
                continue;
            }
            $saison = SaisonModel::findById(LigaModel::findById($liga->id)?->saison)?->name;
            $ligenInfo[$liga->id] = $liga->name . ' ' . $saison;
            $ligenDetails[$liga->id] = [];

            foreach ($mannschaften as $mannschaft) {
                $tcs = [];
                $spieler = SpielerModel::findBy(
                    ['pid=?', '(teamcaptain=1 OR co_teamcaptain=1)'],
                    [$mannschaft->id],
                    ['order' => 'tl_spieler.teamcaptain DESC, tl_spieler.co_teamcaptain DESC']
                );

                if ($spieler) {
                    foreach ($spieler as $sp) {
                        $tcs[] = $sp->getTcDetails();
                    }
                }
                $spielort = $mannschaft->getRelated('spielort');
                $ligenDetails[$liga->id][] = [
                    'mannschaft' => $mannschaft->getLinkedName(),
                    'tc' => $tcs,
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

        $template->ligen = $ligenInfo;
        $template->ligendetails = $ligenDetails;
    }
}
