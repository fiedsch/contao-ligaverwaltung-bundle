<?php

declare(strict_types=1);

namespace Fiedsch\LigaverwaltungBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class PatchSaisonMigration extends AbstractMigration
{

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_saison'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_saison');

        return isset($columns['alias']) && $this->getNumberOfEmptyAliases() > 0;

    }

    protected function getNumberOfEmptyAliases(): int
    {
        $result = $this->connection->executeQuery("SELECT COUNT(1) n FROM tl_saison WHERE alias IS NULL");

        return $result->fetchOne();
    }


    public function run(): MigrationResult
    {
        $stmt = $this->connection->prepare("UPDATE tl_saison SET alias=name WHERE alias IS NULL");

        $rowCount = $stmt->executeStatement();

        return $this->createResult(
            true,
            'Updated '. $rowCount .' tl_saison.alias entries.'
        );
    }

}
