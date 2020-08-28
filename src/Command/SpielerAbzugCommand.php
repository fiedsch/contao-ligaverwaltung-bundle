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

namespace Fiedsch\LigaverwaltungBundle\Command;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\LigaModel;
use Contao\MannschaftModel;
use Contao\SaisonModel;
use Contao\SpielerModel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    protected function configure()
    {
        $this
            ->setName('fiedsch:spielerliste')
            ->setDescription('Datenabzug Spielerliste einer Saison.')
            ->addArgument('saison', InputArgument::REQUIRED, 'Saison')
             ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Contao "booten"
        $framework = $this->getContainer()->get('contao.framework');
        $framework->initialize();

        $saisonParameter = $input->getArgument('saison');
        $saison = SaisonModel::findBy('name', $saisonParameter);
        if (null === $saison) {
            $output->writeln("Saison '$saisonParameter' nicht gefunden!");

            return 0;
        }

        // Ligen dieser Saison

        $ligen = LigaModel::findBy(['saison=?'], [$saison->id]);

        // Kopfzeile

        printf("%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
            'Nachname',
            'Vorname',
            'Spielerpass',
            'Mannschaft',
            'Liga',
            'Saison',
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
                        /** @var \MemberModel $member */
                        $member = $s->getRelated('member_id');

                        printf("%s\t%s\t%s\t%s\t%s\t%s\n",
                            html_entity_decode($member->lastname),
                            html_entity_decode($member->firstname),
                            $member->passnummer,
                            html_entity_decode($mannschaft->name),
                            html_entity_decode($liga->name),
                            html_entity_decode($saison->name),
                            '' // Platzhalter für "bezahlt" Spalte
                        );
                    }
                }
            } else {
                $output->writeln("keine Mannschaften in der Liga '".$liga->name."'\n");
            }
        }


        return 1;
    }
}
