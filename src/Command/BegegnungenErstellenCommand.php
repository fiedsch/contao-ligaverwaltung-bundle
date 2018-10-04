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
use Contao\Database;
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
    const SPIELFREI_MANNSCHAFT = 0;

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

        $countNew = $this->generateBegegnungen($liga->id, $output);

        $output->writeln(sprintf("Fertig: %d Begegnungen wurden neu erstellt", $countNew));

        return 1;
    }

    /**
     * @param integer $ligaId
     * @param OutputInterface $output
     * @return integer Anzahl der erstellten Begegnungen
     */
    protected function generateBegegnungen($ligaId, OutputInterface $output)
    {
        $mannschaft = MannschaftModel::findByLiga($ligaId);

        $mannschaftIds = [];

        $countGenerated = 0;

        while ($mannschaft->next()) {
            $output->writeln(sprintf("-> Mannschaft %s (%d)",$mannschaft->name, $mannschaft->id));
            $mannschaftIds[] = $mannschaft->id;
        }

        // Alle Begegnungen (jeder gegen jeden) Hin und Rückspiel erstellen

        foreach ($mannschaftIds as $idHome) {
            foreach ($mannschaftIds as $idAway) {
                if ($idHome === $idAway) { continue; }
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
                    $countGenerated++;
                }
            }
        }

        // Bei ungerader Anzahl von Mannschaften hat je Spieltag immer eine Mannschaft
        // Spielfrei. Diese Begegnungenn nun auch anlegen (Hin und Rückrunde jeweils
        // als Heimspiel).

        // Ungerade Anzahl von Mannschaften? Dann hat jede ein Mal Spielfrei!
        if (count($mannschaftIds) % 2) {
            foreach ($mannschaftIds as $idHome) {
                $begegnung = BegegnungModel::findBy(
                    ['pid=?', 'home=?', 'away=?'],
                    [$ligaId, $idHome, self::SPIELFREI_MANNSCHAFT]
                );
                if ($begegnung) {
                    $output->writeln(sprintf("-> Begegnung '%s:%s' existiert bereits", $idHome, self::SPIELFREI_MANNSCHAFT));
                } else {
                    $output->writeln(sprintf("->lege 2 x Spielfrei an '%s:%s' (Hin- und Rückrunde)", $idHome, self::SPIELFREI_MANNSCHAFT));
                    for ($i=0; $i<2; $i++) {
                        $begegnung = new BegegnungModel();
                        $begegnung->tstamp = time();
                        $begegnung->pid = $ligaId;
                        $begegnung->home = $idHome;
                        $begegnung->away = self::SPIELFREI_MANNSCHAFT;
                        $begegnung->spiel_tag = self::DUMMY_SPIELTAG; // ein Marker, der bei der Spielplanerstellung manuell geändert werden muss.
                        $begegnung->save();
                        $countGenerated++;
                    }
                }
            }

            }

        return $countGenerated;
    }

}