<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\User;

use Proximum\Vimeet\Application\Adapter\ImpersonatingUserCheckerInterface;
use Proximum\Vimeet\Infrastructure\Adapter\AuthorizationCheckerAdapter;

class ImpersonatingUserChecker implements ImpersonatingUserCheckerInterface
{
    /** @var AuthorizationCheckerAdapter */
    private $authorizationChecker;

    /**
     * ImpersonatingUserChecker constructor.
     *
     * @param AuthorizationCheckerAdapter $authorizationChecker
     */
    public function __construct(AuthorizationCheckerAdapter $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isImpersonated()
    {
        return ($this->isPreviousAdmin() || $this->isPreviousUser());
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
