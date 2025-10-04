<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2025 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Controller;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class JsonController
{
    public function __construct(private readonly ContaoFramework $framework)
    {
        $this->framework->initialize();
        $this->initialize();
        //Controller::loadDataContainer('tl_begegnung'); // see generateIcalEvent()
    }

    /**
     * @return JsonResponse
     */
    #[Route("/ligaverwaltung/spielplan/json/{ligaid}/{mannschaftid}", name: "spielplan_json", requirements: ["ligaid" => "\d+", "mannschaftid" => "\d+"], defaults: ["mannschaftid" => "0"])]
    public function __invoke(int $ligaid, int $mannschaftid): JsonResponse
    {
        // Spiele auslesen

        $columns = ['pid=?'];
        $conditions[] = $ligaid;

        if ($mannschaftid) {
            $columns[] = '(home=? OR away=?)';
            $conditions[] = $mannschaftid;
            $conditions[] = $mannschaftid;
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
                $responseData[] = $this->generateEventData($begegnung, $mannschaftid);
            }
        }

        return new JsonResponse($responseData);
    }

    protected function initialize(): void
    {
        $tz = 'Europe/Berlin';
        date_default_timezone_set($tz);
    }

    protected function generateEventData(BegegnungModel $begegnung, int $mannschaftid): array
    {
        $liga = LigaModel::findById($begegnung->pid);
        $saison = SaisonModel::findById($liga->saison);

        $home = MannschaftModel::findById($begegnung->home);
        $away = MannschaftModel::findById($begegnung->away);
        $spielort = SpielortModel::findById($home->spielort);

        $title = sprintf('%s vs. %s',
            $home->name,
            $away->name
        );

        $address = sprintf('%s, %s %s',
                $spielort->street,
                $spielort->postal,
                $spielort->city
        );

        $result = [
            'title' => $title,
            'location' => ['name' => $spielort->name, 'address' => $address],
            'startDateTime' => $begegnung->spiel_am,
            'datetime_local' => date('d.m.Y H:i', (int) $begegnung->spiel_am), // visual debug
            'liga' => $liga->name,
            'saison' => $saison->name,
        ];

        if ($mannschaftid) {
            // Bei den Daten für "nur eine Mannschaft" zusätzlich
            // bestimmen, ob es ein Heim- oder ein Auswärtsspiel ist.
            $result['is_home_game'] = $home->id === $mannschaftid;
        }

        return $result;
    }
}
