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

    const KEIN_AUFSTELLER = 'kein Auftseller';
    const KEIN_WIRT       = 'kein Spielort';

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
            'wirte'            => [],
            'wirteModels'      => [],
            'aufsteller'       => [],
            'aufstellerModels' => [],
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
                    $wirt = $mannschaft->getRelated('spielort');
                    $keyWirt = $wirt ? $wirt->name : self::KEIN_WIRT;
                    $aufsteller = $mannschaft->getRelated('spielort')->getRelated('aufsteller');
                    $keyAufsteller = $aufsteller ? $aufsteller->name : self::KEIN_AUFSTELLER;

                    if (!isset($data['wirte'][$keyWirt])) {
                        $data['wirte'][$keyWirt] = [];
                    }
                    if (!isset($data['aufsteller'][$keyAufsteller])) {
                        $data['aufsteller'][$keyAufsteller] = [];
                    }
                    $mannschaftsbezeichnung = sprintf("%s, %s",
                        $mannschaft->name,
                        $liga->name
                    );
                    $data['wirte'][$keyWirt][] = $mannschaftsbezeichnung;
                    $data['wirteModels'][$keyWirt] = $wirt;
                    $data['aufsteller'][$keyAufsteller][] = $mannschaftsbezeichnung.", $spielort";
                    $data['aufstellerModels'][$keyAufsteller] = $aufsteller;

                    $output->writeln(sprintf("\n* %s (%s, %s)\n",
                        $mannschaft->name,
                        $keyWirt,
                        $keyAufsteller
                    ));
                }
            } else {
                $output->writeln("keine Mannschaften in der Liga '" . $liga->name . "'\n");
            }
        }

        $output->writeln("## Wirte\n");
        foreach($data['wirteModels'] as $keyWirt => $wirtModel) {
            $output->writeln("### $keyWirt\n");

            $output->writeln(sprintf("\n%s  \n%s %s\n", // two spaces before \n for al linebreak
                $wirtModel->street,
                $wirtModel->postal,
                $wirtModel->city
            ));

            foreach($data['wirte'][$keyWirt] as $who) {
                $output->writeln("* $who\n");
            }
        }

        $output->writeln("## Aufsteller\n");

        foreach($data['aufstellerModels'] as $keyAufsteller =>$aufstellerModel) {
            $output->writeln("### $keyAufsteller");

            if ($keyAufsteller !== self::KEIN_AUFSTELLER) {
                $output->writeln(sprintf("\n%s  \n%s %s\n",
                    $aufstellerModel->street,
                    $aufstellerModel->postal,
                    $aufstellerModel->city
                ));
            }

            foreach($data['aufsteller'][$keyAufsteller] as $who) {
                $output->writeln("* $who\n");
            }
        }

        return 1;
    }
}
