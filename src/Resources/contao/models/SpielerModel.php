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

namespace Contao;

use Fiedsch\LigaverwaltungBundle\DCAHelper;

/**
 * @property int    $id
 * @property int    $pid
 * @property string $name
 * @property bool   $teamcaptain
 * @property bool   $co_teamcaptain
 * @property bool   $active
 *
 * @method static SpielerModel|null findById($id, array $opt=array())
 * @method static Model\Collection|SpielerModel|null findByPid($id, array $opt=array())
 * @method static Model\Collection|Model|null getRelated($tablename)
 */
class SpielerModel extends Model
{
    const ANONYM_LABEL = '*****';

    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_spieler';

    /**
     * Get the full name (lastname, firstname) for a member.
     *
     * @param MemberModel $member
     *
     * @return string
     */
    public static function getFullNameFor(MemberModel $member = null)
    {
        if ($member) {
            return DCAHelper::makeSpielerName($member);
        }

        return 'Kein Member';
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getNameById($id)
    {
        $spieler = self::findById($id);
        if ($spieler) {
            /** @var MemberModel $member */
            $member = $spieler->getRelated('member_id');

            return self::getFullNameFor($member);
        }

        return 'kein Name für Spieler '.$id;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getName()
    {
        /** @var MemberModel $member */
        $member = $this->getRelated('member_id');

        return self::getFullNameFor($member);
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getFullName()
    {
        /** @var MemberModel $member */
        $member = $this->getRelated('member_id');
        $membername = self::getFullNameFor($member);

        /** @var MannschaftModel $mannschaft */
        $mannschaft = $this->getRelated('pid');
        if ($mannschaft) {
            $mannschaftsname = $mannschaft->name;
            /** @var \Contao\LigaModel $liga */
            $liga = $mannschaft->getRelated('liga');
            if ($liga) {
                $mannschaftsname .= ' '.$liga->name;
                $saison = $liga->getRelated('saison');
                if ($saison) {
                    $mannschaftsname .= ', '.$saison->name;
                }
            }
        } else {
            $mannschaftsname = 'Mannschaft ex. nicht (mehr)';
        }

        return $membername.', '.$mannschaftsname;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getNameAndMannschaft()
    {
        $result = self::getName();
        /** @var MannschaftModel $mannschaft */
        $mannschaft = $this->getRelated('pid');
        if ($mannschaft) {
            $result .= ', '.$mannschaft->name;
        }

        return $result;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getTcDetails()
    {
        /** @var \Contao\MemberModel $member */
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
        $kontaktdaten = implode(', ', $kontaktdaten);

        return sprintf('%s%s %s%s%s',
            $this->teamcaptain ? $GLOBALS['TL_LANG']['MSC']['tc1'] : '',
            $this->co_teamcaptain ? $GLOBALS['TL_LANG']['MSC']['tc2'] : '',
            self::getFullNameFor($member),
            $kontaktdaten ? ', ' : '',
            $kontaktdaten
            );
    }
}
