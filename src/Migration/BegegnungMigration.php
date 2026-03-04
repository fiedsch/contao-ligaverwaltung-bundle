<?php

namespace Fiedsch\Ligaverwaltung\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;

class BegegnungMigration extends AbstractMigration
{

    public function __construct(private Connection $connection)
    {
    }

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
