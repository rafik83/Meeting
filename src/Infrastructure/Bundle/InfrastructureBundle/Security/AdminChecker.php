<?php
/*
* This file is part of the Proximum Vimeet project.
*
* Copyright (C) Proximum
*
* @author Elao <contact@elao.com>
*/

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security;

use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AdminChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Admin) {
            return;
        }

        // user is deleted, show a generic Account Not Found message.
        if ($user->isDeleted()
            || !$user->isAccountNonExpired()
            || !$user->isEnabled()
            || !$user->isCredentialsNonExpired()
            || !$user->isAccountNonLocked()
        ) {
            throw new AuthenticationException();
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPostAuth(UserInterface $user){}
}
