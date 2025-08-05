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

namespace Fiedsch\LigaverwaltungBundle\Command;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Date;
use Contao\MemberModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Fiedsch\LigaverwaltungBundle\Model\SpielerModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use function html_entity_decode;

/**
 * Erstellen einer Liste aller Spieler (inkl. Name etc. aus zugehörigem tl_member)
 * für dir Spieler (tl_spieler) aller Mannschaften (tl_mannschaft) einer Saison (tl_saison).
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class SpielerAbzugCommand extends Command implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('fiedsch:spielerliste')
            ->setDescription('Datenabzug Spielerliste einer Saison.')
            ->addArgument('saison', InputArgument::REQUIRED, 'Saison')
             ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Contao "booten"
        $this->getFramework()->initialize();

        $saisonParameter = $input->getArgument('saison');
        $saison = SaisonModel::findBy('name', $saisonParameter);

        if (null === $saison) {
            $output->writeln("Saison '$saisonParameter' nicht gefunden!");

            return 0;
        }

        // Ligen dieser Saison

        $ligen = LigaModel::findBy(['saison=?'], [$saison->id]);

        // Kopfzeile

        printf("%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
            'Nachname',
            'Vorname',
            'Geschlecht',
            'Spielerpass',
            'Mannschaft',
            'Liga',
            'Saison',
            'Geburtsdatum',
            'bezahlt'
        );

        // Ergebnisdaten

        foreach ($ligen as $liga) {
            $mannschaften = MannschaftModel::findBy(
                ['liga=?'],
                [$liga->id]
            );

            if ($mannschaften) {
                foreach ($mannschaften as $mannschaft) {
                    $spieler = SpielerModel::findBy(
                        ['pid=?'],
                        [$mannschaft->id]
                    );

                    foreach ($spieler as $s) {
                        /** @var MemberModel $member */
                        $member = $s->getRelated('member_id');
                        if (null === $member) {
                            continue;
                            printf("%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
                                $s->teamcaptain == '1' ? 'ja' : '',
                                $s->co_teamcaptain == '1' ? 'ja' : '',
                        }

                            html_entity_decode($member->lastname ?? ''),
                            html_entity_decode($member->firstname ?? ''),
                            $member->gender,
                            $member->passnummer,
                            html_entity_decode($mannschaft->name),
                            html_entity_decode($liga->name),
                            html_entity_decode($saison->name),
                            Date::parse('Y-m-d', $member->dateOfBirth),
                            '' // Platzhalter für "bezahlt" Spalte
                        );
                    }
                }
            } else {
                // $output->writeln("keine Mannschaften in der Liga '".$liga->name);
            }
        }

        return 0;
    }
}
