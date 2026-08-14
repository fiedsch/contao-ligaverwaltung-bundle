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
use Contao\StringUtil;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'mannschaftenuebersicht',
    category: 'ligaverwaltung',
    template: 'content_element/mannschaftenuebersicht'
)]
class MannschaftenuebersichtController extends AbstractContentElementController
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
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['mannschaftenuebersicht'][0])->upper().' ###';

        $saisonIds = StringUtil::deserialize($model->saison);

        if ($this->isBackend()) {
            $template->subject = sprintf('IDs: %s',
                join(',',$saisonIds)
            );
            return;
        }

        $template->details = join(', ', $saisonIds);

        $alleLigen = [];
        foreach ($saisonIds as $saisonId) {
            $ligen = LigaModel::findAll([
                'column' => ['aktiv=?', 'saison=?'],
                'value' => [1, $saisonId],
                'order' => 'spielstaerke ASC, name ASC', // name ASC as fallback if spielstaerke (which is kind of an order field) is left emtpy
            ]);
            if (!$ligen) { continue; }
            array_push($alleLigen, ...$ligen->fetchAll());
        }
        if (!$alleLigen) {
            return;
        }

        $ligenInfo = [];
        $ligenDetails = [];

        foreach ($alleLigen as $liga) {
            $mannschaften = MannschaftModel::findBy(['liga=?', 'active=1'], [$liga['id']], ['order' => 'name ASC']);
            if (null === $mannschaften) {
                continue;
            }
            $saison = SaisonModel::findById(LigaModel::findById($liga['id'])?->saison)?->name;
            $ligenInfo[$liga['id']] = $liga['name'] . ' ' . $saison;
            $ligenDetails[$liga['id']] = [];

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
                $ligenDetails[$liga['id']][] = [
                    'mannschaft' => $mannschaft->getShortName(),
                    'mannschaftlink' => $mannschaft->getTeamPageLink(),
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
