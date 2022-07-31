<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2021 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Content element "Spielplan einer Liga".
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentElement;
use Contao\Controller;
use Contao\Date;
use Contao\PageModel;
use Contao\System;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Patchwork\Utf8;

class ContentSpielplan extends ContentElement
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_spielplan';

    /**
     * Generate the content element.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            return $this->generateBackendView();
        }

        return parent::generate();
    }

    /**
     * Generate the content element.
     *
     * @throws \Exception
     */
    public function compile(): void
    {
        if (!$this->liga) {
            return;
        }

        // Name für den Kalender aus der (ersten) Root-Page
        $rootPages = PageModel::findBy(
            ['type=?'],
            ['root'],
            [
                'order' => 'id ASC',
                'limit' => 1,
                'return' => 'Model',
            ]
        );
        $this->Template->calendarBaseName = $rootPages->title;

        $columns = ['pid=?'];
        $conditions = [$this->liga];

        $order = 'spiel_tag ASC, spiel_am ASC';

        if ($this->mannschaft) {
            $columns[] = '(home=? OR away=?)';
            $conditions[] = $this->mannschaft;
            $conditions[] = $this->mannschaft;
            // hier chronologisch, da es Spielverschiebungen geben kann
            $order = 'spiel_am ASC, spiel_tag ASC';
        }
        $begegnungen = BegegnungModel::findBy(
            $columns,
            $conditions,
            ['order' => $order]
        );

        if (null === $begegnungen) {
            return;
        }

        $spiele = [];

        foreach ($begegnungen as $begegnung) {
            // Nicht fertig eingegebene Spiele ausfiltern
            // (z.B. Liga ausgewählt, "submit on change" damit die Mannschaftsdropdowns
            // gefüllt werden dann Abbruch => eine Begegnung ist gespeichert bei der --
            // außer liga -- alle Felder leer sind :-/

            if (!$begegnung->home) {
                $liga = LigaModel::findById($begegnung->pid);
                $message = sprintf('Begegnung %d, %s ist nicht vollständig. Bitte bearbeiten oder löschen',
                    $begegnung->id,
                    $liga->name
                );
                System::log($message, __METHOD__, TL_ERROR);
                continue;
            }

            // Ergsbnis und daraus abgeleitet: hat die Begegnung bereits statt gefunden
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

            // "(geplant) Spielfrei" oder "Gegner nicht mehr aktiv":
            //
            // Reguläres Spielfrei oder Gegner nicht mehr aktiv und
            // Spiel noch nicht gespielt gewesen
            $spielfrei_home = !$away || (!$away->active && !$already_played);
            $spielfrei_away = !$home || (!$home->active && !$already_played);
            $spielfrei = $spielfrei_home || $spielfrei_away;

            // Nicht mehr aktive Heimmanschaft, die an diesem Spieltag
            // Spielfrei gehabt hätte (wäre dann Spielfrei gegen Spielfrei)
            if (!$home->active && !$away) {
                continue;
            }

            $spielort = $home->getRelated('spielort');

            // Ist die Heim- oder die Gastmannschaft nicht mehr aktiv?
            $inactive = !$home->active || !$away->active;

            $homelabel = !$home->active && !$already_played
                ? 'Spielfrei' : $home->getLinkedName();
            $awaylabel = !$away || (!$away->active && !$already_played)
                ? 'Spielfrei' : $away->getLinkedName();

            $spielortlabel = $spielort->name;

            if ($spielort->spielortpage) {
                $spielortpage = PageModel::findById($spielort->spielortpage);
                $spielortlabel = sprintf("<a href='%s'>%s</a>",
                    Controller::generateFrontendUrl($spielortpage->row()),
                    $spielort->name
                );
            }

            $spiel = [
                'home' => $homelabel,
                'away' => $awaylabel,
                // es interessiert nicht, wann und wo "Spielfei" stattfindet:
                'am' => $spielfrei ? '' : sprintf('%s. %s',
                    Date::parse('D', $begegnung->spiel_am),
                    Date::parse(Config::get('dateFormat'), $begegnung->spiel_am)
                ),
                'um' => $spielfrei ? '' : Date::parse(Config::get('timeFormat'), $begegnung->spiel_am),
                'im' => $spielfrei ? '' : $spielortlabel,
                'score' => $inactive && $already_played ? 'nicht gewertet' : $linked_score,
                'legs' => $inactive ? '' : ($already_played ? $begegnung->getLegs() : ''),
                'spiel_tag' => $begegnung->spiel_tag,
                // 'kommentar' => $begegnung->kommentar,
                'postponed' => $begegnung->postponed,
            ];

            if ($this->mannschaft) {
                $spiel['heimspiel'] = $home->id === $this->mannschaft;
            }

            $spiele[$begegnung->spiel_tag][] = $spiel;
        }

        $this->Template->mannschaft = $this->mannschaft;

        $this->Template->spiele = $spiele;

        $this->Template->ical_link = System::getContainer()
            ->get('router')
            ->generate('spielplan_ical', [
                'ligaid' => $this->liga,
                'mannschaftid' => $this->mannschaft,
            ])
        ;
    }

    /**
     * generate the view for the back end.
     *
     * @throws \Exception
     */
    protected function generateBackendView()
    {
        $objTemplate = new BackendTemplate('be_wildcard');

        $liga = LigaModel::findById($this->liga);
        $filter = '';

        if ($this->mannschaft) {
            $mannschaft = MannschaftModel::findById($this->mannschaft);
            $filter = ' (nur Begegnungen von "'.$mannschaft->name.'")';
        }
        $saison = SaisonModel::findById($liga->saison);
        if ($liga) {
            $ligalabel = sprintf('%s %s %s',
                $liga->getRelated('pid')->name,
                $liga->name,
                $saison->name
            );
        } else {
            $subject = sprintf('Liga mit der ID=%d (ex. nicht mehr', $this->liga);
        }
        $suffix = sprintf('%s %s', $ligalabel, $filter);
        $objTemplate->title = $this->headline;
        $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['CTE']['spielplan'][0])." $suffix ###";
        // $objTemplate->id = $this->id;
        // $objTemplate->link = 'the text that will be linked with href';
        // $objTemplate->href = 'contao/main.php?do=article&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

        return $objTemplate->parse();
    }
}
