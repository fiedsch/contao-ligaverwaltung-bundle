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
use Fiedsch\LigaverwaltungBundle\Helper\DataEntrySaver;
use Fiedsch\LigaverwaltungBundle\Model\BegegnungModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use Symfony\Component\Yaml\Yaml;


/**
 * Fixen von Daten (in der aktuellen Implementierung: neu Abspeichern der tl_highlightErstellen einer Liste aller Spieler (inkl. Name etc. aus zugehörigem tl_member)
 * für dir Spieler (tl_spieler) aller Mannschaften (tl_mannschaft) einer Saison (tl_saison).
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class FixDataCommand extends Command implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('fiedsch:fixdata')
            ->setDescription('Datenbereinigung: tl_highlight records für alle Begegnungen neu erstellen.')
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

        // Ligen der angegebenen Saison
        $ligen = LigaModel::findBy(['saison=?'], [$saison->id]);

        foreach ($ligen as $liga) {
            $begegnungen = BegegnungModel::findBy(['pid=?'], [$liga->id], ['order' => 'spiel_tag ASC']);

            /** @var BegegnungModel $begegnung */
            foreach ($begegnungen as $begegnung) {

                if ($begegnung->begegnung_data === '') { continue; }

                $begegnung_data = Yaml::parse($begegnung->begegnung_data);
                $highlights = $begegnung_data['app_data']['highlights'];
                if (empty($highlights)) {
                    printf("Begegnung %s (%s): ohne Highlights\n", $begegnung->id, $begegnung->getLabel());
                }

                printf("Begegnung %s\n\t%s\n\tspeichere '%s'\n",
                    $begegnung->id,
                    $begegnung->getLabel('short'),
                    \json_encode($highlights)
                );

                DataEntrySaver::handleHighlights($begegnung->id, ['highlights' => $highlights]);
            }
        }

        return 0;
    }
}
