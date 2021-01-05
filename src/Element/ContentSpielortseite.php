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
 * Content Element "Mannschaftsseite".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\ContentModel;
use Contao\StringUtil;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;
use Patchwork\Utf8;
use function in_array;
use function array_filter;

class ContentSpielortseite extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielortseite';

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $headline = $this->headline;
            if (!$headline) {
                $spielortModel = SpielortModel::findById($this->spielort);
                $headline = $spielortModel->name;
            }

            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['spielortseite'][0]).' ###';
            $objTemplate->id = $this->id;
            $objTemplate->link = $headline;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    public function compile()
    {
        $spielortModel = SpielortModel::findById($this->spielort);

        $this->addDescriptionToTlHead('Alles zum Spielort '.$spielortModel->name);

        // Spielortinfo
        $contentModel = new ContentModel();
        $contentModel->type = 'spielortinfo';
        $contentModel->spielort = $spielortModel->id;
        $contentModel->headline = [
            // Keine Überschrift!
            // 'value' => 'Spielor ', . $spielortModel->name,
            // 'unit'  => 'h1',
        ];
        $contentElement = new ContentSpielortinfo($contentModel);
        $this->Template->spielortinfo = $contentElement->generate();

        $this->Template->spielort_name = $spielortModel->name;

        $mannschaften = MannschaftModel::findBy(['spielort=?'], [$spielortModel->id]);

        // Alle Mannschaften ermitteln, die "hier spielen"
        // nach Ligen gemäß Auswahl in der CE-Konfiguration filtern
        // Nach Ligen gruppiert Mannschaften (verlinkt)  ausgeben

        $mannschaften_in_ligen_liste = [];
        $gefundene_ligen = [];
        $ligen_lookup = [];

        if ($mannschaften) {
            foreach ($mannschaften as $mannschaft) {
                if (in_array($mannschaft->liga, StringUtil::deserialize($this->ligen), true)) {
                    $mannschaften_in_ligen_liste[$mannschaft->liga][] = $mannschaft->getLinkedName();
                    ++$gefundene_ligen[$mannschaft->liga];
                }
            }
            $gefundene_ligen = array_keys($gefundene_ligen);
        }

        // nach in der Konfiguration ausgewählten Saisons filtern

        $show_ligen = array_filter(StringUtil::deserialize($this->ligen, true), function ($el) use ($gefundene_ligen) {
            return in_array($el, $gefundene_ligen, true);
        });

        foreach ($show_ligen as $ligaId) {
            $ligen_lookup[$ligaId] = LigaModel::findById($ligaId);
        }

        // $this->Template->mannschaften_liste = $mannschaften_liste; // nur debug
        // $this->Template->gefundene_ligen = $gefundene_ligen;  // nur debug
        // $this->Template->ligen_config = StringUtil::deserialize($this->ligen, true);  // nur debug
        $this->Template->show_ligen = $show_ligen;
        $this->Template->ligen_lookup = $ligen_lookup;
        $this->Template->mannschaften_in_ligen_liste = $mannschaften_in_ligen_liste;
    }

    /**
     * Add the following to fe_page.html5 or (if using Bootsrap for Contao) to fe_bootstrap_xx.html5:
     * ```
     * <?php if (!strpos($head, "description") === false): ?>
     * <meta name="description" content="<?php echo $this->description; ?>">
     * <?php endif; ?>
     * ```.
     *
     * @param string $content
     */
    protected function addDescriptionToTlHead(string $content)
    {
        if ($GLOBALS['TL_HEAD']) {
            foreach ($GLOBALS['TL_HEAD'] as $i => $entry) {
                if (preg_match('/description/', $entry)) {
                    unset($GLOBALS['TL_HEAD'][$i]);
                }
            }
        }
        $GLOBALS['TL_HEAD'][] = sprintf('<meta name="description" content="%s">', $content);
    }
}
