<?php declare(strict_types=1);

namespace Fiedsch\LigaverwaltungBundle\Helper;

use Contao\BegegnungModel;
use Contao\LigaModel;


class Spielplan
{
    /**
     * @param BegegnungModel $begegnungModel
     * @return array
     * @throws \Exception
     */
    public static function getSpielplan(BegegnungModel $begegnungModel): array
    {
        /** @var LigaModel $liga */
        $liga = $begegnungModel->getRelated('pid');

        switch ($liga->spielplan) {
            case LigaModel::SPIELPLAN_16E:
                return [
                    ["home" => [0], "away" => [0]],
                    ["home" => [1], "away" => [1]],
                    ["home" => [2], "away" => [2]],
                    ["home" => [3], "away" => [3]],
                    ["home" => [0], "away" => [1]],
                    ["home" => [1], "away" => [0]],
                    ["home" => [2], "away" => [3]],
                    ["home" => [3], "away" => [2]],
                    ["home" => [2], "away" => [0]],
                    ["home" => [3], "away" => [1]],
                    ["home" => [1], "away" => [2]],
                    ["home" => [0], "away" => [3]],
                    ["home" => [3], "away" => [0]],
                    ["home" => [2], "away" => [1]],
                    ["home" => [0], "away" => [2]],
                    ["home" => [1], "away" => [3]],
                ];
                break;
            case LigaModel::SPIELPLAN_16E2D:
                return [
                    ["home" => [0], "away" => [0]],
                    ["home" => [1], "away" => [1]],
                    ["home" => [2], "away" => [2]],
                    ["home" => [3], "away" => [3]],
                    ["home" => [0], "away" => [1]],
                    ["home" => [1], "away" => [0]],
                    ["home" => [2], "away" => [3]],
                    ["home" => [3], "away" => [2]],
                    ["home" => [2], "away" => [0]],
                    ["home" => [3], "away" => [1]],
                    ["home" => [1], "away" => [2]],
                    ["home" => [0], "away" => [3]],
                    ["home" => [3], "away" => [0]],
                    ["home" => [2], "away" => [1]],
                    ["home" => [0], "away" => [2]],
                    ["home" => [1], "away" => [3]],
                    ["home" => [0, 2], "away" => [1, 3]],
                    ["home" => [1, 3], "away" => [0, 2]],
                ];
                break;
            case LigaModel::SPIELPLAN_16E4D:
                return [
                    ["home" => [0], "away" => [0] ],
                    ["home" => [1], "away" => [1] ],
                    ["home" => [2], "away" => [2] ],
                    ["home" => [3], "away" => [3] ],
                    ["home" => [0], "away" => [1] ],
                    ["home" => [1], "away" => [0] ],
                    ["home" => [2], "away" => [3] ],
                    ["home" => [3], "away" => [2] ],
                    ["home" => [2], "away" => [0] ],
                    ["home" => [3], "away" => [1] ],
                    ["home" => [1], "away" => [2] ],
                    ["home" => [0], "away" => [3] ],
                    ["home" => [3], "away" => [0] ],
                    ["home" => [2], "away" => [1] ],
                    ["home" => [0], "away" => [2] ],
                    ["home" => [1], "away" => [3] ],
                    ["home" => [0, 2], "away" => [1, 3] ],
                    ["home" => [1, 3], "away" => [0, 2] ],
                    ["home" => [0, 2], "away" => [0, 2] ],
                    ["home" => [1, 3], "away" => [1, 3] ],
                ];
                break;
            case LigaModel::SPIELPLAN_6E3D:
                return [
                    [ "home" => [0], "away" => [0] ],
                    [ "home" => [1], "away" => [1] ],
                    [ "home" => [2], "away" => [2] ],
                    [ "home" => [3], "away" => [3] ],
                    [ "home" => [4], "away" => [4] ],
                    [ "home" => [5], "away" => [5] ],
                    [ "home" => [0,1], "away"=> [0,1] ],
                    [ "home" => [2,3], "away"=> [2,3] ],
                    [ "home" => [4,5], "away"=> [4,5] ],
                ];
                break;
            case LigaModel::SPIELPLAN_8E2D:
                return [
                    [ "home" => [0], "away" => [0] ],
                    [ "home" => [1], "away" => [1] ],
                    [ "home" => [2], "away" => [2] ],
                    [ "home" => [3], "away" => [3] ],
                    [ "home" => [0], "away" => [3] ],
                    [ "home" => [1], "away" => [2] ],
                    [ "home" => [2], "away" => [1] ],
                    [ "home" => [3], "away" => [0] ],
                    [ "home" => [0,2], "away" => [0,2] ],
                    [ "home" => [1,3], "away" => [1,3] ],
                ];
                break;
            default:
                return [
                ];
        }
    }

    /**
     * @param int $spielplanCode
     * @return string
     */
    public static function getSpielplanCss(int $spielplanCode): string
    {
        $allCss = 'input.spieler-score { width: 2rem; }';
        $baseTwoSkip = '.results-table tr.single:nth-child(2n+1) {border-top: 20px solid white !important;}';
        $baseFourSkip = '.results-table tr.single:nth-child(4n+1) {border-top: 20px solid white !important;}';
        $doublesSkip = '.results-table tr.double {border-top: 20px solid white !important;}';
        switch ($spielplanCode) {
            case LigaModel::SPIELPLAN_16E:
                return "/* SPIELPLAN_16E */ $baseFourSkip $allCss";
                break;
            case LigaModel::SPIELPLAN_16E2D:
                return "/* SPIELPLAN_16E2D */ $baseFourSkip $doublesSkip $allCss";
                break;
            case LigaModel::SPIELPLAN_16E4D:
                return "/* SPIELPLAN_16E4D */ $baseFourSkip $doublesSkip $allCss";
                break;
            case LigaModel::SPIELPLAN_6E3D:
                return "/* SPIELPLAN_6E3D */ $baseTwoSkip  $doublesSkip $allCss";
                break;
            case LigaModel::SPIELPLAN_8E2D:
                return "/* SPIELPLAN_8E2D */ $baseFourSkip $doublesSkip $allCss";
                break;
            default:
                return "/* DEFAULT */ $baseFourSkip $allCss";
        }
    }
}
