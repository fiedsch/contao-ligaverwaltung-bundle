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

namespace Fiedsch\Ligaverwaltung\Model;

use Contao\Config;
use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;
use Contao\PageModel;
//use Contao\System;
use Exception;
//use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    const int ALLE_MANNSCHAFTEN = 0;
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

    public function getShortName(): string
    {
        $result = $this->name;
        // $liga = $this->getRelated('liga');

        if (!$this->active) {
            // Strikethrough and gray to indicate that the team is no longer active
            $result = sprintf('<span class="tl_gray"><s>%s</s></span>', $result);
        }

        return $result;
    }

    /**
     * Zur "Mannschaftsseite" verlinkter Name der Mannschaft.
     *
     * @deprecated do not generate HTML which forces us to use |raw in templates. Use self::getTeamPageLink()
     *
     * @return string
     */
    public function getLinkedName(): string
    {
        $teampageId = Config::get('teampage');

        $mannschaftsName = $this->name;
        if (!$this->active) {
            $mannschaftsName = sprintf('<s>%s</s> (nicht mehr aktiv)', $mannschaftsName);
        }

        if ($teampageId && $this->active) {
            $teampage = PageModel::findById($teampageId);

            if (Config::get('folderUrl')) {
                $url = $teampage->getFrontendUrl('/id/'.$this->id);
            } else {
                $url = $teampage->getFrontendUrl('?id='.$this->id);
            }
            // $urlGenerator = System::getContainer()->get('contao.routing.content_url_generator');
            // $url = $urlGenerator->generate($teampage, ['id' => $this->id], UrlGeneratorInterface::ABSOLUTE_PATH);

            $result = sprintf("<a href='%s'>%s</a>",
                $url,
                $mannschaftsName
            );
        } else {
            $result = $mannschaftsName;
        }

        return $result;
    }

    public function getTeamPageLink(): string
    {
        $teampageId = Config::get('teampage');
        $teampage = PageModel::findById($teampageId);

        if (!$teampage) {
            return '';
        }

        if (Config::get('folderUrl')) {
            $url = $teampage->getFrontendUrl('/id/'.$this->id);
        } else {
            $url = $teampage->getFrontendUrl('?id='.$this->id);
        }

        return $url;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
