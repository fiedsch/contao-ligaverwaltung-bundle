<?php

declare(strict_types=1);

namespace Fiedsch\LigaverwaltungBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class HasPaidMigration extends AbstractMigration
{

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_spieler'])) {
            return false;
        }

        $spielerColumns = $schemaManager->listTableColumns('tl_spieler');
        $memberColumns = $schemaManager->listTableColumns('tl_member');

        return !isset($spielerColumns['haspaid']) && isset($memberColumns['haspaidcurrentseason']);
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery("
            ALTER TABLE
                tl_spieler
            ADD
                haspaid char(1) NOT NULL default ''
        ");

        $stmt = $this->connection->prepare("
            UPDATE
                tl_spieler
            SET
                haspaid = 1
            WHERE pid IN (SELECT id from tl_member where haspaidcurrentseason=1)
        ");

        $rowCount = $stmt->executeStatement();

        return $this->createResult(
            true,
            'Moved '. $rowCount . ' spieler check boxes.'
        );
    }

}
