<?php

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2018 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Contao\Controller;
use Contao\Config;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Symfony\Component\HttpFoundation\Response;

class IcalController
{
    /**
     * @var int
     */
    protected $ligaid;

    /**
     * @var int
     */
    protected $mannschaftid;

    /**
     * @param int $ligaid
     * @param int $mannschaftid
     */
    public function __construct($ligaid, $mannschaftid)
    {
        $this->ligaid = $ligaid;
        $this->mannschaftid = $mannschaftid;
        $this->initialize();
        Controller::loadDataContainer('tl_begegnung'); // see generateIcalEvent()
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        // Name für den Kalender aus der (ersten) Root-Page
        $rootPages = \Contao\PageModel::findBy(
            ['type=?'],
            ['root'],
            [
                'order' => 'id ASC',
                'limit' => 1,
                'return' => 'Model',
                ]
        );
        $calendarBaseName = $rootPages->title;

        // Spiele auslesen

        $columns = ['pid=?'];
        $conditions[] = $this->ligaid;

        if ($this->mannschaftid) {
            $columns[] = '(home=? OR away=?)';
            $conditions[] = $this->mannschaftid;
            $conditions[] = $this->mannschaftid;
        }

        $begegnungen = BegegnungModel::findBy(
            $columns,
            $conditions,
            ['order' => 'spiel_tag ASC, spiel_am ASC']
        );

        // Kalender anlegen
        $vCalendar = new Calendar($calendarBaseName);

        // Events hinzufügen
        if ($begegnungen) {
            foreach ($begegnungen as $begegnung) {
                if (!$begegnung->spiel_am || !$begegnung->away) {
                    // Mannschaft hat Spielfrei
                    continue;
                }
                $vCalendar->addComponent($this->generateIcalEvent($begegnung));
            }
        }

        $calendarName = sprintf('%s-%d-%d.ics',
            $calendarBaseName,
            $this->ligaid,
            $this->mannschaftid ?: 'alle'
        );

        $response = new Response($vCalendar->render());
        $response->headers->add(['Content-Type' => 'text/calendar; charset=utf-8']);
        $response->headers->add(['Content-Disposition' => "attachment; filename=\"$calendarName\""]);

        return $response;
    }

    protected function initialize()
    {
        $tz = 'Europe/Berlin';
        // $dtz = new \DateTimeZone($tz);
        date_default_timezone_set($tz);
    }

    /**
     * @param BegegnungModel $begegnung
     *
     * @throws \Exception
     *
     * @return Event
     */
    protected function generateIcalEvent(BegegnungModel $begegnung)
    {
        $vEvent = new Event();

        $liga = LigaModel::findById($begegnung->pid);

        $home = MannschaftModel::findById($begegnung->home);
        $away = MannschaftModel::findById($begegnung->away);
        $spielort = SpielortModel::findById($home->spielort);

        $summary = sprintf('%s: %s vs. %s (%s)',
            $liga->name,
            $home->name,
            $away->name,
            $spielort->name
        );

        $location = sprintf('%s, %s %s',
                $spielort->street,
                $spielort->postal,
                $spielort->city
        );

        $dtStart = new \DateTime(date('Y-m-d H:i:s', $begegnung->spiel_am), new \DateTimeZone(Config::get('timeZone')));

        // Did they change the default configuration (date + time) to date only in
        // the site's contfiguration? Then add a default time here:
        if ($GLOBALS['TL_DCA']['tl_begegnung']['fields']['spiel_am']['eval']['rgxp'] !== 'datim') {
            // TODO: "Prime-Time" nicht hart kodiert
            // In app/config/config.yml (oder parameters.yml?) als ligaverwaltung.default_time
            // - - - - - - - -
            // # config.yml
            // parameters:
            //     ligaverwaltung.default_time: '20:00'
            // - - - - - - - -
            // und dann hier mittels System::getContainer()->getParameter('ligaverwaltung.default_time');
            // (das Ergebnis dann natürlich noch in Stunden, Minuten splitten).
            $dtStart->setTime(20, 0);
        }
        $vEvent
            ->setDtStart($dtStart)
            ->setSummary($summary)
            ->setLocation($location);

        return $vEvent;
    }
}