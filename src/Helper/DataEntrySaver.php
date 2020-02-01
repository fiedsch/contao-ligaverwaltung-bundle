<?php declare(strict_types=1);

namespace Fiedsch\LigaverwaltungBundle\Helper;

use Contao\BegegnungModel;
use Contao\Database;
use Contao\HighlightModel;
use Contao\LigaModel;
use Contao\MannschaftModel;
use Contao\SpielerModel;
use Contao\SpielModel;
use RuntimeException;

class DataEntrySaver
{
    // app_data ist der "root key" der Daten, die n tl_begegnung.begegnung_data gespeichert werden!
    const KEY_APP_DATA = 'app_data';

    /**
     * Die Daten aus der Begegnunserfassung verarbeiten:
     * == tl_spiel und tl_begegnung Records anlegen bzw. aktualisieren.
     *
     * @param int $begegnung
     * @param array $data die Daten, die die Vue-App "Begegnungserfassung" übermittelt hat.
     * @return string
     */
    public static function handleDataEntryData(int $begegnung, array $data): string
    {
        $begegnungModel = BegegnungModel::findById($begegnung);
        if (!$begegnungModel) {
            return 'Begegnung nicht gefunden';
        }
        // nicht benötigte Daten entfernen
        unset($data['REQUEST_TOKEN']);
        unset($data['FORM_SUBMIT']);
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

        return sprintf("%d Spiele und %s Highlights gespeichert", $spieleGespeichert, $highlightsGespeichert);
    }

    /**
     * @param int $i
     * @param array $spiel
     * @param array $data
     * @return int Anzahl gespeicherte SpielModel
     */
    protected static function handleSpiel($i, $spiel, $data): int
    {
        $begegnungId = $data['begegnungId'];
        $slot = $i + 1;
        $isDouble = count($spiel['home']) > 1;
        $playerHomeId = $data['home']['lineup'][$spiel['home'][0]];
        $playerAwayId = $data['away']['lineup'][$spiel['away'][0]];

        $spielModel = SpielModel::findBy(['pid=?', 'slot=?'], [$begegnungId, $slot]);

        // unvollständige Aufstellung?
        if (null === $playerHomeId || null === $playerAwayId) {
            if ($spielModel) {
                $spielModel->delete();
            }
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
            if ($spielModel) {
                $spielModel->delete();
            }
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
     * @param int $begegnung
     * @param array $data
     * @return int Anzahl gespeicherte HighlightlModel
     */
    protected static function handleHighlights(int $begegnung, $data) //: int
    {
        $existingHighlightsIds = Database::getInstance()
            ->prepare('SELECT id FROM tl_highlight WHERE begegnung_id=?')
            ->execute($begegnung)
            ->fetchEach('id');

        $savedModels = 0;

        foreach ($data['highlights'] as $k => $v) {
            if ('' === $v) {
                continue;
            }
            [$strType, $spieler] = explode('_', $k);

            switch ($strType) {
                case 'one80':
                    $intType = HighlightModel::TYPE_180;
                    break;
                case 'one71':
                    $intType = HighlightModel::TYPE_171;
                    break;
                case 'shortleg':
                    $intType = HighlightModel::TYPE_SHORTLEG;
                    break;
                case 'highfinish':
                    $intType = HighlightModel::TYPE_HIGHFINISH;
            }

            $highlightModel = HighlightModel::findBy(
                ['begegnung_id=?', 'spieler_id=?', 'type=?'],
                [$begegnung, $spieler, $intType]
            );
            if (!$highlightModel) {
                $highlightModel = new HighlightModel();
                $highlightModel->begegnung_id = $begegnung;
                $highlightModel->spieler_id = $spieler;
                $highlightModel->type = $intType;
            }
            $highlightModel->tstamp = time();
            $highlightModel->type = $intType;
            $highlightModel->value = $v;
            $highlightModel->save();
            // Bookkeeping
            ++$savedModels;
            if (($key = array_search($highlightModel->id, $existingHighlightsIds)) !== false) {
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
     * @param array $data
     * @return array
     */
    public static function augment(array $data): array
    {
        $begegnungId = (int)$data['begegnungId'];
        $begegnungModel = BegegnungModel::findById($begegnungId);

        if (!isset($data['spielplan'])) {
            $data['spielplan'] = Spielplan::getSpielplan($begegnungModel);
        }
        if (!isset($data['home'])) {
            $data['home'] = self::getTeamData($begegnungModel, "home");
        }
        if (!isset($data['away'])) {
            $data['away'] = self::getTeamData($begegnungModel, "away");
        }

        if (!is_array($data['highlights']) || count($data['highlights'])===0) {
            //$data['highlights'] = ['dummy'=>'data']; // force Object ('{ }') because it would otherwise be an empty array ('[ ]')
            $data['highlights'] = json_decode('{}');
        }

        return $data;
    }


    protected static function getTeamData(BegegnungModel $begegnungModel, string $homeaway): array
    {
        $teamId = "home" === $homeaway ? $begegnungModel->home : $begegnungModel->away;
        $mannschaftModel = MannschaftModel::findById($teamId);
        $spielerModel = SpielerModel::findBy(['pid=?'], [$teamId]);
        $players = [];
        /** @var SpielerModel $spieler */
        foreach ($spielerModel as $spieler) {
            $players[] = [
                "name" => $spieler->getName(),
                "id"   => $spieler->id,
                "pass" => $spieler->getRelated('member_id')->passnummer,
            ];
        }

        usort($players, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        $players[] = [
            "name" => 'kein Spieler',
            "id"   => 0,
            "pass" => 0,
        ];

        return [
            "key"       => $homeaway,
            "name"      => $mannschaftModel->name,
            "available" => $players,
            "lineup"    => [ ],
            "played"    => [ ],
        ];
    }

}