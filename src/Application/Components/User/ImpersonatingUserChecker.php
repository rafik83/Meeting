<?php

namespace Proximum\Vimeet\Application\Components\User;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ImpersonatingUserCheckerInterface;

class ImpersonatingUserChecker implements ImpersonatingUserCheckerInterface
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /**
     * ImpersonatingUserChecker constructor.
     *
     * @param AuthorizationCheckerAdapterInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerAdapterInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isImpersonated()
    {
        return $this->isPreviousAdmin() || $this->isPreviousUser();
    }

    /**
     * {@inheritdoc}
     */
    public function isPreviousAdmin()
    {
        return $this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN');
    }

    /**
     * {@inheritdoc}
     */
    public function isPreviousUser()
    {
        return $this->authorizationChecker->isGranted('ROLE_PREVIOUS_USER');
    }
}
