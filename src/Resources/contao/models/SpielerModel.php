<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Contao;

/**
 * @property integer $id
 * @property integer $pid
 * @property string $name
 * @property boolean teamcaptain
 * @property boolean co_teamcaptain
 * @method static SpielerModel|null findById($id, array $opt=array())
 * @method static Model\Collection|SpielerModel[]|SpielerModel|null findByPid($id, array $opt=array())
 */

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
     * @param integer $id
     * @return string
     */
    public static function getNameById($id)
    {
        $spieler = self::findById($id);
        if ($spieler) {
            $member = $spieler->getRelated('member_id');
            return self::getFullNameFor($member);
        }
        return "kein Name für Spieler " . $id;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getName()
    {
        /** @var \Contao\MemberModel $member */
        $member = $this->getRelated('member_id');
        return self::getFullNameFor($member);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getFullName()
    {
        /** @var \Contao\MemberModel $member */
        $member = $this->getRelated('member_id');
        $membername = self::getFullNameFor($member);

        /** @var MannschaftModel $mannschaft */
        $mannschaft = $this->getRelated('pid');
        if ($mannschaft) {
            $mannschaftsname = $mannschaft->name;
            /** @var \Contao\LigaModel $liga */
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
     * @throws \Exception
     */
    public function getNameAndMannschaft()
    {
        $result = self::getName();
        /** @var MannschaftModel $mannschaft */
        $mannschaft = $this->getRelated('pid');
        if ($mannschaft) {
            $result .= ', ' . $mannschaft->name;
        }
        return $result;
    }
    /**
     * @return string
     * @throws \Exception
     */
    public function getTcDetails()
    {
        /** @var MemberModel $member */
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
