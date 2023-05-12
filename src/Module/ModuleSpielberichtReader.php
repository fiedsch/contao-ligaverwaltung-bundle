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
 * Front end module "Spielbericht reader".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Module;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\Input;
use Contao\Module;
use Contao\PageModel;
use Fiedsch\LigaverwaltungBundle\Element\ContentSpielbericht;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Exception;
use Fiedsch\LigaverwaltungBundle\Trait\TlModeTrait;
use function Symfony\Component\String\u;

class ModuleSpielberichtReader extends Module
{
    use TlModeTrait;
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_spielberichtreader';

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate(): string
    {
        if ($this->isBackend()) {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['FMD']['spielberichtreader'][0])->upper().' ###';
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
     *
     * @throws Exception
     */
    protected function compile(): void
    {
        /** @var PageModel $objPage */
        // global $objPage;

        // Falls wir einen Back-Link einbauen wollen:
        // $this->Template->referer = 'javascript:history.go(-1)';
        // $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        $id = Input::get('id');
        // spielbericht.html?id=123 (id Parameter) vs.
        // spielbericht/123.html (auto_item)
        if (!$id) {
            $id = Input::get('auto_item');
        }

        if (empty($id)) {
            $this->Template->begegnung = null;

            return;
        }
        $begegnung = BegegnungModel::findById($id);

        if (!$begegnung) {
            $this->Template->begegnung = null;

            return;
        }

        $this->Template->begegnung = $begegnung;

        $contentModel = new ContentModel();
        $contentModel->tstamp = time();
        $contentModel->type = 'spielbericht';
        $contentModel->begegnung = $begegnung->id;
        $contentElement = new ContentSpielbericht($contentModel);
        $this->Template->spielbericht = $contentElement->generate();
    }
}
