<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security;

use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserChecker as SymfonyUserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminChecker extends SymfonyUserChecker
{
    public function checkPreAuth(UserInterface $user): void
    {
        parent::checkPreAuth($user);

        if (!$user instanceof Admin) {
            return;
        }

        if ($user->isDeleted()) {
            throw new DisabledException('Account is disabled.');
        }
    }
}
