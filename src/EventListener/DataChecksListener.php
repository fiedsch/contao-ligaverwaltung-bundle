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

namespace Fiedsch\Ligaverwaltung\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Fiedsch\Ligaverwaltung\Model\LigaModel;

#[AsHook('getSystemMessages')]
class DataChecksListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Prüfung von Beziehungen zwischen Datenbanktabellen, die über Contao DCA Definitionen angelegt wurden,
     * und dort keine Relationen (parent/child) haben, dennoch aber in Beziehung stehen.
     * Beispiele:
     * - Mannschaft spielt in Liga (ist aber kein Child von Liga; das wäre die Verband -> Liga -> Begegnung -> Spiel Hierarchie)
     * - Mannschaft spielt an einem Spielort (ist aber kein Child von Spielort; s.o.)
     *
     * @throws DBALException
     */
    public function __invoke(): string|null
    {
        $result = null;

        $check = $this->checkMannschaftAndLiga();
        if (null !== $check) {
            $result = [$check];
        }

        $check = $this->checkSpielortAndMannschaft();
        if (null !== $check) {
            $result = [...$result ?? [], $check];
        }

        $check = $this->checkBegegnungen();
        if (null !== $check) {
            $result = [...$result ?? [], $check];
        }

        if (is_array($result)) {
            return implode('', $result);
        }

        return $result;

    }

    /**
     * Mannschaften ohne existierende zugeordnete Liga
     *
     * @throws DBALException
     */
    protected function checkMannschaftAndLiga(): string|null
    {
        $dbResult = $this->connection->executeQuery('SELECT * FROM tl_mannschaft WHERE liga NOT IN (SELECT id FROM tl_liga)');
        $numRecords = $dbResult->rowCount();

        if (0 === $numRecords) {
            return null;
        }

        $message = sprintf('Es gibt %d Mannschaften, die keiner (existierenden) Liga zugeordnet sind', $numRecords);
        $result = '<p class="tl_error">'.$message.'</p>';
        $result .= '<ul>';
        foreach ($dbResult->fetchAllAssociative() as $mannschaft) {
            $result .= sprintf('<li>%s (ID der Liga: %d)</li>', $mannschaft['name'], $mannschaft['liga']);
        }
        $result .= '</ul>';

        return $result;
    }

    /**
     * Spielorte, die keiner Mannschaft zugeordnet sind.
     *
     * @throws DBALException
     */
    protected function checkSpielortAndMannschaft(): string|null
    {
        $result = '';

        // Aktive Spielorte, die keiner Mannschaft zugeordnet sind
        $dbResult = $this->connection->executeQuery('SELECT * FROM tl_spielort WHERE id NOT IN (SELECT DISTINCT spielort FROM tl_mannschaft) and aktiv=1');
        $numRecords = $dbResult->rowCount();

        if ($numRecords > 0) {
            $message = sprintf('Es gibt %d <strong>aktive</strong> Spielorte, die <strong>keiner Mannschaft</strong> zugeordnet sind', $numRecords);
            $result .= '<p class="tl_info">' . $message . '</p>';
            $result .= '<ul>';
            foreach ($dbResult->fetchAllAssociative() as $spielort) {
                $result .= sprintf('<li>%s, %s</li>', $spielort['name'], $spielort['city']);
            }
            $result .= '</ul>';
        }


        // Nicht-aktive Spielorte, die (mittlerweile wieder) einer Mannschaft zugeordnet sind
        $dbResult = $this->connection->executeQuery('SELECT * FROM tl_spielort WHERE id IN (SELECT DISTINCT spielort FROM tl_mannschaft) and aktiv=0');
        $numRecords = $dbResult->rowCount();

        if ($numRecords > 0) {
            $message = sprintf('Es gibt %d <strong>nicht aktive</strong> Spielorte, die <strong>Mannschaften</strong> zugeordnet sind', $numRecords);
            $result .= '<p class="tl_info">' . $message . '</p>';
            $result .= '<ul>';
            foreach ($dbResult->fetchAllAssociative() as $spielort) {
                $result .= sprintf('<li>%s, %s</li>', $spielort['name'], $spielort['city']);
            }
            $result .= '</ul>';
        }

        if ('' !== $result) {
            $result .= '<p class="tl_info">Die obige Zuordnung der Spielorte beinflusst die Funktion der Ligaverwaltung nicht, erzeugt aber u.U. unsinnige Einträge auf einer ggf. vorhandenen "Übersicht der Spielorte":</p>';
        }

        return $result === '' ? null : $result;
    }

    /**
     * Verwaiste Begegnungen
     * @throws DBALException
     */
    protected function checkBegegnungen(): string|null
    {
        $sql = <<<EOF
SELECT COUNT(*) n FROM tl_begegnung b
  LEFT JOIN tl_mannschaft mh ON (b.home=mh.id)
  LEFT JOIN tl_mannschaft ma ON (b.away=ma.id)
  WHERE
      b.away > 0 -- kein "Spielfrei"
      AND (mh.id IS NULL OR ma.id IS NULL)
  ;
EOF;

        $dbResult = $this->connection->executeQuery($sql);
        $numRecords = $dbResult->fetchOne();

        if (0 === $numRecords) {
            return null;
        }

        // TO-DOs für den Admin beim Löschen in der Datenbank: siehe doc/cleanup.md

        return sprintf('<p class="tl_error">Es gibt %d Begegnungen ohne zugehörige (noch existierende) Heim- oder Auswärtsmannschaft (Datenbereinigung durch Administrator)</p>', $numRecords);
    }

}
