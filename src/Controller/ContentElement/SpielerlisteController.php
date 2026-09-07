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
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Contao\FilesModel;
use Contao\StringUtil;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'spielerliste',
    category: 'ligaverwaltung',
    template: 'content_element/spielerliste'
)]
class SpielerlisteController extends AbstractContentElementController
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
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['spielerliste'][0])->upper().' ###';

        $mannschaft = MannschaftModel::findById($model->mannschaft);
        if (!$mannschaft) {
            $template->subject = sprintf('Mannschaft mit der ID %d %s', $model->mannschaft, DCAHelper::DOES_NOT_EXIST);
        } else {
            $template->subject = $mannschaft->getFullName();
        }
        if ($this->isBackend()) {
            return;
        }

        $allespieler = SpielerModel::findAll([
            'column' => ['pid=?', 'tl_spieler.active=?', 'tl_spieler.member_id>0'],
            'value' => [$model->mannschaft, '1'],
            //'order'  => 'teamcaptain DESC, co_teamcaptain DESC, lastname ASC, firstname ASC',
            'order' => 'teamcaptain DESC, co_teamcaptain DESC, firstname ASC, lastname ASC',
        ]);

        if (!$allespieler) {
            $allespieler = [];
        }

        $listitems = [];

        foreach ($allespieler as $spieler) {
            $member = $spieler->getRelated('member_id');
            $metaInformationOnPlayer = [];
            $positionOfPlayer = [];
            if ($spieler->teamcaptain) {
                $positionOfPlayer[] = $GLOBALS['TL_LANG']['MSC']['tc1'];
            }
            if ($spieler->co_teamcaptain) {
                $positionOfPlayer[] = $GLOBALS['TL_LANG']['MSC']['tc2'];
            }
            if ($model->showdetails) {
                if ($spieler->teamcaptain || $spieler->co_teamcaptain) {
                    if ($member->mobile) {
                        $metaInformationOnPlayer[] = sprintf('<a href="tel:%s">%s</a>',
                            // TODO: find a proper solution.
                            preg_replace('/^0/', '+49', str_replace('/','',$member->mobile)),
                            $member->mobile
                        );
                    }
                    if ($member->email) {
                        $metaInformationOnPlayer[] = sprintf("<a href='%s'>%s</a>",
                            StringUtil::encodeEmail('mailto:'.$member->email),
                            StringUtil::encodeEmail($member->email)
                        );
                    }
                }
            }

            $listitems[] = [
                'member' => $member,
                'spieler' => $spieler,
                'avatar' => FilesModel::findByUUid($member->avatar)?->path,
                'meta' => join(', ', $metaInformationOnPlayer),
                'position' => join(', ', $positionOfPlayer),
            ];
        }

        $template->mannschaft = $model->mannschaft;
        $template->listitems = $listitems;
        $template->showdetails = $model->showdetails;
    }

}
