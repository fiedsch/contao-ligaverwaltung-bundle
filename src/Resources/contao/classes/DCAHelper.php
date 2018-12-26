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

namespace Fiedsch\LigaverwaltungBundle;

use Contao\BegegnungModel;
use Contao\Config;
use Contao\Database;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\HighlightModel;
use Contao\Image;
use Contao\LigaModel;
use Contao\MannschaftModel;
use Contao\MemberModel;
use Contao\SaisonModel;
use Contao\SpielerModel;
use Contao\SpielModel;
use Contao\SpielortModel;
use Contao\VerbandModel;
use Contao\Widget;

class DCAHelper
{
    /* Helper für tl_verband */

    /**
     * @param $row
     * @param $label
     *
     * @return string
     */
    public static function verbandLabelCallback($row, $label)
    {
        $ligen = Database::getInstance()
            ->prepare('SELECT COUNT(*) n FROM tl_liga WHERE pid=?')
            ->execute($row['id']);

        return sprintf('%s (%d Ligen)', $label, $ligen->n);
    }

    /* Helper für tl_liga */

    /**
     * * ('child_record_callback' in tl_liga).
     *
     * @param $arrRow
     *
     * @return string
     */
    public static function ligaListCallback($arrRow)
    {
        $begegnungen = Database::getInstance()
            ->prepare('SELECT COUNT(*) n FROM tl_begegnung WHERE pid=?')
            ->execute($arrRow['id']);

        return self::ligaLabelCallback($arrRow, $arrRow['name'])
            .sprintf(' (%d Begegnungen)', $begegnungen->n)//. ' <span class="tl_gray">'. json_encode($arrRow).'</span>'
            ;
    }

    /**
     * Label für eine Liga
     * * ('label_callback' in tl_mannschaft).
     *
     * @param $row
     * @param $label
     *
     * @return string
     */
    public static function ligaLabelCallback($row, $label)
    {
        $saison = SaisonModel::findById($row['saison']);
        $class = $row['aktiv'] ? 'tl_green' : 'tl_gray';

        return sprintf("<span class='%s'>%s %s</span>", $class, $label, $saison->name);
    }

    /* Helper für tl_mannschaft */

    /**
     * Label für eine Mannschaft
     * ('child_record_callback' in tl_mannschaft).
     *
     * @param $arrRow
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function mannschaftLabelCallback($arrRow)
    {
        $liga = LigaModel::findById($arrRow['liga']);
        if ('0' === $liga) {
            return sprintf("%s <span class='tl_red'>Keiner Liga zugeordnet</span>", $arrRow['name']);
        }
        if (null === $liga) {
            return sprintf("%s <span class='tl_red'>Liga '%d' existiert nicht mehr!</span>",
                $arrRow['name'],
                $arrRow['liga']);
        }
        $spielort = SpielortModel::findById($arrRow['spielort']);
        $spieler = Database::getInstance()
            ->prepare('SELECT COUNT(*) AS n FROM tl_spieler WHERE pid=?')
            ->execute($arrRow['id']);
        $anzahlSpieler = '<span class="tl_red">keine Spieler eingetragen</span>';
        $inaktiv = '';
        if ($spieler->n > 0) {
            $anzahlSpieler = sprintf('%d Spieler', $spieler->n);
        }
        if ('' === $arrRow['active']) {
            $inaktiv = ', <span class=\'tl_red\'>Mannschaft nicht aktiv</span>';
        }

        return sprintf('<div class="tl_content_left">%s, %s %s %s (%s, %s%s)</div>',
            $arrRow['name'],
            $liga->getRelated('pid')->name,
            $liga->name,
            $liga->getRelated('saison')->name,
            $spielort->name,
            $anzahlSpieler,
            $inaktiv
        );
    }

    /**
     * Alle zur Vefügung stehenden Ligen
     * ('options_callback' in tl_mannschaft).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public static function getLigaForSelect(DataContainer $dc)
    {
        $result = [];
        $ligen = LigaModel::findAll();

        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden. Bitte erst anlegen!'];
        }
        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf('%s %s %s',
                $liga->getRelated('pid')->name,
                $liga->name,
                $liga->getRelated('saison')->name
            );
        }

        return $result;
    }

    /* Helper für tl_begegnung */

    /**
     * * ('child_record_callback' in tl_begegnung).
     *
     * @param $arrRow
     * @param mixed $row
     * @param mixed $label
     *
     * @return string
     */
    /*
    public static function listBegegnungCallback($arrRow)
    {
        $home = MannschaftModel::findById($arrRow['home']);
        if ($arrRow['away']) {
            $away = MannschaftModel::findById($arrRow['away']);
        } else {
            // kein Eintrag bei away === kein Gegner === "Spielfrei"
            $away = null;
        }

        return sprintf("%s %s %s",
            $home->name,
            $away ? 'vs' : 'hat',
            $away ? $away->name : 'Spielfrei'
        );
    }
    */

    /**
     * Label für eine Begegnung (Spiel zweier Mansnchaften gegeneinander)
     * ('label_callback' in tl_begegnung).
     *
     * @param array  $row
     * @param string $label
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function labelBegegnungCallback($row, $label = '')
    {
        $liga = LigaModel::findById($row['pid']);
        $verband = VerbandModel::findById($liga->pid);
        $home = MannschaftModel::findById($row['home']);
        if ($row['away']) {
            $away = MannschaftModel::findById($row['away']);
        } else {
            // kein Eintrag bei away === kein Gegner === "Spielfrei"
            $away = null;
        }
        $eingesetzte_spieler = ['home' => [], 'away' => []];
        $spiele = SpielModel::findByPid($row['id']);
        if ($spiele) {
            $spieleHinterlegt = \count($spiele) > 0 ? sprintf('(%d Spiele)', \count($spiele)) : '';
            $punkte_home = $punkte_away = 0;
            foreach ($spiele as $spiel) {
                $punkte_home += $spiel->score_home > $spiel->score_away ? 1 : 0;
                $punkte_away += $spiel->score_home < $spiel->score_away ? 1 : 0;
                ++$eingesetzte_spieler['home'][$spiel->home];
                ++$eingesetzte_spieler['away'][$spiel->away];
            }
        }
        // nicht angetreten? (Mannschaft nur mit virtuellm Spieler '0' (='kein Spieler') angetreten).
        $is_noshow_home = 1 === \count(array_keys($eingesetzte_spieler['home'])) && 0 === array_keys($eingesetzte_spieler['home'])[0];
        $is_noshow_away = 1 === \count(array_keys($eingesetzte_spieler['away'])) && 0 === array_keys($eingesetzte_spieler['away'])[0];

        $final_score = $punkte_home + $punkte_away > 0 ? sprintf('%d:%d', $punkte_home, $punkte_away) : '';

        return sprintf("<span class='tl_gray'>%s %s %s %d. Spieltag:</span>
                        <span class='tl_blue'>%s %s %s</span>
                        <span class='tl_green'>%s</span>
                        <span class='tl_gray'>%s</span>",
            $verband->name,
            $liga->name,
            $liga->getRelated('saison')->name,
            $row['spiel_tag'],
            $home->name,
            $away ? 'vs' : 'hat',
            $away ? $away->name : 'Spielfrei',
            $final_score,
            $is_noshow_home || $is_noshow_away ? ' nicht angetreten!' : $spieleHinterlegt
        );
    }

    /**
     * Einträge für ein Ligaauswahl Dropdown
     * ('options_callback' in tl_begegnung).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public static function getAktiveLigenForSelect(DataContainer $dc)
    {
        $result = [];
        $ligen = LigaModel::findBy(['aktiv=?'], ['1']);
        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden!'];
        }
        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf('%s %s %s', $liga->getRelated('pid')->name, $liga->name, $liga->getRelated('saison')->name);
        }

        return $result;
    }

    /**
     * Einträge für ein Mannschaftsauswahl Dropdown -- nur aktive Mannschaften
     * ('options_callback' in tl_begegnung).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public static function getMannschaftenForSelect(DataContainer $dc)
    {
        $result = [];
        if ($dc->activeRecord->pid) {
            // Callback beim bearbeiten einer Begegnung
            $mannschaften = MannschaftModel::findByLiga($dc->activeRecord->pid);
        } else {
            // Callback im Listview (Filter:)
            $mannschaften = MannschaftModel::findAllActive();
        }

        if (null === $mannschaften) {
            return ['0' => 'keine Mannschaften gefunden. Bitte erst anlegen und dieser Liga zuordnen!'];
        }
        foreach ($mannschaften as $mannschaft) {
            $result[$mannschaft->id] = $mannschaft->name;
        }

        return $result;
    }

    /* Helper für tl_spieler */

    /**
     * Einträge für ein Spielerauswahl Dropdown.
     * ('options_callback' in tl_spieler).
     *
     * @param DataContainer $dc
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getSpielerForSelect(DataContainer $dc)
    {
        $result = [];
        // Wird ein bestehender Record editiert, dann das zugehörige Member in
        // das $result aufnehmen, da der folgende $query es ja nicht finden würde
        // weil es bereits in der Datenbank eingetragen und somit "im Einsatz" ist.
        if ($dc->activeRecord->member_id) {
            $member = MemberModel::findById($dc->activeRecord->member_id);
            $result[$member->id] = self::makeSpielerName($member);
        }

        if (1 === Config::get('ligaverwaltung_exclusive_model')) {
            // Modell I (edart-bayern.de-Modell);
            // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in einer
            // Liga spielen, die "in der gleichen Saison ist" (unabhängig von der Liga)
            // wie die aktuell betrachtete.
            // Annahme: ein Spieler darf in einer Saison nur in einer Mannschaft spielen!

            $saison = MannschaftModel::findById($dc->activeRecord->pid)->getRelated('liga')->saison;

            $query =
                'SELECT * FROM tl_member WHERE id NOT IN ('
                .' SELECT s.member_id FROM tl_spieler s'
                .' LEFT JOIN tl_mannschaft m ON (s.pid=m.id)'
                .' LEFT JOIN tl_liga l ON (m.liga=l.id)'
                .' WHERE l.saison=?'
                .' AND m.active=\'1\''
                .' AND s.active=\'1\''
                .' AND s.ersatzspieler<>\'1\''
                .')'
                .' AND tl_member.disable=\'\''
                //. ' ORDER BY tl_member.lastname';
                .' ORDER BY tl_member.firstname, tl_member.lastname';
            $member = Database::getInstance()->prepare($query)->execute($saison);
        } else {
            // Modell II harlekin Modell (weniger restriktiv):
            // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in der gleichen
            // Liga spielen.
            // Annahme: ein Spieler darf in einer Liga nur in einer Mannschaft spielen!

            $liga = MannschaftModel::findById($dc->activeRecord->pid)->getRelated('liga')->id;

            $query =
                'SELECT * FROM tl_member WHERE id NOT IN ('
                .' SELECT s.member_id FROM tl_spieler s'
                .' LEFT JOIN tl_mannschaft m ON (s.pid=m.id)'
                .' WHERE m.liga=?'
                .' AND m.active=\'1\''
                .' AND s.active=\'1\''
                .' AND s.ersatzspieler<>\'1\''
                .')'
                .' AND tl_member.disable=\'\''
                .' ORDER BY tl_member.lastname';
            $member = Database::getInstance()->prepare($query)->execute($liga);
        }

        while ($member->next()) {
            $result[$member->id] = sprintf('%s (%s)', self::makeSpielerName($member), $member->passnummer);
        }

        return $result;
    }

    /**
     * Return HTML Code to display one team member
     * ('child_record_callback' in tl_spieler).
     *
     * @param $arrRow
     *
     * @return string
     */
    public static function listMemberCallback($arrRow)
    {
        $member = MemberModel::findById($arrRow['member_id']);

        $teamcaptain_label = $arrRow['teamcaptain'] ? ('(Teamcaptain: '.$member->email.')') : '';
        $co_teamcaptain_label = $arrRow['co_teamcaptain'] ? ('(Co-Teamcaptain: '.$member->email.')') : '';
        $active_label = '1' === $arrRow['active'] ? '' : '<span class="tl_red">nicht aktiv</span>';
        $ersatzspieler_label = '' === $arrRow['ersatzspieler'] ? '' : '<span class="tl_red">Ersatzspieler</span>';

        return sprintf('<div class="tl_content_left">%s %s%s %s %s</div>',
            self::makeSpielerName($member),
            $teamcaptain_label,
            $co_teamcaptain_label,
            $active_label,
            $ersatzspieler_label
        );
    }

    /**
     * Button um das zum Spieler gehörige Mitglied (tl_member) in einem Modal-Window bearbeiten zu können
     * ('wizard' in tl_spieler).
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public static function editMemberWizard(DataContainer $dc)
    {
        if ($dc->value < 1) {
            return '';
        }

        return '<a href="contao/main.php?do=member&amp;act=edit&amp;id='.$dc->value
            .'&amp;popup=1&amp;rt='.REQUEST_TOKEN
            .'" title="'.specialchars($GLOBALS['TL_LANG']['tl_spieler']['editmember'][1]).'"'
            .' style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\''
            .specialchars(str_replace("'", "\\'", specialchars($GLOBALS['TL_LANG']['tl_spieler']['editmember'][1])))
            .'\',\'url\':this.href});return false">'
            .Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_spieler']['editmember'][1], 'style="vertical-align:top"')
            .'</a>';
    }

    /**
     * Sicherstellen, daß ein Spieler nur in einer Mannschaft gleichzeitig aktiv ist.
     * Ausnahme: er/sie ist als "ersatzspieler" markiert.
     *
     * @param string        $value
     * @param DataContainer $dc
     *
     * @throws \Exception
     *
     * @return string
     */
    public function spielerSaveCallback($value, $dc)
    {
        if ('1' === $value) {
            // (1) Mannschaft inaktiv?
            $mannschaft = MannschaftModel::findById($dc->activeRecord->pid);
            if ($mannschaft && !$mannschaft->active) {
                throw new \RuntimeException('Spieler kann in einer inaktiven Mannschaft nicht auf aktiv gesetzt werden');
            }
            //  (2) Spieler ist bereits in einer anderen Mannschaft aktiv (unter Berücksichtigung
            // der Config::get('ligaverwaltung_exclusive_model')-Regeln!
            if ($mannschaft) {
                if (1 === Config::get('ligaverwaltung_exclusive_model')) {
                    // [ 1 => '(in einer Mannschaft) je Saison', 2 => '(in einer Mannschaft) je Liga' ],

                    // Modell I (edart-bayern.de-Modell);
                    // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in einer
                    // Liga spielen, die "in der gleichen Saison ist" (unabhängig von der Liga)
                    // wie die aktuell betrachtete.
                    // Annahme: ein Spieler darf in einer Saison nur in einer Mannschaft spielen!

                    $filterlist = ['-1']; // damit wir bei leeren Ergabnislisten unten etwas zum implode()n haben
                    $ligen = LigaModel::findBy(['saison=?'], [$mannschaft->getRelated('liga')->saison]);
                    foreach ($ligen as $liga) {
                        $mannschaften = MannschaftModel::findBy(
                            ['active=?', 'liga=?'],
                            ['1', $liga->id]);
                        if ($mannschaften) {
                            foreach ($mannschaften as $m) {
                                if ($m->id !== $mannschaft->id) {
                                    $filterlist[] = $m->id;
                                }
                            }
                        }
                    }
                    $filterlist = implode(',', $filterlist);
                } else { // "(in einer Mannschaft) je Liga"
                    // Modell II: Harlekin Modell (weniger restriktiv):
                    // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft
                    // in der gleichen Liga (nicht Saison!) spielen.
                    // Annahme: ein Spieler darf in einer Liga nur in einer Mannschaft spielen!

                    $mannschaften = MannschaftModel::findBy(
                        ['active=?', 'liga=?'],
                        ['1', $mannschaft->getRelated('liga')->id]);
                    $filterlist = ['-1'];
                    if ($mannschaften) {
                        foreach ($mannschaften as $m) {
                            if ($m->id !== $mannschaft->id) {
                                $filterlist[] = $m->id;
                            }
                        }
                    }
                    $filterlist = implode(',', $filterlist);
                }

                $query = ' SELECT s.pid FROM tl_spieler s'
                        .' LEFT JOIN tl_member me ON (s.member_id=me.id)'
                        ." WHERE s.pid IN ($filterlist)"
                        ." AND s.active='1'"
                        ." AND s.ersatzspieler<>'1'"
                        .' AND me.id=?'
                        ;
                $queryResult = Database::getInstance()->prepare($query)->execute($dc->activeRecord->member_id);

                if ($queryResult->count() > 0) {
                    $mannschaftsnamen = [];
                    while ($queryResult->next()) {
                        $mannschaftsnamen[] = MannschaftModel::findById($queryResult->pid)->getFullName();
                    }
                    throw new \RuntimeException('Spieler ist bereits in einer anderen Mannschaft aktiv: '.implode(', ', $mannschaftsnamen));
                }
            }
        }

        return $value;
    }

    /* Helper für tl_spiel */

    /**
     * Spieler der Heimmannschaft
     * ('options_callback' in tl_spiel).
     *
     * @param DataContainer|DC_Table $dc
     *
     * @return array
     */
    public static function getHomeSpielerForSelect($dc)
    {
        $initial = [0 => 'Kein Spieler (ID 0)'];

        if (!$dc->activeRecord->pid) {
            return $initial;
        }
        $begegnung = BegegnungModel::findById($dc->activeRecord->pid);
        if (!$begegnung) {
            return $initial;
        }

        $result = [];
        $spieler = SpielerModel::findByPid($begegnung->home);
        if ($spieler) {
            foreach ($spieler as $sp) {
                $member = $sp->getRelated('member_id');
                $result[$sp->id] = self::makeSpielerName($member);
            }
        }
        // Nach Namen sortieren
        uasort($result, function ($a, $b) {
            return $a < $b ? -1 : ($a > $b ? +1 : 0);
        });

        return $result;
    }

    /**
     * Spieler der Gastmannschaft
     * ('options_callback' in tl_spiel).
     *
     * @param DataContainer|DC_Table $dc
     *
     * @return array
     */
    public static function getAwaySpielerForSelect($dc)
    {
        $initial = [0 => 'Kein Spieler (ID 0)'];

        if (!$dc->activeRecord->pid) {
            return $initial;
        }
        $begegnung = BegegnungModel::findById($dc->activeRecord->pid);
        if (!$begegnung) {
            return $initial;
        }

        $result = []; // $initial;
        $spieler = SpielerModel::findByPid($begegnung->away);
        if ($spieler) {
            foreach ($spieler as $sp) {
                $member = $sp->getRelated('member_id');
                $result[$sp->id] = self::makeSpielerName($member);
            }
        }
        // Nach Namen sortieren
        uasort($result, function ($a, $b) {
            return $a < $b ? -1 : ($a > $b ? +1 : 0);
        });

        return $result;
    }

    /**
     * Label für ein Spiel
     * ('child_record_callback' in tl_spiel).
     *
     * @param array $row
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function listSpielCallback($row)
    {
        $class_home = $row['score_home'] > $row['score_away'] ? 'tl_green' : '';
        $class_away = $row['score_home'] > $row['score_away'] ? '' : 'tl_green';

        switch ($row['spieltype']) {
            case 1:
                $spielerHome = SpielerModel::findById($row['home']);
                $spielerAway = SpielerModel::findById($row['away']);
                /** @var MemberModel $memberHome */
                $memberHome = $spielerHome ? $spielerHome->getRelated('member_id') : null;
                /** @var MemberModel $memberAway */
                $memberAway = $spielerAway ? $spielerAway->getRelated('member_id') : null;
                if ($memberHome) {
                    $memberHomeDisplayname = self::makeSpielerName($memberHome);
                } else {
                    $memberHomeDisplayname = 'Kein Spieler (ID '.$row['home'].')';
                }
                if ($memberAway) {
                    $memberAwayDisplayname = self::makeSpielerName($memberAway);
                } else {
                    $memberAwayDisplayname = 'Kein Spieler (ID '.$row['away'].')';
                }

                return sprintf("(%d) <span class='%s'>%s</span> : <span class='%s'>%s</span> <span class='tl_gray'>%d:%d</span>",
                    $row['slot'],
                    $class_home,
                    $memberHomeDisplayname,
                    $class_away,
                    $memberAwayDisplayname,
                    $row['score_home'],
                    $row['score_away']
                );
                break;
            case 2:
                $spielerHome = SpielerModel::findById($row['home']);
                /** @var MemberModel $memberHome */
                $memberHome = $spielerHome ? $spielerHome->getRelated('member_id') : null;
                $spielerHome2 = SpielerModel::findById($row['home2']);
                /** @var MemberModel $memberHome2 */
                $memberHome2 = $spielerHome2 ? $spielerHome2->getRelated('member_id') : null;
                $spielerAway = SpielerModel::findById($row['away']);
                /** @var MemberModel $memberAway */
                $memberAway = $spielerAway ? $spielerAway->getRelated('member_id') : null;
                $spielerAway2 = SpielerModel::findById($row['away2']);
                /** @var MemberModel $memberAway2 */
                $memberAway2 = $spielerAway2 ? $spielerAway2->getRelated('member_id') : null;

                if ($memberHome) {
                    $memberHomeDisplayname = self::makeSpielerName($memberHome);
                } else {
                    $memberHomeDisplayname = 'Kein Spieler (ID '.$row['home'].')';
                }
                if ($memberHome2) {
                    $memberHome2Displayname = self::makeSpielerName($memberHome2);
                } else {
                    $memberHome2Displayname = 'Kein Spieler (ID '.$row['home2'].')';
                }
                if ($memberAway) {
                    $memberAwayDisplayname = self::makeSpielerName($memberAway);
                } else {
                    $memberAwayDisplayname = 'Kein Spieler (ID '.$row['away'].')';
                }
                if ($memberAway2) {
                    $memberAway2Displayname = self::makeSpielerName($memberAway2);
                } else {
                    $memberAway2Displayname = 'Kein Spieler (ID '.$row['away2'].')';
                }

                return sprintf("(%d) <span class='%s'>%s + %s</span> : <span class='%s'>%s + %s</span> <span class='tl_gray'>%d:%d</span>",
                    $row['slot'],
                    $class_home,
                    $memberHomeDisplayname,
                    $memberHome2Displayname,
                    $class_away,
                    $memberAwayDisplayname,
                    $memberAway2Displayname,
                    $row['score_home'],
                    $row['score_away']
                );
                break;
            default:
                return sprintf("invalid value for 'spieltype': <span class='tl_gray'>%s</span>",
                    json_encode($row)
                );
        }
    }

    /* Helper für tl_cont\ent */

    /**
     * Liste aller definierten Verbände
     * ('options_callback' in tl_content).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public static function getAlleVerbaendeForSelect(DataContainer $dc)
    {
        $result = [];
        $verbaende = VerbandModel::findAll();
        if (null === $verbaende) {
            return ['0' => 'keine Verbände gefunden!'];
        }
        foreach ($verbaende as $verband) {
            $result[$verband->id] = $verband->name;
        }

        return $result;
    }

    /**
     * Liste aller definierte Ligen
     * ('options_callback' in tl_content).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public static function getAlleLigenForSelect(DataContainer $dc)
    {
        $result = [];
        $ligen = LigaModel::findAll();
        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden!'];
        }
        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf('%s %s %s',
                $liga->name,
                $liga->getRelated('pid')->name,
                $liga->getRelated('saison')->name
            );
        }

        return $result;
    }

    /**
     * Einträge für ein Mannschaftsauswahl Dropdown. Da hier alle Ligen aller Saisons in
     * Betracht kommen und eine Mannschaft gleichen Namens daher mehrfach auftaucht,
     * hängen wir Liga und Saison an, um die Auswahl eindeutig zu machen.
     * ('options_callback' in tl_content).
     *
     * @param DataContainer $dc
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function getAlleMannschaftenForSelect(DataContainer $dc)
    {
        $result = [];
        if ($dc && $dc->activeRecord->liga) {
            $mannschaften = MannschaftModel::findByLiga($dc->activeRecord->liga, ['order' => 'name ASC']);
        } else {
            $mannschaften = MannschaftModel::findAll(['order' => 'name ASC']);
        }
        if (null === $mannschaften) {
            return ['0' => 'keine Mannschaften gefunden. Liga wählen und speichern!'];
        }
        foreach ($mannschaften as $mannschaft) {
            $liga = $mannschaft->getRelated('liga');
            $saison = null;
            if ($liga) {
                $saison = $liga->getRelated('saison');
            }
            $result[$mannschaft->id] = sprintf('%s (%s %s)',
                $mannschaft->name,
                $liga ? $liga->name : 'keine Liga :-(',
                $saison ? $saison->name : 'keine Saison :-('
            );
        }
        // nicht bei der Spielerliste, da wir dort zusätzlich eine Auswahl der
        // Liga bräuchten, damit "alle Mannschaften" Sinn ergibt
        // Dito für die Mannschaftsseite.
        if (!\in_array($dc->activeRecord->type, ['spielerliste', 'mannschaftsseite'], true)) {
            $result[0] = 'alle Mannschaften'; // z.B. für "Spielerranking" einer gesamten Liga
        }

        return $result;
    }

    /**
     * Einträge für ein Dropdown in dem die Begegnung ausgewählt werden kann, für die
     * ein Spielbericht erstellt werden soll.
     *
     * @return array
     */
    public function getAlleBegegnungen()
    {
        $result = [];
        $begegnungen = BegegnungModel::findAll(['order' => 'spiel_am ASC']);
        if ($begegnungen) {
            foreach ($begegnungen as $begegnung) {
                $result[$begegnung->id] = $begegnung->getLabel('full');
            }
        }

        return $result;
    }

    /**
     * @param DataContainer|null $dc
     *
     * @return array
     */
    public function getSpielerForHighlight($dc)
    {
        $result = [];
        $spieler = null;
        if ($dc && $dc->activeRecord) {
            $begegnung = BegegnungModel::findById($dc->activeRecord->begegnung_id);
            $spieler = SpielerModel::findBy(
                ['(tl_spieler.pid=? OR tl_spieler.pid=?) AND (tl_spieler.active=\'1\')'],
                [$begegnung->home, $begegnung->away]
            );
        }
        if ($spieler) {
            foreach ($spieler as $s) {
                $result[$s->id] = $s->getNameAndMannschaft();
            }
        }
        asort($result);

        return $result;
    }

    public function getBegegnungenForHighlight()
    {
        $result = [];
        $begegnungen = BegegnungModel::findAll(['eager' => true]);
        foreach ($begegnungen as $begegnung) {
            // Dieser Filter reduziert zwar bei Neueingaben die Anzahl
            // der Optionen im Drop-Down, führt aber beim Bearbeiten alter
            // Records (abgeschlossenen Ligen) dazu, daß das Dropdown nicht
            // korrekt zum bereits erfassten Wert gesetzt ist (werden kann).
            // Workaround: 'filter' in tl_highlight setzen!
            if ($begegnung->getRelated('pid')->aktiv) {
                $result[$begegnung->id] = $begegnung->getLabel($mode = 'full');
            }
        }
        asort($result);

        return $result;
    }

    /**
     * @param string $strRegexp
     * @param string $varValue
     * @param Widget $objWidget
     *
     * @return bool
     */
    public function addCustomRegexp($strRegexp, $varValue, Widget $objWidget)
    {
        $varValue = str_replace(' ', '', $varValue);
        if ('csvdigit' === $strRegexp) {
            // if (!preg_match('/^(\d+(,(?=\d)){0,1})+$/', $varValue)) {
            // Überflüssige Kommata werden im save_callback entfernt, wir prüfen
            // hier nicht darauf um den User nicht zu "überfordern"
            if (!preg_match('/^(\d+,{0,1})+$/', $varValue)) {
                $objWidget->addError('Eingabe muss eine Zahl oder eine eine durch Komma getrennte Liste von Zahlen sein!');
            }

            return true;
        }

        return false;
    }

    /**
     * Eine kommaseparierte Liste von Zahlen aufbereiten:
     * - Leerzeichen entfernen
     * - leere Zellen (entstanden durch überflüssige Kommata) entfernen
     *   (Bsp.: "1,2,"  Ohne Bereinigung => [1,2,''], Soll => [1,2].
     *
     * @param string        $value
     * @param DataContainer $dc
     *
     * @return string
     */
    public function cleanCsvDigitList($value, $dc)
    {
        $entries = explode(',', str_replace(' ', '', $value));
        $entries = array_filter($entries, function ($entry) {
            return '' !== $entry;
        });
        sort($entries);
        switch ($dc->activeRecord->type) {
            case HighlightModel::TYPE_180:
            case HighlightModel::TYPE_171:
                if (\count($entries) > 1) {
                    throw new \RuntimeException('Bitte nur die Anzahl eingeben!');
                }
                break;
            case HighlightModel::TYPE_SHORTLEG:
                if (array_filter($entries, function ($el) { return $el > 20; })) {
                    throw new \RuntimeException('Bitte nur Werte kleiner/gleich 20 eingeben!');
                }
                break;
            case HighlightModel::TYPE_HIGHFINISH:
                if (array_filter($entries, function ($el) { return $el < 100; })) {
                    throw new \RuntimeException('Bitte nur Werte größer/gleich 100 eingeben!');
                }
                break;
        }
        //throw new \RuntimeException("TEST".print_r($dc->activeRecord->row(), true));
        return implode(',', $entries);
    }

    /**
     * Label für einen Spieler
     * Eine Funktion, die bestimmt, ob wir "Nachname, Vorname" oder "Vorname Nachname"
     * haben wollen.
     *
     * @param MemberModel|Database\Result $member
     *
     * @return string
     */
    public static function makeSpielerName($member)
    {
        return self::makeSpielerNameFromParts($member->firstname, $member->lastname, $member->anonymize);
    }

    /**
     * @param string $firstname
     * @param string $lastname
     * @param bool   $anonymize
     *
     * @return string
     */
    public static function makeSpielerNameFromParts($firstname, $lastname, $anonymize)
    {
        if ($anonymize) {
            return SpielerModel::ANONYM_LABEL;
        }
        // return sprintf("%s, %s", $lastname, $firstname);
        return sprintf('%s %s', $firstname, $lastname);
    }
}
