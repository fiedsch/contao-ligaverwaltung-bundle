<?php

namespace Fiedsch\LigaverwaltungBundle\Trait;

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
