<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\CsrfTokenAdapterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenAdapter implements CsrfTokenAdapterInterface
{
    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function isTokenValid(string $id, ?string $submittedValue): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $submittedValue));
    }
}
