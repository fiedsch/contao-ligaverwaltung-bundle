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

namespace Fiedsch\Ligaverwaltung\Widget\Backend;

use Contao\System;
use Contao\Widget;
use Contao\StringUtil;
use Fiedsch\Ligaverwaltung\Helper\DataEntrySaver;
use Fiedsch\Ligaverwaltung\Callback\BegegnungDataEntryForm;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class BegegnungDataEntryWidget extends Widget
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = false;
    protected $strTemplate = 'backend/be_widget';

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function generate(): string
    {
        /** @var BegegnungDataEntryForm $form */
        $form = System::getContainer()->get(BegegnungDataEntryForm::class);
        return $form->generate($this->activeRecord->id);
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public function generateLabel()
    {
        $this->strLabel = '';
        return parent::generateLabel();
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public function validator($varInput)
    {
        // Save the data to other fields (tl_begegnung.app_data and individual tl_spiel records)
        $this->saveData(json_decode(StringUtil::decodeEntities($varInput), true));

        // The value is not supposed to be saved as there is no database field ($GLOBALS['TL_DCA']['tl_begegnung']['fields']['vue_app']['sql'] is set to null).
        // To achieve this, $GLOBALS['TL_DCA']['tl_begegnung']['fields']['vue_app']['eval']['doNotSaveEmpty'] is set to true, so returning an empty string prevents saving.
        return '';
    }


    protected function saveData(array $inputData): void
    {
        // Das folgende wird in
        /* @see DataEntrySaver::handleDataEntryData(...) */
        /* @see LigaverwaltungBackendController::begegnungDataSaveAction() */
        // "mit erledigt"

        try {
            DataEntrySaver::handleDataEntryData($this->activeRecord->id /* == $inputData['begegnungId']*/, $inputData);
        } catch (Exception $e) {
            $this->addError($e->getMessage());
        }
    }

}
