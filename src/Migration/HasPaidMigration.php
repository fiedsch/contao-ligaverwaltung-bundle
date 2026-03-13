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

class HasPaidMigration extends AbstractMigration
{

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws DBALException
     */
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

    /**
     * @throws DBALException
     */
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
