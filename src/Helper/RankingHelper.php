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

use Contao\StringUtil;

class RankingHelper implements RankingHelperInterface
{
    const PUNKTE_GEWONNEN = 3;
    const PUNKTE_UNENTSCHIEDEN = 1;
    const PUNKTE_VERLOREN = 0;

    public function getPunkte(string $score, int $ranking_model = 1): int
    {
        // $ranking_model
        // 'options'   => [ 1 => 'nach gewonnenen Legs', 2 => 'nur gewonnen/verloren' ],

        switch ($ranking_model) {
            // 'nach gewonnenen Legs'
            // Deutlich gewonnen oder knapp verloren gibt mehr Punkte als
            // knapp gewonnen bzw. deutlich verloren
            case 1:
                switch ($score) {
                    // mögliche Ergebnisse bei "best of 3"
                    case '2:0':
                        return 3;
                        //break;

                    case '2:1':
                        return 2;
                        //break;

                    case '1:2':
                        return 1;
                        //break;

                    case '0:2':
                        return 0;
                        //break;
                    // mögliche Ergebnisse bei "best of 5"
                    case '3:0':
                        return 5;
                       //break;

                    case '3:1':
                        return 4;
                        //break;

                    case '3:2':
                        return 3;
                        //break;

                    case '2:3':
                        return 2;
                        //break;

                    case '1:3':
                        return 1;
                        //break;

                    case '0:3':
                        return 0;
                        //break;

                    default:
                        //\System::log("nicht vorgesehenes Spielergebnis ".$score, __METHOD__, TL_ERROR);
                        return 0;
                }
                //break;

            case 2:
            // 'nur gewonnen/verloren'
            // gewonnen -> 1 Punkt, verloren 0 Punkte; wie gewonnen wurde spielt keine Rolle!
            default:
                [$a, $b] = StringUtil::trimsplit(':', $score);
                return (int)$a > (int)$b ? 1 : 0;
        }
    }

    /**
     * Hilfsfunktion für die Sortierung von Ergebnisarrays.
     * Verglichen werden jeweils zwei Einträge, die ihrerseites Arrays sind:
     * Die Parameter $a und $b sind von der folgenden Form:
     * [
     *    'punkte_self' => gewonnenen Punkte
     *    'punkte_other' => "verlorene" Punkte (wird üblicherweise nicht berücksichtigt)
     *    'spiele_self' => gewonnene Spiele
     *    'spiele_other' => verlorene Spiele
     *    'legs_self' => gewonnene Legs
     *    'legs_other' => verlorene Legs
     * ].
     */
    public function compareResults(array $a, array $b): int
    {
        // Bei allen Vergleichen absteigende Sortierung, also  $b <=> $a !
        // Bei Punktegleichstand ...
        if ($a['punkte_self'] === $b['punkte_self']) {
            // ... nach Spieledifferenzen. Sind diese auch gleich, ...
            if ($a['spiele_self'] - $a['spiele_other'] === $b['spiele_self'] - $b['spiele_other']) {
                // ... dann nach Legdifferenzen. Sind diese auch gleich, ...
                if ($a['legs_self'] - $a['legs_other'] === $b['legs_self'] - $b['legs_other']) {
                    // ... dann nach gewonnenen Legs
                    return $b['legs_self'] <=> $a['legs_self'];
                }

                return $b['legs_self'] - $b['legs_other'] <=> $a['legs_self'] - $a['legs_other'];
            }

            return $b['spiele_self'] - $b['spiele_other'] <=> $a['spiele_self'] - $a['spiele_other'];
        }

        return $b['punkte_self'] <=> $a['punkte_self'];
    }
}
