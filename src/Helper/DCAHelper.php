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


use Contao\Config;
use Contao\Database;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Date;
use Contao\System;
use Contao\Image;
use Contao\MemberModel;
use Contao\StringUtil;
use Contao\Widget;
use Exception;
use RuntimeException;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Model\HighlightModel;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Model\SpielModel;
use Fiedsch\Ligaverwaltung\Model\SpielortModel;
use Fiedsch\Ligaverwaltung\Model\VerbandModel;
use function count;
use function in_array;
use function preg_match;
use function str_replace;
use function array_keys;

class DCAHelper
{
    const string DOES_NOT_EXIST = '[ex. nicht mehr]';

    /**
     * Callback für das Label eines Verbands
     * tl_verband.fields.label.label_callback
     */
    public static function verbandLabelCallback(array $row, string $label): string
    {
        $ligen = Database::getInstance()
            ->prepare('SELECT COUNT(*) n FROM tl_liga WHERE pid=?')
            ->execute($row['id'])
        ;

        return sprintf('%s (%d Ligen)', $label, $ligen->n);
    }

    /**
     * Callback für das Label eines Child Records tl_liga.child_record_callback
     */
    public static function ligaListCallback(array $row): string
    {
        $begegnungen = Database::getInstance()
            ->prepare('SELECT COUNT(*) n FROM tl_begegnung WHERE pid=?')
            ->execute($row['id'])
        ;

        return self::ligaLabelCallback($row, $row['name'])
            .sprintf(' (%d Begegnungen)', $begegnungen->n)//. ' <span class="tl_gray">'. json_encode($arrRow).'</span>'
            ;
    }

    /**
     * Label für eine Liga
     * (tl_liga.fields.label.label_callback
     */
    public static function ligaLabelCallback(array $row, string $label): string
    {
        $saison = SaisonModel::findById($row['saison']);
        $class = $row['aktiv'] ? 'tl_green' : 'tl_gray';

        return sprintf("<span class='%s'>%s %s</span><code class='tl_gray'>[%s]</code>", $class, $label, $saison?->name, $saison?->alias);
    }

    /**
     * Label für eine Mannschaft
     * tl_mannschaft.fields.label.label_callback
     * @throws Exception
     *
     */
    public static function mannschaftLabelCallback(array $row): string
    {
        $liga = LigaModel::findById($row['liga']);

        if ('0' === $liga) {
            return sprintf("%s <span class='tl_red'>Keiner Liga zugeordnet</span>", $row['name']);
        }

        if (null === $liga) {
            return sprintf("%s <span class='tl_red'>Liga '%d' existiert nicht mehr!</span>",
                $row['name'],
                $row['liga']);
        }
        $spielort = SpielortModel::findById($row['spielort']);
        $spieler = Database::getInstance()
            ->prepare('SELECT COUNT(*) AS n FROM tl_spieler WHERE pid=?')
            ->execute($row['id'])
        ;
        $anzahlSpieler = '<span class="tl_red">keine Spieler eingetragen</span>';

        if ($spieler->n > 0) {
            $anzahlSpieler = sprintf('%d Spieler', $spieler->n);
        }

        $show_verband = Config::get('show_verband_in_select');

        return sprintf('<div class="tl_content_left %s">%s, %s <span class="tl_green">%s %s</span> <span class="tl_gray">(%s, %s)</span></div>',
            $row['active'] ? '' : 'tl_gray',
            $row['name'],
            $show_verband ? $liga->getRelated('pid')->name : '',
            $liga->name,
            $liga->getRelated('saison')->name,
            $spielort->name,
            $anzahlSpieler
        );
    }

    /**
     * Alle zur Vefügung stehenden Ligen
     * tl_mannschaft.fields.liga.options_callback
     *
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public static function getLigaForSelect(DataContainer $dc): array
    {
        $result = [];
        $ligen = LigaModel::findAll();

        if (null === $ligen) {
            return ['0' => 'keine Ligen gefunden. Bitte erst anlegen!'];
        }

        $show_verband = Config::get('show_verband_in_select');

        foreach ($ligen as $liga) {
            $result[$liga->id] = sprintf('%s %s %s',
                $show_verband ? $liga->getRelated('pid')?->name : '', // optionally do not add liga name as most installations have only one liga
                $liga->name,
                $liga->getRelated('saison')?->name
            );
        }

        return $result;
    }

//     /**
//      * Label für eine Begegnung (Spiel zweier Mannsnchaften gegeneinander)
//      * tl_begegnung.sorting.child_record_callback
//      * und
//      * tl_begegnung.fields.label.label_callback
//      *
//      * @throws Exception
//      */
//     public static function labelBegegnungCallbackTableView(array $row, string $label, DataContainer $dc, array $labels): array
//     { //dd(func_get_args());
//         $show_verband = Config::get('show_verband_in_select');
//         $liga = LigaModel::findById($row['pid']);
//         $verband = $show_verband ? VerbandModel::findById($liga->pid) : null;
//         $labels[0] = sprintf('%s %s %s', $verband?->name, $liga->name, $liga->getRelated('saison')->name);
//
//         /** @var ValueFormatter $valueFormatter */
//         $valueFormatter = System::getContainer()->get('contao.data_container.value_formatter');
//         $GLOBALS['TL_DCA']['tl_begegnung']['fields']['spiel_am']['eval']['rgxp'] = 'date'; // Formatierung ohne Uhrzeit
//         $labels[2] = $valueFormatter->format('tl_begegnung', 'spiel_am', $row['spiel_am'], /*$dc*/null);
//
//         $spielfrei = $labels['4'] === '0';
//
//         $labels[4] = $spielfrei ? 'spielfrei' : $labels['4'];
//
//         $begegnung = BegegnungModel::findById($row['id']);
//         $labels[5] = $spielfrei ? ' ' : $begegnung->getScore();
//
//         return $labels;
//     }
    public static function labelBegegnungCallbackChildView(array $row, string $label = ''): string
    {
        $home = MannschaftModel::findById($row['home']);
        if (null === $home) {
            return $label;
        }

        if ($row['away']) {
            $away = MannschaftModel::findById($row['away']);
        } else {
            // kein Eintrag bei away === kein Gegner === "Spielfrei"
            $away = null;
        }
        $spieleHinterlegt = '';
        $punkte_home = $punkte_away = 0;
        $eingesetzte_spieler = ['home' => [], 'away' => []];
        $spiele = SpielModel::findByPid($row['id']);

        if ($spiele) {
            $spieleHinterlegt = sprintf('(%d Spiele)', count($spiele));

            foreach ($spiele as $spiel) {
                $punkte_home += $spiel->score_home > $spiel->score_away ? 1 : 0;
                $punkte_away += $spiel->score_home < $spiel->score_away ? 1 : 0;
                $eingesetzte_spieler['home'][$spiel->home] = ($eingesetzte_spieler['home'][$spiel->home] ?? 0)+1;
                $eingesetzte_spieler['away'][$spiel->away] = ($eingesetzte_spieler['away'][$spiel->away] ?? 0)+1;
            }
        }
        // nicht angetreten? (Mannschaft nur mit virtuellm Spieler '0' (='kein Spieler') angetreten).
        $is_noshow_home = 1 === count(array_keys($eingesetzte_spieler['home'])) && 0 === array_keys($eingesetzte_spieler['home'])[0];
        $is_noshow_away = 1 === count(array_keys($eingesetzte_spieler['away'])) && 0 === array_keys($eingesetzte_spieler['away'])[0];

        $final_score = $punkte_home + $punkte_away > 0 ? sprintf('%d:%d', $punkte_home, $punkte_away) : '';

        $spielDate = ', ' . Date::parse(Config::get('datime') ?? 'd.m.Y', $row['spiel_am']);
        if (!$row['away']) {
            $spielDate = '';
        }
        if ($row['postponed']) {
            $spielDate = ' verschoben';
        }


        return sprintf("<span class='tl_gray'>%d. Spieltag%s:</span>
                        <span class='tl_blue'>%s %s %s</span>
                        <span class='tl_green'>%s</span>
                        <span class='tl_gray'>%s</span>",
            $row['spiel_tag'],
            $spielDate,
            $home?->getShortName() ?? self::DOES_NOT_EXIST,
            $away ? 'vs' : 'hat',
            $away ? $away->name : 'Spielfrei',
            $final_score,
            $is_noshow_home || $is_noshow_away ? ' nicht angetreten!' : $spieleHinterlegt
        );
    }

    /**
     * Einträge für ein Ligaauswahl Dropdown
     * tl_begegnung.fields.pid.options_callback
     *
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public static function getAktiveLigenForSelect(DataContainer $dc): array
    {
        $result = [];
        $ligen = LigaModel::findBy(['aktiv=?'], [1]);

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
     * tl_begegnung.fields.home.options_callback
     * und
     * tl_begegnung.fields.away.options_callback
     */
    public static function getMannschaftenForSelect(DataContainer $dc): array
    {
        $result = [];
        $activeRecord = $dc->getActiveRecord();

        if ($activeRecord && $activeRecord['pid'] ?? null) {
            // Callback beim Bearbeiten einer Begegnung (auch inaktive berücksichtigen, damit wir alte Begegnungen noch editieren können)
            $mannschaften = MannschaftModel::findByLiga($activeRecord['pid']);
        } else {
            // Callback im Listview (Filter:) wir werden hier aufgerufen (TODO: weil ...), müssen aber kein Ergebnis liefern, da die Dropdowns von Contao standardmäßig gefüllt werden
            return [];
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
     * tl_spieler.fields.member_id.options_callback
     *
     * @throws Exception
     */
    public static function getSpielerForSelect(DataContainer $dc): array
    {
        $result = [];

        $activeRecord = $dc->getActiveRecord();

        // Wird ein bestehender Record editiert, dann das zugehörige Member in
        // das $result aufnehmen, da der folgende $query es ja nicht finden würde
        // weil es bereits in der Datenbank eingetragen und somit "im Einsatz" ist.
        if ($activeRecord && $activeRecord['member_id']) {
            $member = MemberModel::findById($activeRecord['member_id']);
            $result[$member->id] = self::makeSpielerName($member);
        }

        if (1 === Config::get('ligaverwaltung_exclusive_model')) {
            // Modell I (edart-bayern.de-Modell);
            // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in einer
            // Liga spielen, die "in der gleichen Saison ist" (unabhängig von der Liga)
            // wie die aktuell betrachtete.
            // Annahme: ein Spieler darf in einer Saison nur in einer Mannschaft spielen!
            $activeRecord = $dc->getActiveRecord();

            $saison = MannschaftModel::findById($activeRecord['pid'])->getRelated('liga')->saison;

            $query =
                'SELECT * FROM tl_member WHERE id NOT IN ('
                .' SELECT s.member_id FROM tl_spieler s'
                .' LEFT JOIN tl_mannschaft m ON (s.pid=m.id)'
                .' LEFT JOIN tl_liga l ON (m.liga=l.id)'
                .' WHERE l.saison=?'
                .' AND m.active=1'
                .' AND s.active=1'
                .' AND s.ersatzspieler=0'
                .' AND s.member_id IS NOT NULL'
                .')'
                .' AND tl_member.disable=0'
                //. ' ORDER BY tl_member.lastname';
                .' ORDER BY tl_member.firstname, tl_member.lastname';
            $member = Database::getInstance()->prepare($query)->execute($saison);
        } else {
            // Modell II harlekin Modell (weniger restriktiv):
            // Alle Spieler, die nicht bereits in einer (anderen) Mannschaft in der gleichen
            // Liga spielen.
            // Annahme: ein Spieler darf in einer Liga nur in einer Mannschaft spielen!

            $liga = MannschaftModel::findById($activeRecord['pid'])->getRelated('liga')->id;

            $query =
                'SELECT * FROM tl_member WHERE id NOT IN ('
                .' SELECT s.member_id FROM tl_spieler s'
                .' LEFT JOIN tl_mannschaft m ON (s.pid=m.id)'
                .' WHERE m.liga=?'
                .' AND m.active=1'
                .' AND s.active=1'
                .' AND s.ersatzspieler=0'
                .' AND s.member_id IS NOT NULL'
                .')'
                .' AND tl_member.disable=0'
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
     * tl_spieler.list.child_record_callback
     *
     * @param $arrRow
     *
     * @return string
     */
    public static function listMemberCallback($arrRow): string
    {
        $member = MemberModel::findById($arrRow['member_id']);
        $printedMobile = !empty($member->mobile) ? $member->mobile : 'Mobilfunknummer nicht hinterlegt';
        $printedEMail = !empty($member->email) ? $member->email : 'E-Mail-Adresse nicht hinterlegt';

        $teamcaptain_label = $arrRow['teamcaptain'] ? (', <span>Teamcaptain</span> ('.$printedMobile.', '. $printedEMail .')') : '';
        $co_teamcaptain_label = $arrRow['co_teamcaptain'] ? ('(Co-Teamcaptain: '.$printedEMail.')') : '';
        $ersatzspieler_label = 0 === $arrRow['ersatzspieler'] ? '' : ', <span class="tl_red">Ersatzspieler</span>';

        $member_no_longer_exists = (!$member && $arrRow['member_id'] > 0);

        if ($member_no_longer_exists) {
            return sprintf('Mitglied mit der ID %d existiert nicht mehr', $arrRow['member_id']);
        }

        return sprintf('<div class="tl_content_left">%s<span class="tl_gray">%s%s</span>%s</div>',
            self::makeSpielerName($member),
            $teamcaptain_label,
            $co_teamcaptain_label,
            $ersatzspieler_label
        );
    }

    /**
     * Button um das zum Spieler gehörige Mitglied (tl_member) in einem Modal-Window bearbeiten zu können
     * tl_spieler.fields.member_id.wizard
     */
    public static function editMemberWizard(DataContainer $dc): string
    {
        if ($dc->value < 1) {
            return '';
        }

        $requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        return '<a href="contao/main.php?do=member&amp;act=edit&amp;id='.$dc->value
            .'&amp;popup=1&amp;rt='.$requestToken
            .'" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['tl_spieler']['editmember'][1]).'"'
            .' style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\''
            .StringUtil::specialchars(str_replace("'", "\\'", StringUtil::specialchars($GLOBALS['TL_LANG']['tl_spieler']['editmember'][1])))
            .'\',\'url\':this.href});return false">'
            .Image::getHtml('alias.svg', $GLOBALS['TL_LANG']['tl_spieler']['editmember'][1], 'style="vertical-align:top"')
            .'</a>';
    }

    /**
     * Sicherstellen, daß ein Spieler nur in einer Mannschaft gleichzeitig aktiv ist.
     * Ausnahme: er/sie ist als "ersatzspieler" markiert.
     * tl_spieler.fields.active.save_callback
     *
     * @throws Exception
     */
    public function spielerSaveCallback(string $value, DataContainer $dc): string
    {
        $activeRecord = $dc->getActiveRecord();
        if ('1' === $value) {
            // (1) Mannschaft inaktiv?
            $mannschaft = MannschaftModel::findById($activeRecord['pid']);

            if ($mannschaft && !$mannschaft->active) {
                throw new RuntimeException('Spieler kann in einer inaktiven Mannschaft nicht auf aktiv gesetzt werden');
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
                        ." AND s.active=1"
                        ." AND s.ersatzspieler=0"
                        .' AND me.id=?'
                        ;
                $queryResult = Database::getInstance()->prepare($query)->execute($activeRecord['member_id']);

                if ($queryResult->count() > 0) {
                    $mannschaftsnamen = [];

                    while ($queryResult->next()) {
                        $mannschaftsnamen[] = MannschaftModel::findById($queryResult->pid)->getFullName();
                    }

                    throw new RuntimeException('Spieler ist bereits in einer anderen Mannschaft aktiv: '.implode(', ', $mannschaftsnamen));
                }
            }
        }

        return $value;
    }

    /**
     * Spieler der Heimmannschaft
     *  tl_spiel.fields.home.options_callback
     */
    public static function getHomeSpielerForSelect(DataContainer $dc): array
    {
        $initial = [0 => 'Kein Spieler (ID 0)'];

        $activeRecord = $dc->getActiveRecord();

        if (!$activeRecord || !$activeRecord['pid']) {
            return $initial;
        }
        $begegnung = BegegnungModel::findById($activeRecord['pid']);

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
        uasort(
            $result,
            static function ($a, $b) {
                return $a < $b ? -1 : ($a > $b ? +1 : 0);
            }
        );

        return $result;
    }

    /**
     * Spieler der Gastmannschaft
     *  tl_spiel.fields.away.options_callback
 */
    public static function getAwaySpielerForSelect(DataContainer $dc): array
    {
        $initial = [0 => 'Kein Spieler (ID 0)'];
        $activeRecord = $dc->getActiveRecord();

        if (!$activeRecord || !$activeRecord['pid']) {
            return $initial;
        }
        $begegnung = BegegnungModel::findById($activeRecord['pid']);

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
        uasort(
            $result,
            static function ($a, $b) {
                return $a < $b ? -1 : ($a > $b ? +1 : 0);
            }
        );

        return $result;
    }

    /**
     * Label für ein Spiel
     * tl_spiel.list.sorting.child_record_callback
     *
     * @throws Exception
     */
    public static function listSpielCallback(array $row): string
    {
        $class_home = $row['score_home'] > $row['score_away'] ? 'tl_green' : '';
        $class_away = $row['score_home'] > $row['score_away'] ? '' : 'tl_green';

        switch ($row['spieltype']) {
            case 1:
                $spielerHome = SpielerModel::findById($row['home']);
                $spielerAway = SpielerModel::findById($row['away']);
                /** @var MemberModel $memberHome */
                $memberHome = $spielerHome?->getRelated('member_id');
                /** @var MemberModel $memberAway */
                $memberAway = $spielerAway?->getRelated('member_id');

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
                //break;

            case 2:
                $spielerHome = SpielerModel::findById($row['home']);
                /** @var MemberModel $memberHome */
                $memberHome = $spielerHome?->getRelated('member_id');
                $spielerHome2 = SpielerModel::findById($row['home2']);
                /** @var MemberModel $memberHome2 */
                $memberHome2 = $spielerHome2?->getRelated('member_id');
                $spielerAway = SpielerModel::findById($row['away']);
                /** @var MemberModel $memberAway */
                $memberAway = $spielerAway?->getRelated('member_id');
                $spielerAway2 = SpielerModel::findById($row['away2']);
                /** @var MemberModel $memberAway2 */
                $memberAway2 = $spielerAway2?->getRelated('member_id');

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
                //break;

            default:
                return sprintf("invalid value for 'spieltype': <span class='tl_gray'>%s</span>",
                    json_encode($row)
                );
        }
    }

    /**
     * Liste aller definierten Verbände
     * tl_content.fields.verband.options_callback
     * @noinspection PhpUnusedParameterInspection
     */
    public static function getAlleVerbaendeForSelect(DataContainer $dc): array
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
     * tl_content.fields.liga.options_callback
     *
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public static function getAlleLigenForSelect(DataContainer $dc): array
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
                $liga->getRelated('saison')->alias
            );
        }

        return $result;
    }

    /**
     * Liste aller definierte Saisons
     * tl_content.fields.saison.options_callback
     *
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public static function getAlleSaisonsForSelect(DataContainer $dc): array
    {
        $result = [];
        $saisons = SaisonModel::findAll(['order' => 'name ASC']);

        if (null === $saisons) {
            return ['0' => 'keine Saisons gefunden!'];
        }

        foreach ($saisons as $saison) {
            $result[$saison->id] = sprintf('%s',
                $saison->name
            );
        }

        return $result;
    }

    /**
     * Einträge für ein Mannschaftsauswahl Dropdown. Da hier alle Ligen aller Saisons in
     * Betracht kommen und eine Mannschaft gleichen Namens daher mehrfach auftaucht,
     * hängen wir Liga und Saison an, um die Auswahl eindeutig zu machen.
     * tl_content.fields.mannschaft.options_callback
     *
     * @throws Exception
     */
    public static function getAlleMannschaftenForSelect(DataContainer $dc): array
    {
        $result = [];
        $activeRecord = $dc->getActiveRecord();
        if ($activeRecord && $activeRecord['liga']) {
            $mannschaften = MannschaftModel::findByLiga($activeRecord['liga'], ['order' => 'name ASC']);
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
                $saison ? $saison->alias : 'keine Saison :-('
            );
        }
        // nicht bei der Spielerliste, da wir dort zusätzlich eine Auswahl der
        // Liga bräuchten, damit "alle Mannschaften" Sinn ergibt
        // Dito für die Mannschaftsseite.
        if (!in_array($activeRecord['type'], ['spielerliste', 'mannschaftsseite'], true)) {
            // TODO: put "alle Mannschaften" to the start of the List (without reindexing!)
            $result[MannschaftModel::ALLE_MANNSCHAFTEN] = 'alle Mannschaften'; // z.B. für "Spielerranking" einer gesamten Liga
        }

        return $result;
    }

    /**
     * Einträge für ein Dropdown in dem die Begegnung ausgewählt werden kann, für die
     * ein Spielbericht erstellt werden soll.
     *  tl_content.fields.begegnung.options_callback
     *
     * @return array
     * @throws Exception
     */
    public function getAlleBegegnungen(): array
    {
        $result = [];
        $begegnungen = BegegnungModel::findAll(['order' => 'spiel_am ASC']);

        if ($begegnungen) {
            foreach ($begegnungen as $begegnung) {
                $result[$begegnung->id] = $begegnung->getLabel();
            }
        }

        return $result;
    }

    /**
     * tl_highlight.fields.spieler_id.options_callback
     * @throws Exception
     */
    public function getSpielerForHighlight(?DataContainer $dc): array
    {
        $result = [];
        $spieler = null;
        $activeRecord = $dc?->getActiveRecord();

        if ($activeRecord) {
            $begegnung = BegegnungModel::findById($activeRecord['begegnung_id']);
            $spieler = SpielerModel::findBy(
                ['(tl_spieler.pid=? OR tl_spieler.pid=?) AND (tl_spieler.active=1)'],
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

    /**
     * tl_highlight.fields.begegnung_id.options_callback
     * @throws Exception
     *
     */
    public function getBegegnungenForHighlight(): array
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
                $result[$begegnung->id] = $begegnung->getLabel();
            }
        }
        asort($result);

        return $result;
    }

    public function addCustomRegexp(string $strRegexp, string $varValue, Widget $objWidget): bool
    {
        $varValue = str_replace(' ', '', $varValue);

        if ('csvdigit' === $strRegexp) {
            // if (!preg_match('/^(\d+(,(?=\d))?)+$/', $varValue)) {
            // Überflüssige Kommata werden im save_callback entfernt, wir prüfen
            // hier nicht darauf um den User nicht zu "überfordern"
            if (!preg_match('/^(\d+,?)+$/', $varValue)) {
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
     *   (Bsp.: "1,2," Ohne Bereinigung => [1,2,''], Soll => [1,2].
     */
    public function cleanCsvDigitList(string $value, DataContainer $dc): string
    {
        $entries = explode(',', str_replace(' ', '', $value));
        $entries = array_filter(
            $entries,
            static function ($entry) {
                return '' !== $entry;
            }
        );
        sort($entries);

        $activeRecord = $dc->getActiveRecord();

        switch ($activeRecord['type']) {
            case HighlightModel::TYPE_180:
            case HighlightModel::TYPE_171:
                if (count($entries) > 1) {
                    throw new RuntimeException('Bitte nur die Anzahl eingeben!');
                }
                break;

            case HighlightModel::TYPE_SHORTLEG:
                if (array_filter($entries, static function ($el) { return $el > 20; })) {
                    throw new RuntimeException('Bitte nur Werte kleiner/gleich 20 eingeben!');
                }
                break;

            case HighlightModel::TYPE_HIGHFINISH:
                if (array_filter($entries, static function ($el) { return $el < 100; })) {
                    throw new RuntimeException('Bitte nur Werte größer/gleich 100 eingeben!');
                }
                break;
        }

        return implode(',', $entries);
    }

    /**
     * Label für einen Spieler
     * Eine Funktion, die bestimmt, ob wir "Nachname, Vorname" oder "Vorname Nachname"
     * haben wollen.
     */
    public static function makeSpielerName(MemberModel|Result $member = null): string
    {
        return self::makeSpielerNameFromParts($member?->firstname ?? '-', $member?->lastname ?? '-');
    }

    public static function makeSpielerNameFromParts(string $firstname, string $lastname, bool $anonymize = false): string
    {
        if ($anonymize) {
            return SpielerModel::ANONYM_LABEL;
        }

        return sprintf('%s %s', $firstname, $lastname);
    }
}
