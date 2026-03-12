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

namespace Fiedsch\Ligaverwaltung\Trait;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;

trait TlModeTrait
{
    private ?RequestStack $requestStack = null;
    private ?ScopeMatcher $scopeMatcher = null;

    // public function __construct(private RequestStack $requestStack, private ScopeMatcher $scopeMatcher)
    // {
    // }

    public function initializeRequestAndScope(): void
    {
        $this->requestStack = System::getContainer()->get('request_stack');
        $this->scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
    }

    public function isBackend(): bool
    {
        if (null === $this->requestStack) { $this->initializeRequestAndScope(); }
        if (null === $this->scopeMatcher) { $this->initializeRequestAndScope(); }
        return $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest());
    }

    public function isFrontend(): bool
    {
        if (null === $this->requestStack) { $this->initializeRequestAndScope(); }
        if (null === $this->scopeMatcher) { $this->initializeRequestAndScope(); }
        return $this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest());
    }
}
