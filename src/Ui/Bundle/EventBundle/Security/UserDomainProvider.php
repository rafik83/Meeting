<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Security;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserDomainProvider
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getUserDomain(): ?UserDomain
    {
        if (!$this->isUserDomainConnected()) {
            return null;
        }

        return $this->createUserDomain();
    }

    public function createUserDomain(): UserDomain
    {
        return new UserDomain($this->tokenStorage->getToken()->getUser());
    }

    public function isUserDomainConnected(): bool
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            return false;
        }

        return $token->getUser() instanceof User;
    }
}
