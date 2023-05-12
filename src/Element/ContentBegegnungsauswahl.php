<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2023 Andreas Fieger
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

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use Fiedsch\LigaverwaltungBundle\Trait\TlModeTrait;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use function Symfony\Component\String\u;

/**
 * @property string $headline
 * @property int    $verband
 * @property string $saison
 */
class ContentBegegnungsauswahl extends ContentElement
{

    use TlModeTrait;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_begegnungsauswahl';

    public function generate(): string
    {
        if ($this->isBackend()) {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->title = $this->headline;
            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['begegnungsauswahl'][0])->upper().' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the content element.
     */
    public function compile(): void
    {
        $listitems = [];

        $saisons = StringUtil::deserialize($this->saison);

        // Begegnungen der angegebenen Saison(s) im angegebenen Verband
        $strQuery = 'SELECT
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
                         l.saison IN ('.implode(',', $saisons).") AND
                         b.published=''
                     -- ORDER BY l_name ASC, b.spiel_tag ASC, mh_name ASC
                     ";
        $result = $this->Database::getInstance()->prepare($strQuery)->execute($this->verband);

        /** @var Router $router */
        $router = System::getContainer()->get('router');

        while ($result->next()) {
            $listitems[] = [
                'id' => $result->id,
                'label' => sprintf('%d. Spieltag: %s vs. %s', $result->spiel_tag, $result->mh_name, $result->ma_name),
                'saison' => $result->s_name,
                'liga' => $result->l_name,
                'spiel_tag' => $result->spiel_tag,
                'home' => $result->mh_name,
                'away' => $result->ma_name,
                'edit_url' => $router->generate('begegnung_dataentry_form_fe', ['begegnung' => $result->id]),
            ];
        }

        $this->Template->listitems = $listitems;
    }
}
