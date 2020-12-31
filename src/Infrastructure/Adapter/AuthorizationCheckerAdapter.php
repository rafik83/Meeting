<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as SymfonyAuthorizationCheckerInterface;

class AuthorizationCheckerAdapter implements AuthorizationCheckerAdapterInterface
{
    /** @var SymfonyAuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * AuthorizationCheckerAdapter constructor.
     *
     * @param SymfonyAuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(SymfonyAuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }
}
