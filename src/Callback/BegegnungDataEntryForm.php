<?php

namespace Fiedsch\LigaverwaltungBundle\Callback;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Input;
use Contao\System;
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Helper\Spielplan;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

class BegegnungDataEntryForm
{

    private Environment $twig;
    public function __construct()
    {
        $container = System::getContainer();
        $this->twig = $container->get('twig');
    }

    public function generate(): string
    {
        $id = Input::get('id');
        $begegnungModel = BegegnungModel::findById($id);

        if (!$begegnungModel) {
            throw new RedirectResponseException('/contao?do=liga.begegnung');
            // ODER: 'contao/main.php?act=error' ?
        }

        $appData = $begegnungModel->{DataEntrySaver::KEY_APP_DATA};
        if (!is_array($appData)) {
            $appData = [];
        }
        $appData['webserviceUrl'] = '/ligaverwaltung/begegnung';
        $appData['requestToken'] = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        $appData['begegnungId'] = $id;
        $appData['numSlots'] = 8;
        $appData['spielplanCss'] = Spielplan::getSpielplanCss($begegnungModel->getRelated('pid')->spielplan);
        $appData = DataEntrySaver::augment($appData);
        // dd($appData);

        $template = '@FiedschLigaverwaltung/begegnung_dataentry_vue.html.twig';
        return $this->twig->render($template, ['app_data' => $appData]);
    }
}
