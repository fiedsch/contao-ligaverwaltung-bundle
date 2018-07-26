<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\LigaModel;
use Contao\BegegnungModel;
use Contao\SpielortModel;
use Contao\MannschaftModel;
use Symfony\Component\HttpFoundation\Response;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

class IcalController
{
    /**
     * @var integer
     */
    protected $ligaid;

    /**
     * @var integer
     */
    protected $mannschaftid;

    /**
     * @param integer $ligaid
     * @param integer $mannschaftid
     */
    public function __construct($ligaid, $mannschaftid)
    {
        $this->ligaid = $ligaid;
        $this->mannschaftid = $mannschaftid;
        $this->initialize();
    }

    /**
     *
     */
    protected function initialize()
    {
        $tz = 'Europe/Berlin';
        // $dtz = new \DateTimeZone($tz);
        date_default_timezone_set($tz);
    }


    /**
     *
     */
    public function run()
    {
        // Name für den Kalender aus der (ersten) Root-Page
        $rootPages = \Contao\PageModel::findBy(
            ['type=?'],
            ['root'],
            [
                'order'  => 'id ASC',
                'limit'  => 1,
                'return' => 'Model'
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

        $calendarName = sprintf("%s-%d-%d.ics",
            $calendarBaseName,
            $this->ligaid,
            $this->mannschaftid ?: 'alle'
        );

        $response = new Response($vCalendar->render());
        $response->headers->add(['Content-Type', 'text/calendar; charset=utf-8']);
        $response->headers->add(['Content-Disposition', "attachment; filename=\"$calendarName\""]);
        return $response;
    }

    /**
     * @param BegegnungModel $begegnung
     * @return Event
     */
    protected function generateIcalEvent(BegegnungModel $begegnung)
    {
        $vEvent = new Event();

        $liga = LigaModel::findById($begegnung->pid);

        $home = MannschaftModel::findById($begegnung->home);
        $away = MannschaftModel::findById($begegnung->away);
        $spielort = SpielortModel::findById($home->spielort);

        $summary = sprintf("%s: %s vs. %s (%s)",
            $liga->name,
            $home->name,
            $away->name,
            $spielort->name
        );

        $location = sprintf("%s, %s %s",
                $spielort->street,
                $spielort->postal,
                $spielort->city
        );

        $vEvent
            ->setDtStart(new \DateTime(date("Y-m-d H:i:s", $begegnung->spiel_am)))
            ->setSummary($summary)
            ->setLocation($location);

        return $vEvent;
    }

}