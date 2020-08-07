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
 * @property int $id
 * @property int $pid
 * @property string $name
 * @property bool $teamcaptain
 * @property bool $co_teamcaptain
 * @property bool $active
 * @property bool $ersatzspieler
 * @property bool $jugendlich
 *
 * @method static SpielerModel|null findById($id, array $opt = [])
 * @method static Model\Collection|SpielerModel|null findByPid($id, array $opt = [])
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
     * @param int $id
     *
     * @return string
     * @throws \Exception
     *
     */
    public static function getNameById($id)
    {
        $spieler = self::findById($id);
        if ($spieler) {
            /** @var MemberModel $member */
            $member = $spieler->getRelated('member_id');

            return self::getFullNameFor($member);
        }

        return 'kein Name für Spieler ' . $id;
    }

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
     * @return string
     * @throws \Exception
     *
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
                $mannschaftsname .= ' ' . $liga->name;
                $saison = $liga->getRelated('saison');
                if ($saison) {
                    $mannschaftsname .= ', ' . $saison->name;
                }
            }
        } else {
            $mannschaftsname = 'Mannschaft ex. nicht (mehr)';
        }

        return $membername . ', ' . $mannschaftsname;
    }

    /**
     * @return string
     * @throws \Exception
     *
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
     *
     */
    public function getName()
    {
        /** @var MemberModel $member */
        $member = $this->getRelated('member_id');

        return self::getFullNameFor($member);
    }

    /**
     * @return array
     * @throws \Exception
     *
     */
    public function getTcDetails(): array
    {
        /** @var \Contao\MemberModel $member */
        $member = $this->getRelated('member_id');

        $kontaktdaten = [
            'name'   => '',
            'email'  => '',
            'mobile' => '',
        ];
        if ($member->mobile) {
            $kontaktdaten['mobile'] = sprintf("<a href='tel:%s'>%s</a>",
                $member->mobile,
                $member->mobile
            );
        }
        if ($member->email) {
            $kontaktdaten['email'] = sprintf("<a href='%s'>%s</a>",
                StringUtil::encodeEmail('mailto:' . $member->email),
                StringUtil::encodeEmail($member->email)
            );
        }

        $kontaktdaten['name'] = sprintf('%s%s %s',
            $this->teamcaptain ? $GLOBALS['TL_LANG']['MSC']['tc1'] : '',
            $this->co_teamcaptain ? $GLOBALS['TL_LANG']['MSC']['tc2'] : '',
            self::getFullNameFor($member)
        );

        return $kontaktdaten;
    }
}
