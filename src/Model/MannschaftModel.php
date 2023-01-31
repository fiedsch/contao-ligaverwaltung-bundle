<?php

declare(strict_types=1);

/*
 * This file is part of fiedsch/ligaverwaltung-bundle.
 *
 * (c) 2016-2023 Andreas Fieger
 *
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
 * @license https://opensource.org/licenses/MIT
 */

namespace Fiedsch\LigaverwaltungBundle\Model;

use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Contao\PageModel;
use Exception;

/**
 * @property int    $id
 * @property int    $pid
 * @property string $name
 * @property bool   $active
 * @property int    $spielort
 *
 * @method static MannschaftModel|null findById($id, array $opt=array())
 * @method static Collection|MannschaftModel|null findByLiga($id, array $opt=array())
 */
class MannschaftModel extends Model
{

    const MANNSCHAFT_DOES_NOT_EXIST = '[ex. nicht mehr]';
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_mannschaft';

    /**
     * Alle Mannschaften, die aktiv sind, d.h. in eine liga (tl_liga) spielen, die aktiv ist.
     *
     * @return Collection|null
     */
    public static function findAllActive(): ?Collection
    {
        $result = Database::getInstance()
            ->prepare('SELECT m.* FROM  tl_mannschaft m LEFT JOIN tl_liga l ON (m.liga=l.id) WHERE l.aktiv=?')
            ->execute(1)
        ;

        return Model::createCollectionFromDbResult($result, 'tl_mannschaft');
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    public function getFullName(): string
    {
        $result = $this->name;
        $liga = $this->getRelated('liga');

        if ($liga) {
            $result .= ' '.$liga->name;
            $saison = $liga->getRelated('saison');

            if ($saison) {
                $result .= ', '.$saison->name;
            }
        }

        return $result;
    }

    /**
     * Zur "Mansnchaftsseite" verlinkter Name der Mannschaft.
     *
     * @return string
     */
    public function getLinkedName(): string
    {
        $teampageId = Config::get('teampage');

        if ($teampageId && $this->active) {
            $teampage = PageModel::findById($teampageId);

            if (Config::get('folderUrl')) {
                $url = Controller::generateFrontendUrl($teampage->row(), '/id/'.$this->id);
            } else {
                $url = Controller::generateFrontendUrl($teampage->row()).'?id='.$this->id;
            }
            $result = sprintf("<a href='%s'>%s</a>",
                $url,
                $this->name
            );
        } else {
            $result = $this->name.($this->active ? '' : ' (nicht mehr aktiv)');
        }

        return $result;
    }
}
