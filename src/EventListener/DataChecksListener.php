<?php

namespace Fiedsch\Ligaverwaltung\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Doctrine\DBAL\Connection;

#[AsHook('getSystemMessages')]
class DataChecksListener
{
    public function __construct(private Connection $connection)
    {
    }
    public function __invoke(): string|null
    {
        // Mannschaften ohne existierende zugeordnete Liga
        $statement = $this->connection->prepare('SELECT * FROM tl_mannschaft WHERE liga NOT IN (SELECT id FROM tl_liga)');
        $numRecords = $statement->executeQuery()->rowCount();


        if (0 === $numRecords) {
            return null;
        }

        $message = sprintf('Es gibt %d Mannschaften, die keiner (existierenden) Liga zugeordnet sind', $numRecords);
        return '<p class="tl_error">'.$message.'</p>';
    }

}
