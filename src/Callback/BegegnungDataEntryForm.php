<?php

namespace Fiedsch\Ligaverwaltung\Callback;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Input;
use Contao\System;
use Fiedsch\Ligaverwaltung\Helper\DataEntrySaver;
use Fiedsch\Ligaverwaltung\Helper\Spielplan;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class BegegnungDataEntryForm
{

    public function __construct(private Environment $twig)
    {
    }

    public function generate(?int $id): string
    {
        $id = $id ?? Input::get('id');
        $begegnungModel = BegegnungModel::findById($id);

        if (!$begegnungModel) {
            throw new RedirectResponseException('/contao?do=liga.begegnung');
            // ODER: 'contao/main.php?act=error' ?
        }

        $appData = $begegnungModel->{DataEntrySaver::KEY_APP_DATA};
        if (!is_array($appData)) {
            $appData = [];
        }
        $appData['begegnungId'] = $id;
        $appData['numSlots'] = 8;
        $appData['spielplanCss'] = Spielplan::getSpielplanCss($begegnungModel->getRelated('pid')->spielplan);
        $appData['disabled'] = $begegnungModel->published;
        $appData = DataEntrySaver::augment($appData);
        $appData = DataEntrySaver::fixInputEncoding($appData);

        $template = '@Contao_FiedschLigaverwaltungBundle/backend/begegnung_dataentry_vue.html.twig';
        return $this->twig->render($template, ['app_data' => $appData]);
    }
}
