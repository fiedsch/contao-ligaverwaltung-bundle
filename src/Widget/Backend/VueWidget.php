<?php

namespace Fiedsch\LigaverwaltungBundle\Widget\Backend;

use Contao\System;
use Contao\Widget;
use Contao\StringUtil;
use Fiedsch\LigaverwaltungBundle\Controller\LigaverwaltungBackendController;
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Callback\BegegnungDataEntryForm;
use Twig\Environment;

class VueWidget extends Widget
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'backend/be_widget';

    public function generate(): string
    {
        $form = new BegegnungDataEntryForm(System::getContainer()->get('twig'));
        return $form->generate($this->activeRecord->id);
    }

    public function generateLabel()
    {
        $this->strLabel = '';
        return parent::generateLabel();
    }

    public function validator($varInput)
    {
        // Save the data to other fields (tl_begegnung.app_data and individual tl_spiel records)
        try {
            $this->saveData(json_decode(StringUtil::decodeEntities($varInput), true));
        } catch (\Exception) {
            // TODO $this->addError(...); nach Bedarf
            $this->addError('TEst-Error');
        }

        // The value is not supposed to be saved as there is no database field ($GLOBALS['TL_DCA']['tl_begegnung']['fields']['vue_app']['sql'] is set to null).
        // To achieve this, $GLOBALS['TL_DCA']['tl_begegnung']['fields']['vue_app']['eval']['doNotSaveEmpty'] is set to true, so returning an empty string prevents saving.
        return '';
    }

    protected function saveData(array $inputData): void
    {
        // unset data we don't need here
        unset($inputData['REQUEST_TOKEN']);
        unset($inputData['FORM_SUBMIT']);
        // dd($inputData);


        // Das folgende wird in
        /* @see DataEntrySaver::handleDataEntryData(...) */
        /* @see LigaverwaltungBackendController::begegnungDataSaveAction() */
        // "mit erledigt"

        $result = DataEntrySaver::handleDataEntryData($this->activeRecord->id /* == $inputData['begegnungId']*/, $inputData);
    }

}
