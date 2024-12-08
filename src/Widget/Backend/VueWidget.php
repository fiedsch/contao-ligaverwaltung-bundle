<?php

namespace Fiedsch\LigaverwaltungBundle\Widget\Backend;

use Contao\Widget;
use Contao\StringUtil;
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Callback\BegegnungDataEntryForm;

class VueWidget extends Widget
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'backend/be_widget';

    public function generate(): string
    {
        $form = new BegegnungDataEntryForm();
        return $form->generate($this->activeRecord->id);
    }

    public function generateLabel()
    {
        $this->strLabel = '';
        return parent::generateLabel();
    }

    public function validator($varInput)
    {
        // TODO $this->addError(...); nach Bedarf
        // TODO Daten in einem save_callback umwandeln und in tl_begegnung.begegnung_data schreiben (+die einzelnen tl_spiel- und tl_highhlight-Records erzeugen/verwalten)
        // siehe Fiedsch\LigaverwaltungBundle\Callback\SaveModifiedDataCallback

        // Save the data to other fields (tl_begegnung.app_data and individual tl_spiel records)
        $this->saveData(json_decode(StringUtil::decodeEntities($varInput), true));

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


        // Das folgende wird in DataEntrySaver::handleDataEntryData(...) "mit erledigt"
        // $begegnung = BegegnungModel::findById($this->activeRecord->id);
        // $begegnung->setYamlColumnData(['app_data' => $inputData]);
        // $begegnung->save();

        // TODO: Die "alte" Logik von handleDataEntryData() -- insbes. der return value -- überarbeiten und dann hier $result geeignet weiterverarbeiten
        //       um im Validator() ggf. geeignet reagieren zu können (z.B. dort eine Exception zu werfen, die dann im Backend angezeigt wird.
        $result = DataEntrySaver::handleDataEntryData($this->activeRecord->id /* == $inputData['begegnungId']*/, $inputData);

    }

}
