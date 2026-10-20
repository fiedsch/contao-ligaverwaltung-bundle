<?php

namespace Fiedsch\Ligaverwaltung\Callback;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

class MemberCallbacks
{
    public function __construct(private Connection $connection, private RequestStack $requestStack)
    {
    }

    /*
     * Panelerweitern (Dropdown für unsere Suche in externer Datenbanktabelle tl_mannschaft hinzufügen)
     */
    #[AsCallback(table: 'tl_member', target: 'list.sorting.panel_callback.foreign_filter')]
    public function listSortingPanelCallback(DataContainer $dc): string
    {
        $foreignFilter = $this->getFilter();

        $html = '';
        $html .= '<div class="tl_filter tl_subpanel">';
        $html .= '  <strong style="padding-right:5px;">Filtern (andere Tabelle)</strong>';
        $html .= '  <label for="foreign_filter">Mannschaft</label>';
        $html .= '  <div class="tl_select_wrapper" data-controller="contao--choices">';

        $isActive = !empty($foreignFilter);

        $html .= sprintf('<select name="foreign_filter" id="foreign_filter" class="tl_select%s" onchange="this.form.requestSubmit()">',
            $isActive ? ' active' : ''
        );
        $html .= '<option value="">-</option>';

        foreach ($this->getOptionValues() as $option => $label) {
            $html .= sprintf('<option value="%s"%s>%s</option>',
                $option,
                $foreignFilter == $option ? ' selected' : '',
                $label
            );
        }

        $html .= '</select>';

        $html .= '  </div>'; // 'tl_select_wrapper'
        $html .= '</div>'; // 'tl_filter tl_subpanel'

        return $html;
    }



    #[AsCallback(table: 'tl_member', target: 'config.onload')]
    public function dcaOnloadCallback(DataContainer $dc): void
    {
        $foreignFilter = $this->getFilter(); // auch, um ggf. den Filter aus der Session zu entfernen

        if ($foreignFilter !== null && $foreignFilter !== '') {
$sql = <<<SQL
  SELECT me.id FROM tl_member me
            LEFT JOIN tl_spieler sp ON (sp.member_id=me.id)
            LEFT JOIN tl_mannschaft ma ON (sp.pid=ma.id)
            WHERE ma.id= ?
SQL;
            $relatedIds = $this->connection->fetchAllAssociative(
                $sql,
                [$foreignFilter]
            );

            $ids = array_column($relatedIds, 'id');

            if (empty($ids)) {
                $ids = [0];
            }

            $GLOBALS['TL_DCA']['tl_member']['list']['sorting']['filter'][] =
                'id IN (' . implode(',', array_map('intval', $ids)) . ')'
            ;
        }
    }

    protected function getFilter(): string
    {
        $foreignFilter = Input::post('foreign_filter');

        $session = $this->requestStack->getCurrentRequest()->getSession();
        $sessionBag = $session->getBag('contao_backend');
        if ($foreignFilter !== null) {
            $sessionBag->set('foreign_filter', $foreignFilter);
        } else {
            $foreignFilter = $sessionBag->get('foreign_filter') ?? '';

        }


        $filter = $sessionBag->get('filter');

        // Wenn der User auf die Filter zurücksetzt leeren wir die Session
        // TODO: Der "Zurücksetzen" Button muss aktiv "gemacht werden"
        if ('1' === Input::post('filter_reset')) {
            $sessionBag->remove('foreign_filter');
            $foreignFilter = '';
        }
        if ($foreignFilter === '') {
            unset($filter['tl_member']['foreign_filter']);
        } else {
            $filter['tl_member']['foreign_filter'] = $foreignFilter;
        }
        $sessionBag->set('filter', $filter);

        return $foreignFilter;
    }

    protected function getOptionValues(): array
    {
        $sql = <<<SQL
SELECT m.id as id, m.name as mannschaft,l.name as liga, s.name as saison
  FROM tl_mannschaft m
  LEFT JOIN tl_liga l ON (m.liga=l.id)
  LEFT JOIN tl_saison s ON (l.saison=s.id)
  WHERE m.active=1 AND l.saison IS NOT NULL AND l.aktiv = 1 AND s.name IS NOT NULL
  ORDER BY m.name ASC, s.name ASC, l.name ASC
SQL;
        $statement = $this->connection->prepare($sql);
        $dbResult = $statement->executeQuery();
        $result = [];
        foreach ($dbResult->fetchAllAssociative() as $row) {
            $result[$row['id']] = sprintf('%s (%s %s)', $row['mannschaft'], $row['saison'], $row['liga']);
        }

        return $result;
    }


}
