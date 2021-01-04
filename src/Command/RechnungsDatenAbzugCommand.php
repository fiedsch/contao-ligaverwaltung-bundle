<?php

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2020 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Command;

use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Fiedsch\LigaverwaltungBundle\Model\SpielortModel;
use Fiedsch\LigaverwaltungBundle\Model\LigaModel;
use Fiedsch\LigaverwaltungBundle\Model\MannschaftModel;
use Fiedsch\LigaverwaltungBundle\Model\SaisonModel;
use Fiedsch\LigaverwaltungBundle\Model\AufstellerModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use RuntimeException;
use Twig\Environment;

/**
 * Abzug und "Aufbereitung" von Daten, die für die Erstellung von
 * Rechnungen benötigt werden: Informationen über Spieler/Mannschaften/
 * Spielorte/Aufsteller und deren Kombinationen.
 *
 * @author Andreas Fieger <https://github.com/fiedsch>
 */
class RechnungsDatenAbzugCommand extends Command implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    const KEIN_AUFSTELLER = -1;

    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        parent::__construct();
        $this->twig = $twig;
        $this->twig->setCache(false);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('fiedsch:rechnungsdaten')
            ->setDescription('Datenabzug für die Rechnungsstellung.')
            ->addArgument('saison', InputArgument::REQUIRED, 'Saison');
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Contao "booten"
        /** @noinspection PhpDeprecationInspection */
        $this->getFramework()->initialize();

        $saisonParameter = $input->getArgument('saison');
        $saison = SaisonModel::findBy('name', $saisonParameter);
        if (null === $saison) {
            $output->writeln("Saison '$saisonParameter' nicht gefunden!");

            return 1;
        }

        // Ligen dieser Saison

        $ligen = LigaModel::findBy(['saison=?'], [$saison->id]);

        // Ergebnisdaten zum Erzeugen der Rechnungen für Wirte (Spielort) und Aufsteller

        $data = [
            'spielorte'  => [],
            'aufsteller' => [],
        ];

        /** @var LigaModel $liga */
        foreach ($ligen as $liga) {

            $mannschaften = MannschaftModel::findBy(
                ['liga=?'],
                [$liga->id]
            );
            if (!$mannschaften) {
                continue;
            }

            /** @var MannschaftModel $mannschaft */
            foreach ($mannschaften as $mannschaft) {

                /** @var SpielortModel $spielort */
                $spielort = $mannschaft->getRelated('spielort');
                if (!$spielort) {
                    $output->writeln(sprintf("Mannschaft %s ohne Spielort", $mannschaft->getFullName()));
                    continue;
                }

                /** @var AufstellerModel $aufsteller */
                $aufsteller = $mannschaft->getRelated('spielort')->getRelated('aufsteller');
                $keyAufsteller = $aufsteller ? $aufsteller->id : self::KEIN_AUFSTELLER;

                // Initialisierung

                if (!isset($data['spielorte'][$spielort->id])) {
                    $data['spielorte'][$spielort->id] = [
                        'id'                        => $spielort->id,
                        'name'                      => $spielort->name,
                        'street'                    => $spielort->street,
                        'postal'                    => $spielort->postal,
                        'city'                      => $spielort->city,
                        'is_aufsteller'             => $keyAufsteller === self::KEIN_AUFSTELLER,
                        'summe_rechnung'            => 0,
                        'summe_rechnung_aufsteller' => 0, // Rechnungssumme, falls der Spielort sein eigener Aufsteller ist
                        'mannschaften'              => [],
                    ];
                }

                if (!isset($data['aufsteller'][$keyAufsteller])) {
                    $data['aufsteller'][$keyAufsteller] = [
                        'id'             => $keyAufsteller,
                        'name'           => $aufsteller ? $aufsteller->name : '-',
                        'street'         => $aufsteller ? $aufsteller->street : '-',
                        'postal'         => $aufsteller ? $aufsteller->postal : '-',
                        'city'           => $aufsteller ? $aufsteller->city : '-',
                        'summe_rechnung' => 0,
                        'mannschaften'   => [],
                    ];
                }

                // Daten für die Rechnung für den Spielort
                $data['spielorte'][$spielort->id]['summe_rechnung'] += $this->toFloat($liga->rechnungsbetrag_spielort);
                // Wenn es keinen Aufsteller zum Spielort gibt, der also sein eigener Aufsteller ist:
                if ($data['spielorte'][$spielort->id]['is_aufsteller']) {
                    $data['spielorte'][$spielort->id]['summe_rechnung_aufsteller'] += $this->toFloat($liga->rechnungsbetrag_aufsteller);
                }
                $data['spielorte'][$spielort->id]['mannschaften'][] = [
                    'name'                       => $mannschaft->name,
                    'liga'                       => $liga->name,
                    'spielstaerke'               => $liga->spielstaerke,
                    'saison'                     => $saison->name,
                    'rechnungsbetrag_spielort'   => $liga->rechnungsbetrag_spielort,
                    'rechnungsbetrag_aufsteller' => $liga->rechnungsbetrag_aufsteller,
                ];

                // Daten für die Rechnung für den Aufsteller
                $data['aufsteller'][$keyAufsteller]['summe_rechnung'] += $this->toFloat($liga->rechnungsbetrag_aufsteller);
                $data['aufsteller'][$keyAufsteller]['mannschaften'][] = [
                    'name'                       => $mannschaft->name,
                    'liga'                       => $liga->name,
                    'spielstaerke'               => $liga->spielstaerke,
                    'saison'                     => $saison->name,
                    'spielort'                   => $spielort->name,
                    'rechnungsbetrag_spielort'   => $liga->rechnungsbetrag_spielort,
                    'rechnungsbetrag_aufsteller' => $liga->rechnungsbetrag_aufsteller,
                ];
            }
        }

        // Mannschaftslisten sortieren, damit sie auf den Rechnungsn in nachvollziehbarer Reihenfolge erscheinen
        foreach (array_keys($data['spielorte']) as $id) {
            $this->sortMannschaften($data['spielorte'][$id]['mannschaften'], 'spielort');
        }
        foreach (array_keys($data['aufsteller']) as $keyAufsteller) {
            $this->sortMannschaften($data['aufsteller'][$keyAufsteller]['mannschaften'], 'aufsteller');
        }

        // Die Daten entfernen, die bei "kein Aufsteller" unter self::KEIN_AUFSTELLER summiert wurden,
        // aber beim Spielort abgerechnet werden
        unset($data['aufsteller'][self::KEIN_AUFSTELLER]);

        // Daten ausgeben
        $output->writeln($this->twig->render(
            '@FiedschLigaverwaltung/rechnungsdaten/rechnungsdaten.html.twig',
            [
                'saison'     => $saison->name,
                'spielorte'  => $data['spielorte'],
                'aufsteller' => $data['aufsteller'],
            ]
        ));

        return 0;
    }

    protected function toFloat(string $value): float
    {
        if ('' === $value) {
            return 0;
        }

        return (float)str_replace(',', '.', $value);
    }

    protected function sortMannschaften(array &$data, $type = 'spielort'): void
    {
        switch ($type) {
            case 'spielort':
                usort($data, function($a, $b) {
                    // nach Liga
                    return strnatcmp($a['spielstaerke'], $b['spielstaerke']);
                });
                break;
            case 'aufsteller';
                usort($data, function($a, $b) {
                    // nach Spielort und innerhalb eines Spielorts nach Liga ('spielstaerke')
                    if ($a['spielort'] === $b['spielort']) {
                        return $a['spielstaerke'] <=> $b['spielstaerke'];
                    }
                    return strnatcmp($a['spielort'], $b['spielort']);
                });
                break;
            default:
                throw new RuntimeException("ungültiger Sortiertyp $type");
        }
    }

}
