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

namespace Fiedsch\Ligaverwaltung\Command;

use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Fiedsch\Ligaverwaltung\Helper\DataEntrySaver;
use Fiedsch\Ligaverwaltung\Model\BegegnungModel;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\SaisonModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Exception;
use function json_encode;


/**
 * Fixen von Daten (in der aktuellen Implementierung: neu Abspeichern der tl_highlight. Erstellen einer Liste aller
 * Spieler (inkl. Name etc. aus zugehörigem tl_member) für die Spieler (tl_spieler) aller Mannschaften (tl_mannschaft)
 * einer Saison (tl_saison).
 *
 * TODO: Sollte das nicht eher eine Migarion sein/werden? Oder ist es mittlerweile obsolete?
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
            ->setName('fiedsch:ligaverwaltung:fixdata')
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
        $this->framework->initialize();

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
                    json_encode($highlights)
                );

                DataEntrySaver::handleHighlights($begegnung->id, ['highlights' => $highlights]);
            }
        }

        return 0;
    }
}
