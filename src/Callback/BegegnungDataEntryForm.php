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

namespace Fiedsch\Ligaverwaltung\Callback;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Input;
use Fiedsch\Ligaverwaltung\Helper\DataEntrySaver;
use Fiedsch\Ligaverwaltung\Helper\Spielplan;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Exception;

class BegegnungDataEntryForm
{

    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws Exception
     */
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
