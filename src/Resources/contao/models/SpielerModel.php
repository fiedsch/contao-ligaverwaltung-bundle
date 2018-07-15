<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Contao;

use \Fiedsch\LigaverwaltungBundle\DCAHelper;

class SpielerModel extends Model
{
    const ANONYM_LABEL = '*****';

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_spieler";

    /**
     * Get the full name (lastname, firstname) for a member
     *
     * @param MemberModel $member
     * @return string
     */
    public static function getFullNameFor(MemberModel $member = null)
    {
        if ($member) {
            return DCAHelper::makeSpielerName($member);
        } else {
            return "Kein Member";
        }
    }

    /**
     * @param int $id
     * @ return string
     * @return string
     */
    public static function getNameById($id) {
        $spieler = self::findById($id);
        if ($spieler) {
            $member = $spieler->getRelated('member_id');
            return self::getFullNameFor($member);
        }
        return "kein Name für Spieler " . $id;
    }

    /**
     * @return string
     */
    public function getName() {
        $member = $this->getRelated('member_id');
        return self::getFullNameFor($member);
    }

    /**
     * @return string
     */
    public function getFullName() {
        $member = $this->getRelated('member_id');
        $membername = self::getFullNameFor($member);

        $mannschaft = $this->getRelated('pid');
        if ($mannschaft) {
            $mannschaftsname = $mannschaft->name;
            $liga = $mannschaft->getRelated('liga');
            if ($liga) {
                $mannschaftsname .= ' '. $liga->name;
                $saison = $liga->getRelated('saison');
                if ($saison) {
                    $mannschaftsname .= ', ' . $saison->name;
                }
            }
        } else {
            $mannschaftsname = "Mannschaft ex. nicht (mehr)";
        }
        return $membername . ', ' . $mannschaftsname;
    }

    /**
     * @return string
     */
    public function getNameAndMannschaft()
    {
        $result = self::getName();
        $mannschaft = $this->getRelated('pid');
        if ($mannschaft) {
            $result .= ', ' . $mannschaft->name;
        }
        return $result;
    }
    /**
     * @return string
     */
    public function getTcDetails() {
        $member = $this->getRelated('member_id');

        $kontaktdaten = [];
        if ($member->mobile) {
            $kontaktdaten[] = sprintf("<a href='tel:%s'>%s</a>",
                $member->mobile,
                $member->mobile
            );
        }
        if ($member->email) {
            $kontaktdaten[] = sprintf("<a href='%s'>%s</a>",
                StringUtil::encodeEmail('mailto:'.$member->email),
                StringUtil::encodeEmail($member->email)
            );
        }
        $kontaktdaten = join(', ', $kontaktdaten);

        return sprintf("%s%s %s%s%s",
            $this->teamcaptain ? 'TC' : '',
            $this->co_teamcaptain ? 'Co-TC' : '',
            self::getFullNameFor($member),
            $kontaktdaten ? ', ' : '',
            $kontaktdaten
            );
    }

}