<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2025 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\Ligaverwaltung\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use function Symfony\Component\String\u;

class ContentTeamsAndPlayersOverview extends ContentElement
{
    use TlModeTrait;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'ce_teamsandplayersoverview';


    public function generate(): string
    {
        if ($this->isBackend()) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $headline = $this->headline;

            $objTemplate->wildcard = '### ' . u($GLOBALS['TL_LANG']['CTE']['teamsandplayersoverview'][0])->upper() . ' ###';
            $objTemplate->id = $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    public function compile(): void
    {
        $templateResult = [];

        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');

        $saisons = StringUtil::deserialize($this->saison);
        $aggregated = [];

        foreach ($saisons as $saison) {
            $query = <<<EOF
SELECT
    m.name mname, m.id mid, l.name lname, l.spielstaerke lspielstaerke, s.name sname
FROM
    tl_mannschaft m
LEFT JOIN tl_liga l ON (m.liga=l.id)
LEFT JOIN tl_saison s ON (l.saison=s.id)
WHERE s.id=?
ORDER BY lspielstaerke ASC, lname ASC, mname ASC
EOF;
            $dbResult = $connection->executeQuery($query,
                [$saison],
                [ParameterType::INTEGER]
            );

            $teams = $dbResult->fetchAllAssociative();

            $statement = $connection->prepare('SELECT COUNT(*) n FROM tl_spieler s LEFT JOIN tl_mannschaft m on s.pid = m.id WHERE m.id=?');

            foreach ($teams as $team) {
                $ligaKey = sprintf('%s %s', $team['lname'], $team['sname']);
                if (!isset($aggregated[$ligaKey])) {
                    $aggregated[$ligaKey] = [
                        'mannschaften' => 0,
                        'spieler' => 0
                    ];
                }
                ++$aggregated[$ligaKey]['mannschaften'];
                $statement->bindValue(1, $team['mid'], ParameterType::INTEGER);
                $dbResult = $statement->executeQuery();
                $aggregated[$ligaKey]['spieler'] += $dbResult->fetchOne();
            }
        }

        $this->Template->result = $aggregated;
        $this->Template->gesamt = array_reduce($aggregated, function ($carry, $item) {
            return [
                'mannschaften' => $carry['mannschaften'] + $item['mannschaften'],
                'spieler' => $carry['spieler'] + $item['spieler']
            ];
        }, ['mannschaften' => 0, 'spieler' => 0]);
    }
}
