<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle;

use Contao\LigaModel;
use Contao\BegegnungModel;
use Contao\SaisonModel;
use Contao\SpielortModel;
use Contao\MannschaftModel;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonController
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
        date_default_timezone_set($tz);
    }


    /**
     *
     */
    public function run()
    {
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

        $responseData = [];

        // Events hinzufügen
        if ($begegnungen) {
            foreach ($begegnungen as $begegnung) {
                if (!$begegnung->spiel_am || !$begegnung->away) {
                    // Mannschaft hat Spielfrei
                    continue;
                }
                $responseData[] = $this->generateEventData($begegnung);
            }
        }

        return new JsonResponse($responseData);
    }

    /**
     * @param BegegnungModel $begegnung
     * @return array
     */
    protected function generateEventData(BegegnungModel $begegnung)
    {

        $liga = LigaModel::findById($begegnung->pid);
        $saison = SaisonModel::findById($liga->saison);

        $home = MannschaftModel::findById($begegnung->home);
        $away = MannschaftModel::findById($begegnung->away);
        $spielort = SpielortModel::findById($home->spielort);

        $title = sprintf("%s vs. %s",
            $home->name,
            $away->name
        );

        $address = sprintf("%s, %s %s",
                $spielort->street,
                $spielort->postal,
                $spielort->city
        );

        $result = [
            'title'          => $title,
            'location'       => ['name' =>$spielort->name,  'address' => $address ],
            'startDateTime'  => $begegnung->spiel_am,
            'datetime_local' => date("d.m.Y H:i", $begegnung->spiel_am), // visual debug
            'liga'           => $liga->name,
            'saison'         => $saison->name,
        ];

        if ($this->mannschaftid) {
            // Bei den Daten für "nur eine Mannschaft" zusätzlich
            // bestimmen, ob es ein Heim- oder ein Auswärtsspiel ist.
            $result['is_home_game'] = $home->id === $this->mannschaftid;
        }

        return $result;
    }

}
