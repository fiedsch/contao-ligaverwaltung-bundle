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

namespace Fiedsch\LigaverwaltungBundle\Element;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Fiedsch\LigaverwaltungBundle\Trait\TlModeTrait;
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

            $objTemplate->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['teamsandplayersoverview'][0])->upper().' ###';
            $objTemplate->id = $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    public function compile(): void
    {
        $verband = $this->verband;
        $saison = StringUtil::deserialize($this->saison);

        $templateResult = [];

        /** @var Connection $connection */
        $connection = System::getContainer()->get('database_connection');

        $query = <<<EOF
SELECT
    m.name mname, m.id mid, l.name lname, l.spielstaerke lspielstaerke, s.name sname
FROM
    tl_mannschaft m
LEFT JOIN tl_liga l ON (m.liga=l.id)
LEFT JOIN tl_saison s ON (l.saison=s.id)
WHERE l.pid=? AND s.id IN(?)
ORDER BY sname ASC, lspielstaerke ASC, lname ASC, mname ASC
EOF;

        $dbResult = $connection->executeQuery($query,
            [$verband, $saison],
            [ParameterType::INTEGER, Connection::PARAM_INT_ARRAY]
        );

        $result = $dbResult->fetchAllAssociative();

        // dd($result);

        $aggregated = [];

        $statement = $connection->prepare('SELECT COUNT(*) n FROM tl_spieler s LEFT JOIN tl_mannschaft m on s.pid = m.id WHERE m.id=?');

        foreach ($result as $record) {
            $ligaKey = sprintf('%s %s', $record['lname'], $record['sname']);
            if (!isset($aggregated[$ligaKey])) {
                $aggregated[$ligaKey] = [
                    'mannschaften' => 0,
                    'spieler' => 0
                ];
            }
            ++$aggregated[$ligaKey]['mannschaften'];
            $dbResult = $statement->executeQuery([$record['mid']]);
            $aggregated[$ligaKey]['spieler'] += $dbResult->fetchOne();
        }

        $this->Template->verbandId = $verband;
        $this->Template->result = $aggregated;
        $this->Template->gesamt = array_reduce($aggregated, function($carry, $item) {
            return [
                'mannschaften' => $carry['mannschaften'] + $item['mannschaften'],
                'spieler' => $carry['spieler'] + $item['spieler']
            ];
            }, ['mannschaften' => 0, 'spieler' =>0 ]);
    }
}
