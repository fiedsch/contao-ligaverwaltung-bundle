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

namespace Fiedsch\Ligaverwaltung\Helper;

use Contao\Database;
use Contao\StringUtil;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Model\HighlightModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Model\SpielModel;
use Exception;
use function count;
use function is_array;

class DataEntrySaver
{
    // app_data ist der "root key" der Daten, die n tl_begegnung.begegnung_data gespeichert werden!
    const string KEY_APP_DATA = 'app_data';

    /**
     * Die Daten aus der Begegnungserfassung verarbeiten:
     * == tl_spiel und tl_begegnung Records anlegen bzw. aktualisieren.
     *
     * @param int $begegnung ID der Begegnung
     * @param array $data die Daten, die die Vue-App "Begegnungserfassung" übermittelt hat
     *
     * @throws Exception
     */
    public static function handleDataEntryData(int $begegnung, array $data): void
    {
        $begegnungModel = BegegnungModel::findById($begegnung);

        if (!$begegnungModel) {
            throw new Exception('Begegnung nicht gefunden');
        }

        if ($begegnungModel->published) {
            // throw new Exception('Begegnung ist bereits erfasst und veröffentlicht. Für Änderungen muss die Veröffentlichung vorübergehend zurückgesetzt werden.');
            // silently "don't handle" the begegnung and highlights data
            return;
            // Note: checking "published" with changes (and still unsaved) results or highlights data
            // will:
            // * save the data ('submitOnClick' => true in dca/tl_begegegnung.php)
            // * have a $begegnungModel->published === '' at this point here
            // * i.e. we will not return above!
            // * Can we rely on that order? The answer should be "yes" as we call saveData() which eventually calls handleDataEntryData()
            //   in the widgets validator() which means "before anything gets saved" (as other widgets might return "sorry, not valid").
        }

        foreach ($data['highlights'] as $k => $v) {
            if ('' === $v) {
                unset($data['highlights'][$k]);
            }
        }

        $begegnungModel->{self::KEY_APP_DATA} = $data;

        // Wurde die Begegnung (zumindest teilweise) erfasst?
        $begegnungModel->erfasst = !empty($data);
        $begegnungModel->save();

        foreach ($data['spielplan'] as $i => $spiel) {
            self::handleSpiel($i, $spiel, $data);
        }

        self::handleHighlights($begegnung, $data);
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
     * @throws Exception
     */
    protected static function handleSpiel(int $i, array $spiel, array $data): void
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

            return;
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

            return;
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

        $spielModel->save();
    }

    /**
     * @throws Exception
     */
    public static function handleHighlights(int $begegnung, array $data): void
    {
        $existingHighlightsIds = Database::getInstance() // TODO: use @database_connection from container (via DI?)
            ->prepare('SELECT id FROM tl_highlight WHERE begegnung_id=?')
            ->execute($begegnung)
            ->fetchEach('id')
        ;

        foreach ($data['highlights'] as $k => $v) {
            if ('' === $v) {
                continue;
            }
            [$strType, $spieler] = explode('_', $k);

            $highlightType = match ($strType) {
                'one80' => HighlightModel::TYPE_180,
                'one71' => HighlightModel::TYPE_171,
                'shortleg' => HighlightModel::TYPE_SHORTLEG,
                'highfinish' => HighlightModel::TYPE_HIGHFINISH,
                default => '',
            };

            $highlightModel = HighlightModel::findBy(
                ['begegnung_id=?', 'spieler_id=?', 'type=?'],
                [$begegnung, $spieler, $highlightType]
            );

            if (!$highlightModel) {
                $highlightModel = new HighlightModel();
                $highlightModel->begegnung_id = $begegnung;
                $highlightModel->spieler_id = $spieler;
            }
            $highlightModel->tstamp = time();
            $highlightModel->type = (int)$highlightType;
            $highlightModel->value = preg_replace('/[^\d,]/', '', $v); // entries are a comma separated list of numbers
            $highlightModel->save();

            if (($key = array_search($highlightModel->id, $existingHighlightsIds, true)) !== false) {
                unset($existingHighlightsIds[$key]);
            }
        }

        // After changes in the data entry and a call to save and the above removing of the really existing IDs from
        // $existingHighlightsIds, the array  contains the formerly existing IDs that now must be deleted
        if (count($existingHighlightsIds)) {
            $query = sprintf('DELETE FROM tl_highlight WHERE id IN (%s)', implode(',', $existingHighlightsIds));
            Database::getInstance()->execute($query);
        }
    }

    /**
     * @throws Exception
     */
    protected static function getTeamData(BegegnungModel $begegnungModel, string $homeaway): array
    {
        $teamId = 'home' === $homeaway ? $begegnungModel->home : $begegnungModel->away;
        $mannschaftModel = MannschaftModel::findById($teamId);
        $spielerModel = SpielerModel::findBy(['pid=?', 'tl_spieler.active=?'], [$teamId, 1]);
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

    public static function fixInputEncoding(array $appData): array
    {
        $appData['home']['name'] = StringUtil::decodeEntities($appData['home']['name']);
        $appData['away']['name'] = StringUtil::decodeEntities($appData['away']['name']);

        foreach ($appData['home']['available'] as &$player) {
            $player['name'] = StringUtil::decodeEntities($player['name']);
        }
        foreach ($appData['away']['available'] as &$player) {
            $player['name'] = StringUtil::decodeEntities($player['name']);
        }
        return $appData;
    }
}
