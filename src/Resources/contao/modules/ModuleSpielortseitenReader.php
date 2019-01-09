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
 * Front end module "Spielortseiten reader".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\Input;
use Contao\Module;
use Contao\SpielortModel;
use Patchwork\Utf8;

class ModuleSpielortseitenReader extends Module
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_spielortseitenreader';

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            /** @var BackendTemplate $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['spielortseitenreader'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile()
    {
        /** @var \Contao\PageModel $objPage */
        // global $objPage;

        // Falls wir einen Back-Link einbauen wollen:
        // $this->Template->referer = 'javascript:history.go(-1)';
        // $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        $id = Input::get('id');
        // spielort.html?id=123 (id Parameter) vs.
        // spielort/123.html (auto_item)
        if (!$id) {
            $id = Input::get('auto_item');
        }
        if (empty($id)) {
            $this->Template->spielort = null;

            return;
        }
        $spielort = SpielortModel::findById($id);
        if (!$spielort) {
            $this->Template->spielort = null;

            return;
        }

        $this->Template->spielort = $spielort;

        $contentModel = new ContentModel();
        $contentModel->type = 'spielortseite';
        $contentModel->spielort = $spielort->id;
        $contentModel->ligen = $this->ligen;
        $contentElement = new ContentSpielortseite($contentModel);
        $this->Template->spielortseite = $contentElement->generate();
    }
}
