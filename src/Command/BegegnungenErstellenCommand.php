<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Fiedsch\LigaverwaltungBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Contao\LigaModel;
use Contao\SaisonModel;
use Contao\MannschaftModel;
use Contao\BegegnungModel;


/**
 * Test being a command.
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class BegegnungenErstellenCommand extends ContainerAwareCommand
{

    const DUMMY_SPIELTAG = 999;
    const SPIELFREI_MANNSCHAFT = 999;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fiedsch:begegnungenerstellen')
            ->setDescription('Begegnungen für eine Liga erstellen.')
            ->addArgument('liga', InputArgument::REQUIRED, 'Liga-ID')
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


        $ligaParameter = $input->getArgument('liga');
        $liga = LigaModel::findBy('id', $ligaParameter);
        if (null === $liga) {
            $output->writeln("Liga '$ligaParameter' nicht gefunden!");
            return 0;
        }

        $saison = SaisonModel::findBy('id', $liga->saison);

        $output->writeln("Erstelle Begegnungen für " . $liga->name . ", " . $saison->name);

        $this->generateBegegnungen($liga->id, $output);

        $output->writeln("Fertig: Begegnungen sind erstellt");

        return 1;
    }

    /**
     * @param integer $ligaId
     * @param OutputInterface $output
     */
    protected function generateBegegnungen($ligaId, OutputInterface $output)
    {
        $mannschaft = MannschaftModel::findByLiga($ligaId);

        $mannschaftIds = [];

        while ($mannschaft->next()) {
            $output->writeln("-> Mannschaft " . $mannschaft->name);
            $mannschaftIds[] = $mannschaft->id;
        }

        // Ungerade Anzahl von Mannschaften? Dann hat jede ein Mal Spielfrei!
        if (count($mannschaftIds) % 2) {
            $mannschaftIds[] = self::SPIELFREI_MANNSCHAFT;
        }

        // Alle Begegnungen (jeder gegen jeden) erstellen

        foreach ($mannschaftIds as $idHome) {
            foreach ($mannschaftIds as $idAway) {
                if ($idHome === $idAway) { continue; }
                if ($idHome === self::SPIELFREI_MANNSCHAFT) { continue; }
                $begegnung = BegegnungModel::findBy(
                        ['pid=?', 'home=?', 'away=?'],
                        [$ligaId, $idHome, $idAway]
                );
                if ($begegnung) {
                    $output->writeln(sprintf("-> Begegnung '%s:%s' existiert bereits", $idHome, $idAway));
                } else {
                    $output->writeln(sprintf("->lege Begegnung '%s:%s' an", $idHome, $idAway));
                    $begegnung = new BegegnungModel();
                    $begegnung->tstamp = time();
                    $begegnung->pid = $ligaId;
                    $begegnung->home = $idHome;
                    $begegnung->away = $idAway;
                    $begegnung->spiel_tag = self::DUMMY_SPIELTAG; // ein Marker, der bei der Spielplanerstellung manuell geändert werden muss.
                    $begegnung->save();
                }
            }
        }

    }
}
