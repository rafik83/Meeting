<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\User;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ImpersonatingUserChecker
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * ImpersonatingUserChecker constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @return bool
     */
    public function isImpersonated()
    {
        return ($this->isUserWasPreviousAdmin() || $this->isUserWasPreviousUser());
    }

    /**
     * Return true if current user have ROLE_PREVIOUS_ADMIN
     *
     * @return bool
     */
    public function isUserWasPreviousAdmin()
    {
        return $this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN');
    }

    /**
     * Return true if current user have ROLE_PREVIOUS_USER
     *
     * @return bool
     */
    public function isUserWasPreviousUser()
    {
        return $this->authorizationChecker->isGranted('ROLE_PREVIOUS_USER');
    }
}
