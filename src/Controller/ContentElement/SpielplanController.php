<?php

declare(strict_types=1);

namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Date;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;


#[AsContentElement(
    type: 'spielplan',
    category: 'ligaverwaltung',
    template: 'content_element/spielplan'
)]
class SpielplanController extends AbstractContentElementController
{

    use TlModeTrait;

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->setData($template, $model);

        return $template->getResponse();
    }

    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### ' . u($GLOBALS['TL_LANG']['CTE']['spielplan'][0])->upper() . ' ###';

        $liga = LigaModel::findById($model->liga);
        $template->liga = $liga;

        if (!$liga) {
            $template->details = sprintf('Liga mit der ID %d %s', $model->liga, DCAHelper::DOES_NOT_EXIST);
        } else {
            $saison = $liga->getRelated('saison');
            $template->details = sprintf('%s %s', $liga->name, $saison->name);
        }
        if ($this->isBackend()) {
            return;
        }

        if (!$liga) {
            return;
        }
        $template->ligaId = $model->liga;
        $template->mannschaftId = $model->mannschaft;

        // Name für den Kalender (iCal Download) aus der (ersten) Root-Page
        $rootPages = PageModel::findBy(
            ['type=?'],
            ['root'],
            [
                'order' => 'id ASC',
                'limit' => 1,
                'return' => 'Model',
            ]
        );
        $template->calendarBaseName = $rootPages->title;

        $columns = ['pid=?'];
        $conditions = [$model->liga];

        $order = 'spiel_tag ASC, spiel_am ASC';

        if ($model->mannschaft) {
            $columns[] = '(home=? OR away=?)';
            $conditions[] = $model->mannschaft;
            $conditions[] = $model->mannschaft;
            // hier chronologisch, da es Spielverschiebungen geben kann
            // TODO: ist allerdings problematisch, wenn noch kein neues Spieldatum eingegeben wurde (das Feld also leer ist :-o(
            // TODO: ist allerdings problematisch, wenn noch kein neues Spieldatum eingegeben wurde (das Feld also leer ist :-o(
            $order = 'spiel_am ASC, spiel_tag ASC';
        }
        $begegnungen = BegegnungModel::findBy(
            $columns,
            $conditions,
            ['order' => $order]
        );

        if (null === $begegnungen) {
            $template->spiele = [];
            return;
        }

        $spiele = [];

        foreach ($begegnungen as $begegnung) {

            if (!$begegnung->home) {
                $liga = LigaModel::findById($begegnung->pid);
                $message = sprintf('Begegnung %d, %s ist nicht vollständig. Bitte bearbeiten oder löschen',
                    $begegnung->id,
                    $liga->name
                );
//                System::log($message, __METHOD__, TL_ERROR); // TODO inject and use contao logger
                continue;
            }

            // Ergebnis und daraus abgeleitet: hat die Begegnung bereits statt gefunden
            $linked_score = $begegnung->getLinkedScore();
            $already_played = '' !== $linked_score;

            /** @var MannschaftModel $home */
            $home = $begegnung->getRelated('home');
            /** @var MannschaftModel $away */
            $away = $begegnung->getRelated('away');

            // Voreilig gelöschte Heimmannschaft (hätte auf inaktiv gesetzt werden sollen)
            if ($begegnung->home && !$home) {
                continue;
            }
            // Dito mit der Gastmannschaft
            if ($begegnung->away && !$away) {
                continue;
            }

            // "(geplant) spielfrei" oder "Gegner nicht mehr aktiv":
            //
            // Reguläres spielfrei oder Gegner nicht mehr aktiv und
            // Spiel noch nicht gespielt gewesen
            $spielfrei_home = !$away || (!$away->active && !$already_played);
            $spielfrei_away = !$home || (!$home->active && !$already_played);
            $spielfrei = $spielfrei_home || $spielfrei_away;

            // Nicht mehr aktive Heimmanschaft, die an diesem Spieltag
            // spielfrei gehabt hätte (wäre dann spielfrei gegen Spielfrei)
            if (!$home->active && !$away) {
                continue;
            }

            $spielort = $home->getRelated('spielort');

            // Ist die Heim- oder die Gastmannschaft nicht mehr aktiv?
            $inactive = (!$home?->active) || (!$away?->active);

            $homelabel = !$home?->active && !$already_played
                ? 'Spielfrei' : $home?->getLinkedName();
            $awaylabel = !$away?->active && !$already_played
                ? 'Spielfrei' : $away?->getLinkedName();

            $spielortlabel = $spielort->name;

            if ($spielort->spielortpage) {
                $spielortpage = PageModel::findById($spielort->spielortpage);
                $spielortlabel = sprintf("<a href='%s'>%s</a>",
                    //Controller::generateFrontendUrl($spielortpage->row()),
                    $spielortpage->getFrontendUrl(),
                    $spielort->name
                );
            }

            $legs = $inactive ? '' : ($already_played ? $begegnung->getLegs() : '');
            $spielLegsClass = empty($legs) ? 'empty' : (preg_match("/\d+:\d+/", $legs) ? '' : 'noshow');

            $spiel = [
                'home' => $homelabel,
                'away' => $awaylabel,
                // es interessiert nicht, wann und wo "Spielfei" stattfindet:
                'am' => $spielfrei||$begegnung->postponed ? '' : sprintf('%s. %s',
                    Date::parse('D', $begegnung->spiel_am),
                    Date::parse(Config::get('dateFormat'), $begegnung->spiel_am)
                ),
                'um' => $spielfrei ? '' : Date::parse(Config::get('timeFormat'), $begegnung->spiel_am),
                'im' => $spielfrei ? '' : $spielortlabel,
                'score' => $inactive && $already_played ? 'nicht gewertet' : $linked_score,
                'legs' => $legs,
                'spiel_tag' => $begegnung->spiel_tag,
                // 'kommentar' => $begegnung->kommentar,
                'postponed' => $begegnung->postponed,
                'spielLegsClass' => $spielLegsClass,
            ];

            if ($model->mannschaft) {
                $spiel['heimspiel'] = $home->id === $model->mannschaft;
            }

            $spiele[$begegnung->spiel_tag][] = $spiel;
        }

        $template->spiele = $spiele;

        $template->ical_link = System::getContainer()
            ->get('router') // TODO: inject and use
            ->generate('spielplan_ical', [
                'ligaid' => $model->liga,
                'mannschaftid' => $model->mannschaft,
            ]);

        $headlineUnit = StringUtil::deserialize($model->headline)['unit'];
        $subheadlineUnit = preg_match('/h(\d)/', $headlineUnit, $match) ? $match[1]+1 : 3;
        $template->subheadlineUnit = $subheadlineUnit;
    }
}
