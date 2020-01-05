<?php

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2018 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Auswahl einer Begegnung für die Bearbeitung im Frontend".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use Patchwork\Utf8;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * @property string $headline
 * @property integer $verband
 * @property string $saison
 */
class ContentBegegnungsauswahl extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_begegnungsauswahl';

    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['begegnungsauswahl'][0]) . " ###";

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the content element.
     */
    public function compile()
    {
        $listitems = [];

        $saisons = StringUtil::deserialize($this->saison);

        // Begegnungen der angegebenen Saison(s) im angegebenen Verband
        $strQuery = "SELECT
                         b.*,
                         mh.name as mh_name,
                         ma.name as ma_name,
                         l.name as l_name,
                         s.name as s_name
                     FROM
                         tl_begegnung b
                         LEFT JOIN tl_liga l on b.pid = l.id
                         LEFT JOIN tl_saison s on s.id = l.saison
                         LEFT JOIN tl_verband v on l.pid = v.id
                         LEFT JOIN tl_mannschaft mh on b.home = mh.id
                         LEFT JOIN tl_mannschaft ma on b.away = ma.id
                     WHERE
                         v.id=? AND
                         l.saison IN (" . join(',', $saisons) . ") AND
                         b.published=''
                     -- ORDER BY l_name ASC, b.spiel_tag ASC, mh_name ASC
                     ";
        $result = $this->Database::getInstance()->prepare($strQuery)->execute($this->verband);

        /** @var Router $routeGenerator */
        $routeGenerator = System::getContainer()->get('router')->getGenerator();

        while ($result->next()) {
            $listitems[] = [
                'id'        => $result->id,
                'label'     => sprintf("%d. Spieltag: %s vs. %s", $result->spiel_tag, $result->mh_name, $result->ma_name),
                'saison'    => $result->s_name,
                'liga'      => $result->l_name,
                'spiel_tag' => $result->spiel_tag,
                'home'      => $result->mh_name,
                'away'      => $result->ma_name,
                'edit_url'  => $routeGenerator->generate('begegnung_dataentry_form_fe', ['begegnung' => $result->id])
            ];
        }

        $this->Template->listitems = $listitems;
    }
}
