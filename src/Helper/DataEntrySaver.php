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

namespace Fiedsch\LigaverwaltungBundle\Helper;

use Contao\Database;
use Exception;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\HighlightModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielModel;
use RuntimeException;
use function count;
use function is_array;

class DataEntrySaver
{
    // app_data ist der "root key" der Daten, die n tl_begegnung.begegnung_data gespeichert werden!
    const KEY_APP_DATA = 'app_data';

    /**
     * Die Daten aus der Begegnunserfassung verarbeiten:
     * == tl_spiel und tl_begegnung Records anlegen bzw. aktualisieren.
     *
     * @param array $data die Daten, die die Vue-App "Begegnungserfassung" übermittelt hat
     */
    public static function handleDataEntryData(int $begegnung, array $data): string
    {
        $begegnungModel = BegegnungModel::findById($begegnung);

        if (!$begegnungModel) {
            return 'Begegnung nicht gefunden';
        }

        if ($begegnungModel->published) {
            return 'Begegnung ist bereits erfasst und veröffentlicht. Für Änderungen muss die Veröffentlichung vorübergehend zurückgesetzt werden.';
        }
        // nicht benötigte Daten entfernen
        unset($data['REQUEST_TOKEN'], $data['FORM_SUBMIT']);

        foreach ($data['highlights'] as $k => $v) {
            if ('' === $v) {
                unset($data['highlights'][$k]);
            }
        }

        $begegnungModel->{self::KEY_APP_DATA} = $data;
        $begegnungModel->save();

        $spieleGespeichert = 0;

        foreach ($data['spielplan'] as $i => $spiel) {
            $spieleGespeichert += self::handleSpiel($i, $spiel, $data);
        }

        $highlightsGespeichert = self::handleHighlights($begegnung, $data);

        return sprintf('%d Spiele und %s Highlights gespeichert', $spieleGespeichert, $highlightsGespeichert);
    }

    /**
     * @throws Exception
     */
    public static function augment(array $data): array
    {
        $begegnungId = (int) $data['begegnungId'];
        $begegnungModel = BegegnungModel::findById($begegnungId);

        if (!isset($data['spielplan'])) {
            $data['spielplan'] = Spielplan::getSpielplan($begegnungModel);
        }

        if (!isset($data['home'])) {
            $data['home'] = self::getTeamData($begegnungModel, 'home');
        }

        if (!isset($data['away'])) {
            $data['away'] = self::getTeamData($begegnungModel, 'away');
        }
        if (!is_array($data['highlights'] ?? null) || 0 === count($data['highlights'])) {
            //$data['highlights'] = ['dummy'=>'data']; // force Object ('{ }') because it would otherwise be an empty array ('[ ]')
            $data['highlights'] = json_decode('{}');
        }

        return $data;
    }

    /**
     * @return int Anzahl gespeicherte SpielModel
     */
    protected static function handleSpiel(int $i, array $spiel, array $data): int
    {
        $begegnungId = $data['begegnungId'];
        $slot = $i + 1;
        $isDouble = count($spiel['home']) > 1;
        $playerHomeId = $data['home']['lineup'][$spiel['home'][0]];
        $playerAwayId = $data['away']['lineup'][$spiel['away'][0]];

        $spielModel = SpielModel::findBy(['pid=?', 'slot=?'], [$begegnungId, $slot]);

        // unvollständige Aufstellung?
        if (null === $playerHomeId || null === $playerAwayId) {
            $spielModel?->delete();

            return 0;
        }

        $playerHome2Id = 0;
        $playerAway2Id = 0;

        if ($isDouble) {
            $playerHome2Id = $data['home']['lineup'][$spiel['home'][1]];
            $playerAway2Id = $data['away']['lineup'][$spiel['away'][1]];
        }

        $scoreHome = $spiel['scores']['home'];
        $scoreAway = $spiel['scores']['away'];

        // Unvollständige Ergebniserfassung?
        if (null === $scoreHome || null === $scoreAway) {
            $spielModel?->delete();

            return 0;
        }
        $spieltype = $isDouble ? SpielModel::TYPE_DOPPEL : SpielModel::TYPE_EINZEL;

        if (!$spielModel) {
            $spielModel = new SpielModel();
            $spielModel->pid = $begegnungId;
            $spielModel->slot = $slot;
        }
        $spielModel->spieltype = $spieltype;
        $spielModel->home = $playerHomeId;
        $spielModel->home2 = $playerHome2Id;
        $spielModel->away = $playerAwayId;
        $spielModel->away2 = $playerAway2Id;
        $spielModel->score_home = $scoreHome;
        $spielModel->score_away = $scoreAway;
        $spielModel->tstamp = time();

        try {
            $spielModel->save();
        } catch (RuntimeException $e) {
            return 0;
        }

        return 1;
    }

    /**
     * @return int Anzahl gespeicherte HighlightlModel
     */
    protected static function handleHighlights(int $begegnung, array $data): int
    {
        $existingHighlightsIds = Database::getInstance()
            ->prepare('SELECT id FROM tl_highlight WHERE begegnung_id=?')
            ->execute($begegnung)
            ->fetchEach('id')
        ;

        $savedModels = 0;

        foreach ($data['highlights'] as $k => $v) {
            if ('' === $v) {
                continue;
            }
            [$strType, $spieler] = explode('_', $k);

            switch ($strType) {
                case 'one80':
                    $highlightType = HighlightModel::TYPE_180;
                    break;

                case 'one71':
                    $highlightType = HighlightModel::TYPE_171;
                    break;

                case 'shortleg':
                    $highlightType = HighlightModel::TYPE_SHORTLEG;
                    break;

                case 'highfinish':
                    $highlightType = HighlightModel::TYPE_HIGHFINISH;
                default:
                    $highlightType = '';
            }

            $highlightModel = HighlightModel::findBy(
                ['begegnung_id=?', 'spieler_id=?', 'type=?'],
                [$begegnung, $spieler, $highlightType]
            );

            if (!$highlightModel) {
                $highlightModel = new HighlightModel();
                $highlightModel->begegnung_id = $begegnung;
                $highlightModel->spieler_id = $spieler;
                //$highlightModel->type = $highlightType;
            }
            $highlightModel->tstamp = time();
            $highlightModel->type = (int)$highlightType;
            $highlightModel->value = $v;
            $highlightModel->save();
            // Bookkeeping
            ++$savedModels;

            if (($key = array_search($highlightModel->id, $existingHighlightsIds, true)) !== false) {
                unset($existingHighlightsIds[$key]);
            }
        }

        if (count($existingHighlightsIds)) {
            $query = sprintf('DELETE FROM tl_highlight WHERE id IN (%s)', implode(',', $existingHighlightsIds));
            Database::getInstance()->execute($query);
        }

        return $savedModels;
    }

    /**
     * @throws Exception
     */
    protected static function getTeamData(BegegnungModel $begegnungModel, string $homeaway): array
    {
        $teamId = 'home' === $homeaway ? $begegnungModel->home : $begegnungModel->away;
        $mannschaftModel = MannschaftModel::findById($teamId);
        $spielerModel = SpielerModel::findBy(['pid=?'], [$teamId]);
        $players = [];

        if ($spielerModel) {
            /** @var SpielerModel $spieler */
            foreach ($spielerModel as $spieler) {
                $players[] = [
                    'name' => html_entity_decode($spieler->getName()),
                    'id' => $spieler->id,
                    'pass' => $spieler->getRelated('member_id')->passnummer,
                ];
            }
        }

        usort(
            $players,
            static function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
        );
        $players[] = [
            'name' => 'kein Spieler',
            'id' => 0,
            'pass' => 0,
        ];

        return [
            'key' => $homeaway,
            'name' => $mannschaftModel?->name ?? '',
            'available' => $players,
            'lineup' => [],
            'played' => [],
        ];
    }
}
