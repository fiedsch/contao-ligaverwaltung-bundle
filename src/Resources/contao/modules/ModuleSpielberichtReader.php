<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Front end module "Spielbericht reader".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\Module;
use Contao\BackendTemplate;
use Contao\BegegnungModel;
use Contao\ContentModel;
use Contao\Input;
use Patchwork\Utf8;


class ModuleSpielberichtReader extends Module
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'mod_spielberichtreader';


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['spielberichtreader'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }


    /**
     * Generate the module
     *
     * @throws \Exception
     */
    protected function compile()
    {
        /** @var \PageModel $objPage */
        // global $objPage;

        // Falls wir einen Back-Link einbauen wollen:
        // $this->Template->referer = 'javascript:history.go(-1)';
        // $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];

        $id = Input::get('id');
        if (empty($id)) {
            $this->Template->begegnung = null;
            return;
        }
        $begegnung = BegegnungModel::findById(Input::get('id'));
        if (!$begegnung) {
            $this->Template->begegnung = null;
            return;
        }

        $this->Template->begegnung = $begegnung;

        $contentModel = new ContentModel();
        $contentModel->type = 'spielbericht';
        $contentModel->begegnung = $begegnung->id;
        $contentElement = new ContentSpielbericht($contentModel);
        $this->Template->spielbericht = $contentElement->generate();

    }
}
