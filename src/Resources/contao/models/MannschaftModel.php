<?php

/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

namespace Contao;

class MannschaftModel extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = "tl_mannschaft";

    /**
     * Alle Mannschaften, die aktiv sind, d.h. in eine liga (tl_liga) spielen, die aktiv ist
     *
     * @return \Contao\Model\Collection|null
     */
    public static function findAllActive()
    {
        $result = Database::getInstance()
            ->prepare('SELECT m.* FROM  tl_mannschaft m LEFT JOIN tl_liga l ON (m.liga=l.id) WHERE l.aktiv=?')
            ->execute(1);
        return Model::createCollectionFromDbResult($result, 'tl_mannschaft');
    }


    /**
     * @return string
     */
    public function getFullName()
    {
        $result = $this->name;
        $liga = $this->getRelated('liga');
        if ($liga) {
            $result .= ' ' . $liga->name;
            $saison = $liga->getRelated('saison');
            if ($saison) {
                $result .= ', ' . $saison->name;
            }
            return $result;
        }
    }

    /**
     * Zur "Mansnchaftsseite" verlinkter Name der Mannschaft
     *
     * @return string
     */
    public function getLinkedName()
    {
        $teampageId = Config::get('teampage');
        if ($teampageId && $this->active) {
            $teampage = PageModel::findById($teampageId);

            if (\Config::get('folderUrl')) {
                $url = Controller::generateFrontendUrl($teampage->row(), '/id/'.$this->id);
            } else {
                $url = Controller::generateFrontendUrl($teampage->row()) . '?id=' . $this->id;
            }
            $result = sprintf("<a href='%s'>%s</a>",
                $url,
                $this->name
            );
        } else {
            $result = $this->name . ($this->active ? '' : ' (nicht mehr aktiv)');
        }
        return $result;
    }
}