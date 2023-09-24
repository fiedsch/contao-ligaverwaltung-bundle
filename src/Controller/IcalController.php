<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2023 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Contao\Config;
use Contao\Controller;
use Contao\PageModel;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\DateTime as IcalDateTime;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;

class IcalController
{
    /**
     * @var int
     */
    protected int $ligaid;

    /**
     * @var int
     */
    protected int $mannschaftid;

    public function __construct(int $ligaid, int $mannschaftid)
    {
        $this->ligaid = $ligaid;
        $this->mannschaftid = $mannschaftid;
        $this->initialize();
        Controller::loadDataContainer('tl_begegnung'); // see generateIcalEvent()
    }

    /**
     * @throws Exception
     */
    public function run(): Response
    {
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

        $events = [];

        // Events hinzufügen
        if ($begegnungen) {
            foreach ($begegnungen as $begegnung) {
                if (!$begegnung->spiel_am || !$begegnung->away) {
                    // Mannschaft hat Spielfrei
                    continue;
                }
                $events[] = $this->generateIcalEvent($begegnung);
            }
        }

        // Kalender anlegen
        $vCalendar = new Calendar($events);

        $calendarName = sprintf('%s-%d-%d.ics',
            $calendarBaseName,
            $this->ligaid,
            $this->mannschaftid ?: 'alle'
        );

        $iCalendarComponent = (new CalendarFactory())->createCalendar($vCalendar);
        $response = new Response((string)$iCalendarComponent);
        $response->headers->add(['Content-Type' => 'text/calendar; charset=utf-8']);
        $response->headers->add(['Content-Disposition' => "attachment; filename=\"$calendarName\""]);

        return $response;
    }

    protected function initialize(): void
    {
        $tz = 'Europe/Berlin';
        date_default_timezone_set($tz);
    }

    /**
     * @throws Exception
     */
    protected function generateIcalEvent(BegegnungModel $begegnung): Event
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

        $dtStart = new DateTime(date('Y-m-d H:i:s', (int)$begegnung->spiel_am), new DateTimeZone(Config::get('timeZone')));

        // Did they change the default configuration (date + time) to date only in
        // the site's contfiguration? Then add a default time here:
        if ('datim' !== $GLOBALS['TL_DCA']['tl_begegnung']['fields']['spiel_am']['eval']['rgxp']) {
            // TODO: "Prime-Time" nicht hart kodiert
            // Z.B. in app/config/parameters.yml als ligaverwaltung.default_time
            // - - - - - - - -
            // # parameters.yml
            // parameters:
            //     ligaverwaltung.default_time: '20:00'
            // - - - - - - - -
            // und dann hier mittels System::getContainer()->getParameter('ligaverwaltung.default_time');
            // (das Ergebnis dann natürlich noch in Stunden, Minuten splitten).
            $dtStart->setTime(20, 0);
        }
        $vEvent
            ->setOccurrence(
                new TimeSpan(
                    new IcalDateTime($dtStart, true),
                    new IcalDateTime($dtStart->add(new DateInterval('PT2H')), true) // hard coded 2 hours duration
                )
            )
            ->setSummary(html_entity_decode($summary))
            ->setLocation(new Location(html_entity_decode($location)))
        ;

        return $vEvent;
    }
}
