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

use Contao\MemberModel;
use Doctrine\DBAL\Connection;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;

class LigaVerwaltungHelper
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * Darf ein angemeldetes Mitglied die Begegnung mit der angegebenen ID bearbeiten?
     * TODO: ja, wenn es in der Heim- oder der Gastmannschaft der Begegnung spielt
     */
    public function memberCanEdit(?MemberModel $member, int $begegnungId): bool
    {
        if (null === $member) {
            return false;
        }

        return true; // TODO anhand von $member und $begegnungId
    }

    /**
     * Mannschaften, in denen ein Mitglied (MemberModel) in den angegebenen Saisons spielt
     *
     * @param MemberModel $member
     * @param array<int> $saisons
     * @return array<MannschaftModel>
     * @throws Exception
     */
    public function getRelevantMannschaften(MemberModel $member, array $saisons): array
    {
        // Aktive Spieler zum angemeldeten FrontendUser (Member)
        $spielerCollection = SpielerModel::findBy(['tl_spieler.member_id=?', 'tl_spieler.active=?'], [$member->id, '1'], ['eager'=>false,'order'=>'pid DESC']);

        // Ligen zu den im Inhaltselement konfigurierten Saisons
        $ligenCollection = LigaModel::findBy('aktiv',true, [
            'eager' => true,
            'having' => sprintf('saison IN (%s)', implode(',', $saisons))
        ]);
        $ligenIds = $ligenCollection->fetchEach('id');

        // Mannschaften aus den $ligenIds, in denen einer der zum Member gehörenden Spieler ($spielerCollection) spielt
        $mannschaftsliste = [];
        foreach ($spielerCollection as $spielerModel) {
            $mannschaftsliste[] = $spielerModel->getRelated('pid');
        }
        // Reduzieren auf die konfigurierten Saisons und aktive Mannschaften
        $mannschaftsliste = array_filter($mannschaftsliste,
            fn($el) => in_array($el->liga, $ligenIds) && $el->active
        );

        return $mannschaftsliste;
    }



}
