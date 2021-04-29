<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationManager implements AuthenticationManagerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SessionInterface */
    private $session;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param TokenStorageInterface    $tokenStorage
     * @param SessionInterface         $session
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->tokenStorage    = $tokenStorage;
        $this->session         = $session;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Authenticate the user
     */
    public function authenticate(AbstractUser $user, string $providerKey)
    {
        if ($user instanceof Admin) {
            if (!$user->isAccountNonExpired()
                || !$user->isAccountNonLocked()
                || !$user->isCredentialsNonExpired()
                || !$user->isEnabled()
                || $user->isDeleted()
            ) {
                throw new AuthenticationException();
            }
        }

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $this->tokenStorage->setToken($token);

        $event = new AuthenticationEvent($token);
        $this->eventDispatcher->dispatch(AuthenticationEvents::AUTHENTICATION_SUCCESS, $event);
    }

    /**
     * Disconnect the user
     */
    public function disconnect()
    {
        $this->tokenStorage->setToken(null);
        $this->session->invalidate();
    }
}
