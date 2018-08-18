<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Fiedsch\LigaverwaltungBundle\Command;

use Contao\SaisonModel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Contao\MemberModel;
use Contao\AufstellerModel;
use Contao\LigaModel;
use Contao\MannschaftModel;
use Contao\SpielortModel;


/**
 * Test being a command.
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class RechnungsDatenAbzugCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fiedsch:rechnungsdaten')
            ->setDescription('Datenabzug für die Rechnungsstellung.')
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

        $output->writeln("# RechnungsdatenAbzugCommand für Saison '$saisonParameter'\n");

        // Ligen dieser Saison

        $ligen = LigaModel::findBy(['saison=?'], [$saison->id]);

        // Ergebnisdaten

        $data = [
            'wirte'      => [],
            'aufsteller' => [],
        ];

        $output->writeln("## Ligen und Mannschaften\n");

        foreach ($ligen as $liga) {

            $output->writeln(sprintf("### %s\n", $liga->name));

            $mannschaften = \MannschaftModel::findBy(
                ['liga=?'],
                [$liga->id]
            );

            if ($mannschaften) {
                foreach ($mannschaften as $mannschaft) {
                    $spielort = $mannschaft->getRelated('spielort')->name ?: 'kein Spielort';
                    $aufsteller = $mannschaft->getRelated('spielort')->getRelated('aufsteller')->name ?: 'kein Aufsteller';

                    if (!isset($data['wirte'][$spielort])) {
                        $data['wirte'][$spielort] = [];
                    }
                    if (!isset($data['aufsteller'][$aufsteller])) {
                        $data['aufsteller'][$aufsteller] = [];
                    }
                    $mannschaftsbezeichnung = sprintf("%s, %s",
                        $mannschaft->name,
                        $liga->name
                    );
                    $data['wirte'][$spielort][] = $mannschaftsbezeichnung;
                    $data['aufsteller'][$aufsteller][] = $mannschaftsbezeichnung.", $spielort";
                    $output->writeln(sprintf("* %s (%s, %s)\n",
                        $mannschaft->name,
                        $spielort,
                        $aufsteller
                    ));
                }
            } else {
                $output->writeln("keine Mannschaften in der Liga '" . $liga->name . "'\n");
            }
        }

        $output->writeln("## Wirte\n");
        foreach($data['wirte'] as $wirt => $d) {
            $output->writeln("### $wirt\n");
            foreach($d as $who) {
                $output->writeln("* $who\n");
            }
        }

        $output->writeln("## Aufsteller\n");
        foreach($data['aufsteller'] as $aufsteller => $d) {
            $output->writeln("### $aufsteller");
            foreach($d as $who) {
                $output->writeln("* $who\n");
            }
        }

        return 1;
    }
}
