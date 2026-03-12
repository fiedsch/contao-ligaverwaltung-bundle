<?php /** @noinspection ALL */

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

namespace Fiedsch\Ligaverwaltung\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\Database;
use Contao\MemberModel;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Config;
use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Exception;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Fiedsch\Ligaverwaltung\Entity\Begegnung;
use Fiedsch\Ligaverwaltung\Entity\Spiel;
use Fiedsch\Ligaverwaltung\Helper\DCAHelper;
use Fiedsch\Ligaverwaltung\Helper\RankingHelperInterface;
use Fiedsch\Ligaverwaltung\Model\LigaModel;
use Fiedsch\Ligaverwaltung\Model\MannschaftModel;
use Fiedsch\Ligaverwaltung\Model\SpielerModel;
use Fiedsch\Ligaverwaltung\Trait\TlModeTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

#[AsContentElement(
    type: 'teamsandplayersoverview',
    category: 'ligaverwaltung',
    template: 'content_element/teamsandplayersoverview'
)]
class TeamsAndPlayersOverviewController extends AbstractContentElementController
{
    public function __construct(private readonly Connection $connection)
    {
    }

    use TlModeTrait;

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->setData($template, $model);

        return $template->getResponse();
    }

    /**
     * @throws Exception
     */
    private function setData(FragmentTemplate $template, ContentModel $model): void
    {
        $template->wildcard = '### '.u($GLOBALS['TL_LANG']['CTE']['teamsandplayersoverview'][0])->upper().' ###';
        $template->subject = 'TODO';

        if ($this->isBackend()) {
            return;
        }

        $templateResult = [];

        $saisons = StringUtil::deserialize($model->saison);
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
            $dbResult = $this->connection->executeQuery($query,
                [$saison],
                [ParameterType::INTEGER]
            );

            $teams = $dbResult->fetchAllAssociative();

            $statement = $this->connection->prepare('SELECT COUNT(*) n FROM tl_spieler s LEFT JOIN tl_mannschaft m on s.pid = m.id WHERE m.id=?');

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

        $template->result = $aggregated;
        $template->gesamt = array_reduce($aggregated, function ($carry, $item) {
            return [
                'mannschaften' => $carry['mannschaften'] + $item['mannschaften'],
                'spieler' => $carry['spieler'] + $item['spieler']
            ];
        }, ['mannschaften' => 0, 'spieler' => 0]);

    }

}
