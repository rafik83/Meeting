<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as SymfonyAuthorizationCheckerInterface;

class AuthorizationCheckerAdapter
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
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes
     * @param mixed $object
     *
     * @return bool
     */
    public function isGranted($attributes, $object = null)
    {
        return $this->authorizationChecker->isGranted($attributes, $object);
    }
}
