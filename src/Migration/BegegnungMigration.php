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

namespace Fiedsch\Ligaverwaltung\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Schema\Column;

class BegegnungMigration extends AbstractMigration
{

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws DBALException
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_begegnung'])) {
            return false;
        }

        $availableColumns = $schemaManager->introspectTableColumnsByUnquotedName('tl_begegnung');
        $availableColumns = array_map(fn(Column $col): string => $col->getObjectName()->getIdentifier()->getValue(), $availableColumns);
        if (!in_array('erfasst', $availableColumns)) {
            return false;
        }
        $dbResult = $this->connection->executeQuery("SELECT COUNT(*) FROM tl_begegnung WHERE (erfasst IS NULL OR erfasst<>1) AND LENGTH(begegnung_data)>0");

        return $dbResult->fetchOne() > 0;
    }

    /**
     * @throws DBALException
     */
    public function run(): MigrationResult
    {
        $dbResult = $this->connection->executeQuery("UPDATE `tl_begegnung` SET erfasst=0 WHERE LENGTH(begegnung_data) = 0");
        $rowCount = $dbResult->rowCount();

        $dbResult = $this->connection->executeQuery("UPDATE `tl_begegnung` SET erfasst=1 WHERE LENGTH(begegnung_data) > 0");
        $rowCount += $dbResult->rowCount();
        return $this->createResult(
            true,
            'Updated '. $rowCount . ' begegnung records.'
        );
    }
}
