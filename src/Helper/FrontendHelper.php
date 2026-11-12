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

namespace Fiedsch\Ligaverwaltung\Helper;

use Contao\FrontendUser;
use Contao\MemberModel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FrontendHelper
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getCurrentLoggedInMember(): ?MemberModel
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof FrontendUser) {
            return MemberModel::findById($user->id);
        }

        return null;
    }

}
