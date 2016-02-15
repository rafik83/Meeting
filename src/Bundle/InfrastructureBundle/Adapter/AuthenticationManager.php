<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthenticationManager implements AuthenticationManagerInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * AuthenticationManager constructor.
     *
     * @param TokenStorageInterface    $tokenStorage
     * @param SessionInterface         $session
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack             $requestStack
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack
    ) {
        $this->tokenStorage    = $tokenStorage;
        $this->session         = $session;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack    = $requestStack;
    }

    /**
     * Authenticate the user
     *
     * @param User $user
     */
    public function authenticate(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($this->requestStack->getMasterRequest(), $token);
        $this->eventDispatcher->dispatch('security.interactive_login', $event);
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
